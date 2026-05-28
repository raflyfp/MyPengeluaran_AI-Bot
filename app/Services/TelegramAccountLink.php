<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TelegramAccountLink
{
    private const TOKEN_LENGTH = 12;

    /**
     * @return array{token: string|null, command: string|null, url: string|null, expires_at: string|null}
     */
    public function forUser(User $user): array
    {
        if (filled($user->telegram_user_id) || filled($user->telegram_chat_id)) {
            return [
                'token' => null,
                'command' => null,
                'url' => null,
                'expires_at' => null,
            ];
        }

        if (
            ! $user->telegram_link_token
            || strlen($user->telegram_link_token) !== self::TOKEN_LENGTH
            || $user->telegram_link_token_expires_at?->isPast()
        ) {
            $user->forceFill([
                'telegram_link_token' => Str::random(self::TOKEN_LENGTH),
                'telegram_link_token_expires_at' => now()->addMinutes(30),
            ])->save();
        }

        $command = '/start '.$user->telegram_link_token;
        $botUsername = trim((string) config('services.telegram.bot_username'));
        $url = $botUsername !== ''
            ? 'https://t.me/'.ltrim($botUsername, '@').'?start='.$user->telegram_link_token
            : null;

        return [
            'token' => $user->telegram_link_token,
            'command' => $command,
            'url' => $url,
            'expires_at' => $user->telegram_link_token_expires_at?->timezone(config('app.timezone'))->format('H:i'),
        ];
    }
}
