<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotClient
{
    /**
     * @param  array<string, mixed>  $replyMarkup
     */
    public function sendMessage(int|string|null $chatId, string $text, array $replyMarkup = []): void
    {
        // Token diambil dari config/services.php agar tidak ada secret hardcoded di kode.
        $token = config('services.telegram.bot_token');

        // Saat local/dev token atau chat id bisa kosong; cukup log warning agar webhook tidak crash.
        if (! $token || ! $chatId) {
            Log::warning('Telegram reply skipped because bot token or chat id is missing.', [
                'has_token' => (bool) $token,
                'chat_id' => $chatId,
            ]);

            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== []) {
            // reply_markup dipakai untuk inline button agar bot terasa lebih interaktif.
            $payload['reply_markup'] = $replyMarkup;
        }

        // Kirim balasan ke chat Telegram. parse_mode HTML dipakai untuk teks bold sederhana.
        $response = Http::timeout(5)
            ->asJson()
            ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

        // Gagal kirim balasan tidak membatalkan transaksi; error cukup dicatat di log.
        if ($response->failed()) {
            Log::warning('Telegram reply failed.', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    public function answerCallbackQuery(int|string|null $callbackQueryId, string $text = ''): void
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $callbackQueryId) {
            return;
        }

        // Telegram butuh callback dijawab agar loading spinner di tombol berhenti.
        $response = Http::timeout(5)
            ->asJson()
            ->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
            ]);

        if ($response->failed()) {
            Log::warning('Telegram callback answer failed.', [
                'callback_query_id' => $callbackQueryId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
