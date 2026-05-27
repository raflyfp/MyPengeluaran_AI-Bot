<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AnalyticsController extends Controller
{
    private const CHART_COLORS = ['#0D8B7D', '#093C5D', '#3B7597', '#6FD1D7', '#4D42A8', '#BA1A1A'];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $selectedMonth = CarbonImmutable::parse($request->query('month', now()->toDateString()));
        $monthlySummary = Transaction::monthlySummaryFor($user, $selectedMonth);
        $categoryBreakdown = $this->categorySpendingBreakdown($user, $selectedMonth);
        $income = (float) $monthlySummary['income_total'];
        $expense = (float) $monthlySummary['expense_total'];
        $net = $income - $expense;

        return view('analytics.index', [
            'apexCharts' => [
                'monthlySpending' => $this->monthlySpendingChartData($user),
                'categoryBreakdown' => $this->categoryBreakdownChartData($categoryBreakdown),
                'weeklyExpenseTrends' => $this->weeklyExpenseTrends($user, $selectedMonth),
                'monthlyIncomeVsExpense' => $this->monthlyIncomeVsExpense($user),
            ],
            'summaryCards' => [
                [
                    'label' => 'Total Spending',
                    'value' => $this->formatRupiah($expense),
                    'caption' => $selectedMonth->format('F Y'),
                    'tone' => 'expense',
                    'change' => $this->spendingChangeLabel($user, $selectedMonth),
                ],
                [
                    'label' => 'Net Cashflow',
                    'value' => $this->formatRupiah($net, withSign: true),
                    'caption' => 'Income minus expense',
                    'tone' => $net >= 0 ? 'income' : 'expense',
                    'change' => $net >= 0 ? 'Positive' : 'Deficit',
                ],
            ],
            'analyticsCards' => $this->analyticsCards($user, $selectedMonth, $income, $expense, $categoryBreakdown),
            'categories' => $this->categoryStatistics($categoryBreakdown, $expense),
            'months' => $this->monthlyOverview($user),
            'topSpendingCategories' => $this->topSpendingCategories($categoryBreakdown),
        ]);
    }

    public function monthlySpendingChartData($user, int $months = 6): array
    {
        $start = now()->copy()->subMonths($months - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $rows = Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->selectRaw("date_trunc('month', transactions.transaction_date) as period")
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total')
            ->groupByRaw("date_trunc('month', transactions.transaction_date)")
            ->orderByRaw("date_trunc('month', transactions.transaction_date)")
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m'));

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $data[] = (float) ($rows->get($key)?->total ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Monthly Spending', 'data' => $data],
            ],
        ];
    }

    public function weeklyExpenseTrends($user, ?CarbonImmutable $date = null): array
    {
        $date ??= CarbonImmutable::now();
        $start = $date->startOfWeek();
        $end = $date->endOfWeek();
        $rows = Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->selectRaw('EXTRACT(ISODOW FROM transactions.transaction_date)::int as weekday')
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total')
            ->groupByRaw('EXTRACT(ISODOW FROM transactions.transaction_date)::int')
            ->orderByRaw('EXTRACT(ISODOW FROM transactions.transaction_date)::int')
            ->get()
            ->keyBy('weekday');

        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'series' => [
                [
                    'name' => 'Expense',
                    'data' => collect(range(1, 7))
                        ->map(fn (int $day): float => (float) ($rows->get($day)?->total ?? 0))
                        ->all(),
                ],
            ],
        ];
    }

    public function monthlyIncomeVsExpense($user, int $months = 6): array
    {
        $start = now()->copy()->subMonths($months - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $rows = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->selectRaw("date_trunc('month', transactions.transaction_date) as period")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
            ->groupByRaw("date_trunc('month', transactions.transaction_date)")
            ->orderByRaw("date_trunc('month', transactions.transaction_date)")
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m'));

        $labels = [];
        $income = [];
        $expense = [];

        foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $income[] = (float) ($rows->get($key)?->income_total ?? 0);
            $expense[] = (float) ($rows->get($key)?->expense_total ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Income', 'data' => $income],
                ['name' => 'Expense', 'data' => $expense],
            ],
        ];
    }

    private function categorySpendingBreakdown($user, CarbonImmutable $date): Collection
    {
        return Transaction::categorySpendingBreakdownFor($user, $date)
            ->values()
            ->map(function ($category, int $index): array {
                return [
                    'id' => $category->id,
                'name' => $this->displayCategoryName($category->name),
                    'icon' => $category->icon,
                    'total' => (float) $category->total,
                    'transactions_count' => (int) $category->transactions_count,
                    'color' => self::CHART_COLORS[$index % count(self::CHART_COLORS)],
                ];
            });
    }

    private function categoryBreakdownChartData(Collection $categoryBreakdown): array
    {
        return [
            'labels' => $categoryBreakdown->pluck('name')->values()->all(),
            'series' => $categoryBreakdown->pluck('total')->map(fn ($total): float => (float) $total)->values()->all(),
            'colors' => $categoryBreakdown->pluck('color')->values()->all(),
        ];
    }

    private function categoryStatistics(Collection $categoryBreakdown, float $monthlyExpenseTotal): array
    {
        return $categoryBreakdown
            ->map(fn (array $category): array => [
                'name' => $category['name'],
                'amount' => $this->formatRupiah($category['total']),
                'share' => $monthlyExpenseTotal > 0 ? round(($category['total'] / $monthlyExpenseTotal) * 100, 1) : 0,
                'color' => $category['color'],
            ])
            ->values()
            ->all();
    }

    private function topSpendingCategories(Collection $categoryBreakdown, int $limit = 5): array
    {
        return $categoryBreakdown
            ->take($limit)
            ->map(fn (array $category): array => [
                'name' => $category['name'],
                'total' => $category['total'],
                'formatted_total' => $this->formatRupiah($category['total']),
                'transactions_count' => $category['transactions_count'],
            ])
            ->values()
            ->all();
    }

    private function monthlyOverview($user, int $months = 3): array
    {
        $start = now()->copy()->subMonths($months - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $rows = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->selectRaw("date_trunc('month', transactions.transaction_date) as period")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
            ->groupByRaw("date_trunc('month', transactions.transaction_date)")
            ->orderByRaw("date_trunc('month', transactions.transaction_date)")
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m'));

        return collect(CarbonPeriod::create($start, '1 month', $end))
            ->map(function ($month) use ($rows): array {
                $key = $month->format('Y-m');
                $income = (float) ($rows->get($key)?->income_total ?? 0);
                $expense = (float) ($rows->get($key)?->expense_total ?? 0);

                return [
                    'label' => $month->format('M'),
                    'income' => $this->formatRupiah($income),
                    'expense' => $this->formatRupiah($expense),
                    'net' => $this->formatRupiah($income - $expense, withSign: true),
                ];
            })
            ->values()
            ->all();
    }

    private function analyticsCards($user, CarbonImmutable $date, float $income, float $expense, Collection $categoryBreakdown): array
    {
        $daysElapsed = max(1, min((int) $date->day, (int) $date->daysInMonth));
        $dailyAverage = $expense / $daysElapsed;
        $savingsRate = $income > 0 ? round((($income - $expense) / $income) * 100) : 0;
        $topCategory = $categoryBreakdown->first();

        return [
            ['label' => 'Daily Avg', 'value' => $this->formatRupiah($dailyAverage), 'caption' => 'This month', 'tone' => 'blue'],
            ['label' => 'Savings Rate', 'value' => $savingsRate.'%', 'caption' => $savingsRate >= 20 ? 'On target' : 'Needs attention', 'tone' => 'green'],
            ['label' => 'Top Category', 'value' => $topCategory['name'] ?? '-', 'caption' => isset($topCategory) ? $this->formatRupiah($topCategory['total']) : 'No spending yet', 'tone' => 'cyan'],
        ];
    }

    private function spendingChangeLabel($user, CarbonImmutable $date): string
    {
        $current = (float) Transaction::monthlyExpenseTotalFor($user, $date);
        $previous = (float) Transaction::monthlyExpenseTotalFor($user, $date->subMonth());

        if ($previous <= 0) {
            return $current > 0 ? 'New' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;

        return ($change > 0 ? '+' : '').number_format($change, 1, ',', '.').'%';
    }

    private function formatRupiah(string|int|float|null $amount, bool $withSign = false): string
    {
        $numericAmount = (float) ($amount ?? 0);
        $prefix = $withSign && $numericAmount > 0 ? '+' : ($withSign && $numericAmount < 0 ? '-' : '');

        return $prefix.'Rp '.number_format(abs($numericAmount), 0, ',', '.');
    }

    private function displayCategoryName(?string $categoryName): string
    {
        return match (strtolower((string) $categoryName)) {
            'food & dining', 'food and dining' => 'Food & Drink',
            '' => 'Uncategorized',
            default => (string) $categoryName,
        };
    }
}
