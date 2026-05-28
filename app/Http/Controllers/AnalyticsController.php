<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    private const CHART_COLORS = ['#0D8B7D', '#093C5D', '#3B7597', '#6FD1D7', '#4D42A8', '#BA1A1A'];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $selectedMonth = CarbonImmutable::parse($request->query('month', now()->toDateString()));
        $activePeriod = $this->resolvePeriod($request);
        [$start, $end] = $this->periodRange($activePeriod, $selectedMonth);
        $periodSummary = $this->summaryForPeriod($user, $start, $end);
        $categoryBreakdown = $this->categorySpendingBreakdownForPeriod($user, $start, $end);
        $income = $periodSummary['income_total'];
        $expense = $periodSummary['expense_total'];
        $net = $income - $expense;
        $daysInPeriod = $start->diffInDays($end) + 1;

        return view('analytics.index', [
            'activePeriod' => $activePeriod,
            'apexCharts' => [
                'categoryBreakdown' => $this->categoryBreakdownChartData($categoryBreakdown),
                'weeklyExpenseTrends' => $this->expenseTrendChart($user, $start, $end, $activePeriod),
                'monthlyIncomeVsExpense' => $this->incomeVsExpenseChart($user, $start, $end, $activePeriod),
            ],
            'summaryCards' => [
                [
                    'label' => 'Total Spending',
                    'value' => $this->formatRupiah($expense),
                    'caption' => $this->periodCaption($activePeriod, $selectedMonth, $start, $end),
                    'tone' => 'expense',
                    'change' => $this->spendingChangeLabelForPeriod($user, $start, $end),
                ],
                [
                    'label' => 'Net Cashflow',
                    'value' => $this->formatRupiah($net, withSign: true),
                    'caption' => 'Income minus expense',
                    'tone' => $net >= 0 ? 'income' : 'expense',
                    'change' => $net >= 0 ? 'Positive' : 'Deficit',
                ],
            ],
            'analyticsCards' => $this->analyticsCards($user, $selectedMonth, $income, $expense, $categoryBreakdown, $daysInPeriod),
            'categories' => $this->categoryStatistics($categoryBreakdown, $expense),
            'months' => $this->periodOverview($user, $start, $end, $activePeriod),
            'topSpendingCategories' => $this->topSpendingCategories($categoryBreakdown),
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $selectedMonth = CarbonImmutable::parse($request->query('month', now()->toDateString()));
        $activePeriod = $this->resolvePeriod($request);
        [$start, $end] = $this->periodRange($activePeriod, $selectedMonth);
        $timezone = config('app.timezone');
        $fileName = sprintf('analytics-%s-%s-%s.csv', $activePeriod, $start->format('Ymd'), $end->format('Ymd'));

        $rows = Transaction::query()
            ->with('category')
            ->forUser($user)
            ->betweenDates($start, $end)
            ->orderByDesc('transaction_date')
            ->get();

        return response()->streamDownload(function () use ($rows, $timezone): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Time', 'Type', 'Category', 'Note', 'Amount']);

            foreach ($rows as $transaction) {
                $date = $transaction->transaction_date->copy()->timezone($timezone);
                fputcsv($handle, [
                    $date->format('Y-m-d'),
                    $date->format('H:i'),
                    $transaction->type,
                    $this->displayCategoryName($transaction->category?->name),
                    $transaction->note,
                    (string) $transaction->amount,
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function monthlySpendingChartData($user, int $months = 6): array
    {
        $start = now()->copy()->subMonths($months - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $monthExpr = $this->dateGroupExpression('month');
        $rows = Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->selectRaw("{$monthExpr} as period")
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total')
            ->groupByRaw($monthExpr)
            ->orderByRaw($monthExpr)
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

    public function expenseTrendChart($user, CarbonImmutable $start, CarbonImmutable $end, string $period): array
    {
        if ($period === 'Y') {
            $monthExpr = $this->dateGroupExpression('month');
            $rows = Transaction::query()
                ->forUser($user)
                ->ofType('expense')
                ->betweenDates($start, $end)
                ->selectRaw("{$monthExpr} as period")
                ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total')
                ->groupByRaw($monthExpr)
                ->orderByRaw($monthExpr)
                ->get()
                ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m'));

            $labels = [];
            $tooltipLabels = [];
            $data = [];

            foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
                $key = $month->format('Y-m');
                $labels[] = $month->format('M');
                $tooltipLabels[] = $month->format('F Y');
                $data[] = (float) ($rows->get($key)?->total ?? 0);
            }

            return [
                'labels' => $labels,
                'tooltipLabels' => $tooltipLabels,
                'showXAxisLabels' => false,
                'series' => [
                    ['name' => 'Expense', 'data' => $data],
                ],
            ];
        }

        $dateExpr = 'DATE(transactions.transaction_date)';
        $rows = Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->selectRaw("{$dateExpr} as period")
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total')
            ->groupByRaw($dateExpr)
            ->orderByRaw($dateExpr)
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m-d'));

        $labels = [];
        $tooltipLabels = [];
        $data = [];

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $key = $day->format('Y-m-d');
            $labels[] = $day->format($period === 'W' ? 'D' : 'j');
            $tooltipLabels[] = $day->format('d M Y');
            $data[] = (float) ($rows->get($key)?->total ?? 0);
        }

        return [
            'labels' => $labels,
            'tooltipLabels' => $tooltipLabels,
            'showXAxisLabels' => $period === 'M',
            'series' => [
                ['name' => 'Expense', 'data' => $data],
            ],
        ];
    }

    public function incomeVsExpenseChart($user, CarbonImmutable $start, CarbonImmutable $end, string $period): array
    {
        if ($period === 'Y') {
            $monthExpr = $this->dateGroupExpression('month');
            $rows = Transaction::query()
                ->forUser($user)
                ->betweenDates($start, $end)
                ->selectRaw("{$monthExpr} as period")
                ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
                ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
                ->groupByRaw($monthExpr)
                ->orderByRaw($monthExpr)
                ->get()
                ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m'));

            $labels = [];
            $tooltipLabels = [];
            $income = [];
            $expense = [];

            foreach (CarbonPeriod::create($start, '1 month', $end) as $month) {
                $key = $month->format('Y-m');
                $labels[] = $month->format('M');
                $tooltipLabels[] = $month->format('F Y');
                $income[] = (float) ($rows->get($key)?->income_total ?? 0);
                $expense[] = (float) ($rows->get($key)?->expense_total ?? 0);
            }

            return [
                'labels' => $labels,
                'tooltipLabels' => $tooltipLabels,
                'showXAxisLabels' => true,
                'series' => [
                    ['name' => 'Income', 'data' => $income],
                    ['name' => 'Expense', 'data' => $expense],
                ],
            ];
        }

        $dateExpr = 'DATE(transactions.transaction_date)';
        $rows = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->selectRaw("{$dateExpr} as period")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
            ->groupByRaw($dateExpr)
            ->orderByRaw($dateExpr)
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->period)->format('Y-m-d'));

        $labels = [];
        $tooltipLabels = [];
        $income = [];
        $expense = [];

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $key = $day->format('Y-m-d');
            $labels[] = $day->format($period === 'W' ? 'D' : 'j');
            $tooltipLabels[] = $day->format('d M Y');
            $income[] = (float) ($rows->get($key)?->income_total ?? 0);
            $expense[] = (float) ($rows->get($key)?->expense_total ?? 0);
        }

        return [
            'labels' => $labels,
            'tooltipLabels' => $tooltipLabels,
            'showXAxisLabels' => true,
            'series' => [
                ['name' => 'Income', 'data' => $income],
                ['name' => 'Expense', 'data' => $expense],
            ],
        ];
    }

    private function categorySpendingBreakdownForPeriod($user, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select([
                'categories.id',
                'categories.name',
                'categories.icon',
                DB::raw('SUM(transactions.amount) as total'),
                DB::raw('COUNT(transactions.id) as transactions_count'),
            ])
            ->groupBy('categories.id', 'categories.name', 'categories.icon')
            ->orderByDesc('total')
            ->get()
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

    private function periodOverview($user, CarbonImmutable $start, CarbonImmutable $end, string $period): array
    {
        $transactions = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->get(['transaction_date', 'type', 'amount']);

        if ($period === 'Y') {
            return collect(CarbonPeriod::create($start, '1 month', $end))
                ->map(function ($month) use ($transactions): array {
                    return $this->overviewRow(
                        $month->format('M'),
                        $transactions,
                        CarbonImmutable::parse($month)->startOfMonth(),
                        CarbonImmutable::parse($month)->endOfMonth()
                    );
                })
                ->values()
                ->all();
        }

        if ($period === 'W') {
            return collect(CarbonPeriod::create($start, '1 day', $end))
                ->map(fn ($day): array => $this->overviewRow(
                    CarbonImmutable::parse($day)->format('D d'),
                    $transactions,
                    CarbonImmutable::parse($day)->startOfDay(),
                    CarbonImmutable::parse($day)->endOfDay()
                ))
                ->values()
                ->all();
        }

        $rows = [];
        $week = 1;
        $cursor = $start;

        while ($cursor->lessThanOrEqualTo($end)) {
            $chunkEnd = $cursor->addDays(6)->endOfDay();

            if ($chunkEnd->greaterThan($end)) {
                $chunkEnd = $end;
            }

            $rows[] = $this->overviewRow(
                'Week '.$week,
                $transactions,
                $cursor,
                $chunkEnd
            );

            $week++;
            $cursor = $chunkEnd->addDay()->startOfDay();
        }

        return $rows;
    }

    private function overviewRow(string $label, Collection $transactions, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $periodTransactions = $transactions->filter(function (Transaction $transaction) use ($start, $end): bool {
            $date = CarbonImmutable::parse($transaction->transaction_date);

            return $date->greaterThanOrEqualTo($start) && $date->lessThanOrEqualTo($end);
        });

        $income = (float) $periodTransactions
            ->where('type', 'income')
            ->sum(fn (Transaction $transaction): float => (float) $transaction->amount);
        $expense = (float) $periodTransactions
            ->where('type', 'expense')
            ->sum(fn (Transaction $transaction): float => (float) $transaction->amount);

        return [
            'label' => $label,
            'income' => $this->formatRupiah($income),
            'expense' => $this->formatRupiah($expense),
            'net' => $this->formatRupiah($income - $expense, withSign: true),
        ];
    }

    private function analyticsCards($user, CarbonImmutable $date, float $income, float $expense, Collection $categoryBreakdown, int $daysInPeriod): array
    {
        $daysElapsed = max(1, $daysInPeriod);
        $dailyAverage = $expense / $daysElapsed;
        $savingsRate = $income > 0 ? round((($income - $expense) / $income) * 100) : 0;
        $topCategory = $categoryBreakdown->first();
        $periodLabel = $daysElapsed >= 300 ? 'Last 12 months' : ($daysElapsed <= 7 ? 'Last 7 days' : 'This period');

        return [
            ['label' => 'Daily Avg', 'value' => $this->formatRupiah($dailyAverage), 'caption' => $periodLabel, 'tone' => 'blue'],
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

    private function spendingChangeLabelForPeriod($user, CarbonImmutable $start, CarbonImmutable $end): string
    {
        $current = (float) Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($start, $end)
            ->sum('transactions.amount');

        $days = $start->diffInDays($end) + 1;
        $previousEnd = $start->subDay()->endOfDay();
        $previousStart = $previousEnd->subDays($days - 1)->startOfDay();

        $previous = (float) Transaction::query()
            ->forUser($user)
            ->ofType('expense')
            ->betweenDates($previousStart, $previousEnd)
            ->sum('transactions.amount');

        if ($previous <= 0) {
            return $current > 0 ? 'New' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;

        return ($change > 0 ? '+' : '').number_format($change, 1, ',', '.').'%';
    }

    private function resolvePeriod(Request $request): string
    {
        $period = strtoupper($request->query('period', 'M'));

        return in_array($period, ['W', 'M', 'Y'], true) ? $period : 'M';
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function periodRange(string $period, CarbonImmutable $selectedMonth): array
    {
        if ($period === 'W') {
            $end = CarbonImmutable::now()->endOfDay();
            $start = $end->subDays(6)->startOfDay();

            return [$start, $end];
        }

        if ($period === 'Y') {
            $end = CarbonImmutable::now()->endOfDay();
            $start = $end->subMonths(11)->startOfMonth();

            return [$start, $end];
        }

        return [$selectedMonth->startOfMonth(), $selectedMonth->endOfMonth()];
    }

    private function periodCaption(string $period, CarbonImmutable $selectedMonth, CarbonImmutable $start, CarbonImmutable $end): string
    {
        return match ($period) {
            'W' => 'Last 7 days',
            'Y' => 'Last 12 months',
            default => $selectedMonth->format('F Y'),
        };
    }

    private function summaryForPeriod($user, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $summary = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
            ->first();

        return [
            'income_total' => (float) ($summary?->income_total ?? 0),
            'expense_total' => (float) ($summary?->expense_total ?? 0),
        ];
    }

    private function dateGroupExpression(string $granularity): string
    {
        if ($granularity === 'month') {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                return "strftime('%Y-%m-01', transactions.transaction_date)";
            }

            if ($driver === 'pgsql') {
                return "DATE_TRUNC('month', transactions.transaction_date)";
            }

            return "DATE_FORMAT(transactions.transaction_date, '%Y-%m-01')";
        }

        return 'DATE(transactions.transaction_date)';
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
