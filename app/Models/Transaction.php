<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'note',
        'source',
        'transaction_date',
    ];

    /**
     * @return BelongsTo<User, Transaction>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, Transaction>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        return $query->where('transactions.user_id', $user instanceof User ? $user->id : $user);
    }

    /**
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('transactions.type', $type);
    }

    /**
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeBetweenDates(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query->whereBetween('transactions.transaction_date', [$start, $end]);
    }

    /**
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeForMonth(Builder $query, ?CarbonInterface $date = null): Builder
    {
        $date ??= now();

        return $query->betweenDates($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
    }

    public static function monthlyIncomeTotalFor(User $user, ?CarbonInterface $date = null): string
    {
        return (string) static::monthlySummaryFor($user, $date)['income_total'];
    }

    public static function monthlyExpenseTotalFor(User $user, ?CarbonInterface $date = null): string
    {
        return (string) static::monthlySummaryFor($user, $date)['expense_total'];
    }

    /**
     * @return array{income_total: string, expense_total: string, net_total: string}
     */
    public static function monthlySummaryFor(User $user, ?CarbonInterface $date = null): array
    {
        $summary = static::query()
            ->forUser($user)
            ->forMonth($date)
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'expense' THEN transactions.amount ELSE 0 END), 0) as expense_total")
            ->first();

        $income = (string) ($summary?->income_total ?? 0);
        $expense = (string) ($summary?->expense_total ?? 0);

        return [
            'income_total' => $income,
            'expense_total' => $expense,
            'net_total' => (string) ($income - $expense),
        ];
    }

    public static function currentBalanceFor(User $user): string
    {
        $balance = static::query()
            ->forUser($user)
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'income' THEN transactions.amount ELSE -transactions.amount END), 0) as balance")
            ->value('balance');

        return (string) ($balance ?? 0);
    }

    public static function categorySpendingBreakdownFor(User $user, ?CarbonInterface $date = null)
    {
        return static::query()
            ->forUser($user)
            ->forMonth($date)
            ->ofType('expense')
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
            ->get();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
