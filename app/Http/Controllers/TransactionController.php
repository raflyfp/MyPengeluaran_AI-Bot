<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $type = $request->string('type')->toString();
        $search = $request->string('search')->toString();
        $normalizedSearch = mb_strtolower($search);

        $transactions = Transaction::query()
            ->with('category')
            ->forUser($user)
            ->when(in_array($type, ['income', 'expense'], true), fn ($query) => $query->ofType($type))
            ->when($search !== '', function ($query) use ($normalizedSearch): void {
                $query->where(function ($query) use ($normalizedSearch): void {
                    $query
                        ->whereRaw('LOWER(transactions.note) LIKE ?', ["%{$normalizedSearch}%"])
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->whereRaw('LOWER(categories.name) LIKE ?', ["%{$normalizedSearch}%"]));
                });
            })
            ->latest('transaction_date')
            ->paginate(20)
            ->withQueryString();

        $monthlySummary = Transaction::monthlySummaryFor($user);
        $monthlyCounts = Transaction::query()
            ->forUser($user)
            ->forMonth()
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN 1 ELSE 0 END), 0) as income_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN 1 ELSE 0 END), 0) as expense_count")
            ->first();

        return view('transactions.index', [
            'transactions' => $transactions,
            'categories' => Category::query()->orderBy('type')->orderBy('name')->get(),
            'transactionGroups' => $this->formatTransactionGroups($transactions->getCollection()),
            'summary' => [
                'monthly_income_total' => $monthlySummary['income_total'],
                'monthly_expense_total' => $monthlySummary['expense_total'],
                'formatted_monthly_income_total' => $this->formatRupiah($monthlySummary['income_total']),
                'formatted_monthly_expense_total' => $this->formatRupiah($monthlySummary['expense_total']),
                'current_balance' => Transaction::currentBalanceFor($user),
                'category_spending_breakdown' => Transaction::categorySpendingBreakdownFor($user),
                'income_count' => (int) ($monthlyCounts?->income_count ?? 0),
                'expense_count' => (int) ($monthlyCounts?->expense_count ?? 0),
            ],
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->transactions()->create([
            ...$validated,
            'source' => $validated['source'] ?? 'manual',
            'transaction_date' => $this->parseLocalDateTime($validated['transaction_date']),
        ]);

        return back()
            ->with('status', 'Transaction created successfully.');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['transaction_date'])) {
            $validated['transaction_date'] = $this->parseLocalDateTime($validated['transaction_date']);
        }

        $transaction->update($validated);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaction updated successfully.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaction deleted successfully.');
    }

    /**
     * @param  Collection<int, Transaction>  $transactions
     * @return Collection<int, array{date: string, summary: string, items: Collection<int, array<string, mixed>>}>
     */
    private function formatTransactionGroups(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction): string => $this->dateHeading($transaction))
            ->map(function (Collection $items, string $date): array {
                $netAmount = $items->sum(fn (Transaction $transaction): float => $transaction->type === 'income'
                    ? (float) $transaction->amount
                    : -((float) $transaction->amount));

                return [
                    'date' => $date,
                    'summary' => $this->formatRupiah($netAmount, true, $netAmount >= 0 ? 'income' : 'expense'),
                    'items' => $items->map(fn (Transaction $transaction): array => $this->formatTransaction($transaction)),
                ];
            })
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatTransaction(Transaction $transaction): array
    {
        $transactionDate = $transaction->transaction_date->copy()->timezone(config('app.timezone'));

        return [
            'id' => $transaction->id,
            'title' => $transaction->note ?: $this->displayCategoryName($transaction->category?->name),
            'category' => $this->displayCategoryName($transaction->category?->name),
            'category_id' => $transaction->category_id,
            'time' => $transactionDate->format('H:i'),
            'date_full' => $transactionDate->format('l, d F Y'),
            'amount' => $this->formatRupiah($transaction->amount, true, $transaction->type),
            'raw_amount' => (float) $transaction->amount,
            'type' => $transaction->type,
            'source' => $transaction->source,
            'note' => $transaction->note,
            'transaction_date' => $transactionDate->format('Y-m-d\TH:i'),
            'icon' => $this->mapCategoryIcon($transaction->category?->icon, $transaction->category?->name),
            'tags' => implode(' ', [
                'all',
                $transaction->type,
                $transaction->source,
                $transaction->category?->name,
                $transaction->note,
            ]),
        ];
    }

    private function dateHeading(Transaction $transaction): string
    {
        $date = $transaction->transaction_date->copy()->timezone(config('app.timezone'));

        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isYesterday()) {
            return 'Yesterday';
        }

        return $date->format('l, d M');
    }

    private function formatRupiah(string|int|float|null $amount, bool $withSign = false, string $type = 'income'): string
    {
        $numericAmount = (float) ($amount ?? 0);
        $prefix = '';

        if ($withSign) {
            $prefix = $type === 'income' ? '+' : '-';
        }

        $currency = auth()->user()?->currency ?? 'Rp';

        return $prefix . $currency . ' ' . number_format(abs($numericAmount), 0, ',', '.');
    }

    private function mapCategoryIcon(?string $icon, ?string $categoryName): string
    {
        $icon = strtolower((string) $icon);
        $categoryName = strtolower((string) $categoryName);

        return match (true) {
            str_contains($icon, 'car') || str_contains($categoryName, 'transport') => 'transport',
            str_contains($icon, 'briefcase') || str_contains($icon, 'laptop') || str_contains($categoryName, 'salary') || str_contains($categoryName, 'freelance') => 'work',
            str_contains($icon, 'utensils') || str_contains($categoryName, 'food') || str_contains($categoryName, 'dining') => 'food',
            str_contains($icon, 'receipt') || str_contains($categoryName, 'bill') || str_contains($categoryName, 'utilities') => 'bill',
            str_contains($icon, 'heart') || str_contains($categoryName, 'health') => 'health',
            str_contains($icon, 'play') || str_contains($categoryName, 'subscription') => 'subscription',
            str_contains($icon, 'arrow') || str_contains($categoryName, 'transfer') => 'transfer',
            default => 'shopping',
        };
    }

    private function parseLocalDateTime(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value, config('app.timezone'))->utc();
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
