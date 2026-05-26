<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
                'category_spending_breakdown' => $this->formatCategoryBreakdown($categoryBreakdown, $monthlyExpenseTotal),
            ],
            'transactions' => Transaction::query()
                ->with('category')
                ->forUser($user)
                ->latest('transaction_date')
                ->limit(5)
                ->get()
                ->map(fn (Transaction $transaction): array => $this->formatTransaction($transaction)),
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
                'name' => $category->name,
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
        return [
            'title' => $transaction->note ?: $transaction->category?->name ?: 'Transaction',
            'category' => $transaction->category?->name ?? 'Uncategorized',
            'time' => $transaction->transaction_date->diffForHumans(),
            'amount' => $this->formatRupiah($transaction->amount, true, $transaction->type),
            'type' => $transaction->type,
            'icon' => $this->mapCategoryIcon($transaction->category?->icon, $transaction->category?->name),
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
