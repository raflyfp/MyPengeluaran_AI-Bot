<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotClient
{
    public function sendMessage(int|string|null $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $chatId) {
            Log::warning('Telegram reply skipped because bot token or chat id is missing.', [
                'has_token' => (bool) $token,
                'chat_id' => $chatId,
            ]);

            return;
        }

        $response = Http::timeout(5)
            ->asJson()
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

        if ($response->failed()) {
            Log::warning('Telegram reply failed.', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
