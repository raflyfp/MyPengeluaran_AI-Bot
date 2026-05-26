<?php

namespace App\Services;

use App\Models\BotMessage;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * @param  array{type: string, amount: float|null, note: string, category_hint: string, confidence: float, raw_message: string}  $parsed
     * @return array{bot_message: BotMessage, transaction: Transaction|null}
     */
    public function storeFromTelegramMessage(User $user, string $message, array $parsed, array $telegramMeta = []): array
    {
        return DB::transaction(function () use ($user, $message, $parsed, $telegramMeta): array {
            $botMessage = BotMessage::query()->create([
                'user_id' => $user->id,
                'platform' => 'telegram',
                'message' => $message,
                'parsed_data' => [
                    ...$parsed,
                    'telegram' => $telegramMeta,
                ],
                'status' => $parsed['amount'] ? 'parsed' : 'failed',
            ]);

            if (! $parsed['amount']) {
                return [
                    'bot_message' => $botMessage,
                    'transaction' => null,
                ];
            }

            $category = $this->matchCategory($parsed['category_hint'], $parsed['type']);

            $transaction = $user->transactions()->create([
                'category_id' => $category->id,
                'type' => $parsed['type'],
                'amount' => $parsed['amount'],
                'note' => $parsed['note'],
                'source' => 'telegram',
                'transaction_date' => now(),
            ]);

            return [
                'bot_message' => $botMessage,
                'transaction' => $transaction,
            ];
        });
    }

    public function matchCategory(string $categoryHint, string $type): Category
    {
        $category = Category::query()
            ->where('type', $type)
            ->where(function ($query) use ($categoryHint): void {
                $query
                    ->whereRaw('LOWER(name) = ?', [strtolower($categoryHint)])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.strtolower($categoryHint).'%']);
            })
            ->first();

        if ($category) {
            return $category;
        }

        return Category::query()->firstOrCreate([
            'name' => $categoryHint,
            'type' => $type,
        ], [
            'icon' => $type === 'income' ? 'wallet' : 'receipt',
        ]);
    }
}
