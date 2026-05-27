<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $monthlySummary = Transaction::monthlySummaryFor($user);
        $currentBalance = Transaction::currentBalanceFor($user);
        $categoryBreakdown = Transaction::categorySpendingBreakdownFor($user);
        $monthlyExpenseTotal = (float) $monthlySummary['expense_total'];

        return view('dashboard', [
            'user' => $user,
            'summary' => [
                'monthly_income_total' => $monthlySummary['income_total'],
                'monthly_expense_total' => $monthlySummary['expense_total'],
                'current_balance' => $currentBalance,
                'formatted_monthly_income_total' => $this->formatRupiah($monthlySummary['income_total']),
                'formatted_monthly_expense_total' => $this->formatRupiah($monthlySummary['expense_total']),
                'formatted_current_balance' => $this->formatRupiah($currentBalance),
                'balance_change_label' => $this->balanceChangeLabel($user),
                'category_spending_breakdown' => $this->formatCategoryBreakdown($categoryBreakdown, $monthlyExpenseTotal),
            ],
            'transactions' => Transaction::query()
                ->with('category')
                ->forUser($user)
                ->latest('transaction_date')
                ->limit(5)
                ->get()
                ->map(fn (Transaction $transaction): array => $this->formatTransaction($transaction)),
            'cashflow' => $this->cashflowChartData($user),
        ]);
    }

    private function formatRupiah(string|int|float|null $amount, bool $withSign = false, string $type = 'income'): string
    {
        $numericAmount = (float) ($amount ?? 0);
        $prefix = '';

        if ($withSign) {
            $prefix = $type === 'income' ? '+' : '-';
        }

        return $prefix.'Rp '.number_format(abs($numericAmount), 0, ',', '.');
    }

    /**
     * @param  Collection<int, mixed>  $categoryBreakdown
     * @return Collection<int, array{name: string, icon: string|null, total: string, formatted_total: string, transactions_count: int, share: float}>
     */
    private function formatCategoryBreakdown(Collection $categoryBreakdown, float $monthlyExpenseTotal): Collection
    {
        return $categoryBreakdown->map(function ($category) use ($monthlyExpenseTotal): array {
            $total = (float) $category->total;

            return [
                'name' => $this->displayCategoryName($category->name),
                'icon' => $category->icon,
                'total' => (string) $category->total,
                'formatted_total' => $this->formatRupiah($category->total),
                'transactions_count' => (int) $category->transactions_count,
                'share' => $monthlyExpenseTotal > 0 ? round(($total / $monthlyExpenseTotal) * 100, 1) : 0.0,
            ];
        });
    }

    /**
     * @return array{title: string, category: string, time: string, amount: string, type: string, icon: string}
     */
    private function formatTransaction(Transaction $transaction): array
    {
        $transactionDate = $transaction->transaction_date
            ->copy()
            ->timezone(config('app.timezone'));

        return [
            'title' => $transaction->note ?: $transaction->category?->name ?: 'Transaction',
            'category' => $this->displayCategoryName($transaction->category?->name),
            'time' => $this->formatTransactionTime($transactionDate),
            'amount' => $this->formatRupiah($transaction->amount, true, $transaction->type),
            'type' => $transaction->type,
            'icon' => $this->mapCategoryIcon($transaction->category?->icon, $transaction->category?->name),
        ];
    }

    private function balanceChangeLabel($user): string
    {
        $now = now();
        $currentMonthNet = (float) Transaction::monthlySummaryFor($user, $now)['net_total'];
        $previousMonthNet = (float) Transaction::monthlySummaryFor($user, $now->copy()->subMonth())['net_total'];

        if ($previousMonthNet == 0.0) {
            return $currentMonthNet == 0.0 ? '0%' : 'New';
        }

        $change = (($currentMonthNet - $previousMonthNet) / abs($previousMonthNet)) * 100;

        return ($change > 0 ? '+' : '').number_format($change, 1, ',', '.').'%';
    }

    private function displayCategoryName(?string $categoryName): string
    {
        return match (strtolower((string) $categoryName)) {
            'food & dining', 'food and dining' => 'Food & Drink',
            '' => 'Uncategorized',
            default => (string) $categoryName,
        };
    }

    private function formatTransactionTime($transactionDate): string
    {
        $now = now(config('app.timezone'));

        if ($transactionDate->isSameDay($now)) {
            $minutes = (int) $transactionDate->diffInMinutes($now, false);

            if ($minutes >= 0 && $minutes < 3) {
                return 'Baru saja';
            }

            return 'Hari ini, '.$transactionDate->format('H:i');
        }

        if ($transactionDate->isYesterday()) {
            return 'Kemarin, '.$transactionDate->format('H:i');
        }

        return $transactionDate->format('d M, H:i');
    }

    /**
     * @return array{line_points: string, area_points: string, has_data: bool, labels: array<int, string>}
     */
    private function cashflowChartData($user): array
    {
        $start = now()->copy()->subDays(6)->startOfDay();
        $end = now()->copy()->endOfDay();
        $rows = Transaction::query()
            ->forUser($user)
            ->betweenDates($start, $end)
            ->selectRaw('DATE(transactions.transaction_date) as day')
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE -transactions.amount END), 0) as net_total")
            ->groupByRaw('DATE(transactions.transaction_date)')
            ->orderByRaw('DATE(transactions.transaction_date)')
            ->get()
            ->keyBy(fn ($row): string => CarbonImmutable::parse($row->day)->format('Y-m-d'));

        $period = collect(CarbonPeriod::create($start, '1 day', $end));
        $values = $period
            ->map(fn ($date): float => (float) ($rows->get($date->format('Y-m-d'))?->net_total ?? 0))
            ->values();

        if ($values->every(fn (float $value): bool => $value === 0.0)) {
            $values = collect([0, 8000, 14000, 9000, 18000, 12000, 22000]);
            $hasData = false;
        } else {
            $hasData = true;
        }

        $min = (float) $values->min();
        $max = (float) $values->max();
        $range = max(1, $max - $min);
        $count = max(1, $values->count() - 1);
        $left = 24;
        $right = 296;
        $top = 34;
        $bottom = 136;

        $points = $values
            ->map(function (float $value, int $index) use ($left, $right, $top, $bottom, $range, $min, $count): string {
                $x = $left + (($right - $left) / $count) * $index;
                $y = $bottom - ((($value - $min) / $range) * ($bottom - $top));

                return round($x, 1).','.round($y, 1);
            })
            ->implode(' ');

        return [
            'line_points' => $points,
            'area_points' => "{$left},176 {$points} {$right},176",
            'has_data' => $hasData,
            'labels' => $period->map(fn ($date): string => $date->format('D'))->values()->all(),
        ];
    }

    private function mapCategoryIcon(?string $icon, ?string $categoryName): string
    {
        $icon = strtolower((string) $icon);
        $categoryName = strtolower((string) $categoryName);

        return match (true) {
            str_contains($icon, 'car') || str_contains($categoryName, 'transport') => 'transport',
            str_contains($icon, 'briefcase') || str_contains($icon, 'laptop') || str_contains($categoryName, 'salary') || str_contains($categoryName, 'freelance') => 'work',
            str_contains($icon, 'utensils') || str_contains($categoryName, 'food') => 'food',
            str_contains($icon, 'receipt') || str_contains($categoryName, 'bill') => 'bill',
            str_contains($icon, 'heart') || str_contains($categoryName, 'health') => 'health',
            str_contains($icon, 'play') || str_contains($categoryName, 'subscription') => 'subscription',
            str_contains($icon, 'arrow') || str_contains($categoryName, 'transfer') => 'transfer',
            default => 'shopping',
        };
    }
}
