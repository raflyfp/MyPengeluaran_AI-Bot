<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Seed realistic demo transactions for the default user.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $transactions = [
            [
                'category' => 'Salary',
                'type' => 'income',
                'amount' => 9800000,
                'note' => 'Monthly salary May 2026',
                'source' => 'manual',
                'transaction_date' => now()->startOfMonth()->addDays(24)->setTime(9, 0),
            ],
            [
                'category' => 'Freelance',
                'type' => 'income',
                'amount' => 3250000,
                'note' => 'Freelance dashboard UI project',
                'source' => 'whatsapp',
                'transaction_date' => now()->subDay()->setTime(18, 20),
            ],
            [
                'category' => 'Groceries',
                'type' => 'expense',
                'amount' => 384500,
                'note' => 'Ranch Market groceries',
                'source' => 'telegram',
                'transaction_date' => now()->setTime(10, 42),
            ],
            [
                'category' => 'Food & Drink',
                'type' => 'expense',
                'amount' => 58000,
                'note' => 'Ayam geprek lunch',
                'source' => 'telegram',
                'transaction_date' => now()->setTime(12, 30),
            ],
            [
                'category' => 'Transport',
                'type' => 'expense',
                'amount' => 72000,
                'note' => 'Grab ride to client meeting',
                'source' => 'manual',
                'transaction_date' => now()->subDay()->setTime(12, 5),
            ],
            [
                'category' => 'Bills & Utilities',
                'type' => 'expense',
                'amount' => 300000,
                'note' => 'PLN token top up',
                'source' => 'whatsapp',
                'transaction_date' => now()->setTime(7, 30),
            ],
            [
                'category' => 'Subscription',
                'type' => 'expense',
                'amount' => 150000,
                'note' => 'Netflix monthly plan',
                'source' => 'system',
                'transaction_date' => now()->subDay()->setTime(0, 12),
            ],
            [
                'category' => 'Health',
                'type' => 'expense',
                'amount' => 217500,
                'note' => 'Siloam pharmacy vitamins',
                'source' => 'manual',
                'transaction_date' => now()->subDays(2)->setTime(16, 44),
            ],
            [
                'category' => 'Savings Transfer',
                'type' => 'expense',
                'amount' => 310000,
                'note' => 'Transfer to emergency savings',
                'source' => 'manual',
                'transaction_date' => now()->subDays(2)->setTime(9, 5),
            ],
        ];

        foreach ($transactions as $transaction) {
            $category = Category::query()
                ->where('name', $transaction['category'])
                ->where('type', $transaction['type'])
                ->firstOrFail();

            Transaction::query()->firstOrCreate([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'amount' => $transaction['amount'],
                'note' => $transaction['note'],
                'transaction_date' => $transaction['transaction_date'],
            ], [
                'type' => $transaction['type'],
                'source' => $transaction['source'],
            ]);
        }
    }
}
