<?php

namespace App\Services;

use App\Models\BotMessage;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * @param  array{type: string, amount: float|null, note: string, category_hint: string, confidence: float, raw_message: string, parser?: string, fallback_reason?: string, intent?: string}  $parsed
     * @return array{bot_message: BotMessage, transaction: Transaction|null}
     */
    public function storeFromTelegramMessage(User $user, string $message, array $parsed, array $telegramMeta = []): array
    {
        // Bot message dan transaksi disimpan dalam DB transaction agar statusnya konsisten.
        return DB::transaction(function () use ($user, $message, $parsed, $telegramMeta): array {
            $botMessage = BotMessage::query()->create([
                'user_id' => $user->id,
                'platform' => 'telegram',
                'message' => $message,
                'parsed_data' => [
                    // parsed_data menyimpan hasil AI/fallback, termasuk parser dan fallback_reason.
                    ...$parsed,
                    'telegram' => $telegramMeta,
                ],
                'status' => $parsed['amount'] ? 'parsed' : 'failed',
            ]);

            // Jika AI/fallback tidak menemukan nominal, cukup log bot_messages tanpa membuat transaksi.
            if (! $parsed['amount']) {
                return [
                    'bot_message' => $botMessage,
                    'transaction' => null,
                ];
            }

            // category_hint dari AI dicocokkan lagi ke tabel categories sebelum transaksi dibuat.
            $category = $this->matchCategory($parsed['category_hint'], $parsed['type']);

            $transaction = $user->transactions()->create([
                'category_id' => $category->id,
                'type' => $parsed['type'],
                'amount' => $parsed['amount'],
                'note' => $parsed['note'],
                'source' => 'telegram',
                'transaction_date' => $this->telegramTransactionDate($telegramMeta),
            ]);

            return [
                'bot_message' => $botMessage,
                'transaction' => $transaction,
            ];
        });
    }

    public function matchCategory(string $categoryHint, string $type): Category
    {
        // Normalisasi alias lama/umum agar output AI tetap masuk kategori yang benar.
        $categoryHint = $this->normalizeCategoryHint($categoryHint);

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

        // Kalau kategori dari AI belum ada, buat kategori baru supaya transaksi tidak gagal.
        return Category::query()->firstOrCreate([
            'name' => $categoryHint,
            'type' => $type,
        ], [
            'icon' => $type === 'income' ? 'wallet' : 'receipt',
        ]);
    }

    private function normalizeCategoryHint(string $categoryHint): string
    {
        return match (strtolower($categoryHint)) {
            'food & dining', 'food and dining', 'food', 'drink', 'food and drink' => 'Food & Drink',
            default => $categoryHint,
        };
    }

    private function telegramTransactionDate(array $telegramMeta): CarbonImmutable
    {
        $timestamp = data_get($telegramMeta, 'date');

        // Telegram mengirim Unix timestamp UTC; simpan sebagai UTC agar aman untuk timezone-aware display.
        if (is_numeric($timestamp)) {
            return CarbonImmutable::createFromTimestamp((int) $timestamp, 'UTC');
        }

        // Fallback saat payload Telegram tidak membawa timestamp.
        return CarbonImmutable::now('UTC');
    }
}
