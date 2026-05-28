<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;

class TelegramUserResolver
{
    public function resolve(array $update): ?User
    {
        $telegramUserId = $this->telegramUserId($update);

        if ($telegramUserId) {
            $user = User::query()
                ->where('telegram_user_id', $telegramUserId)
                ->where('is_active', UserStatus::Active->value)
                ->first();

            if ($user) {
                return $user;
            }
        }

        $telegramChatId = $this->telegramChatId($update);

        if ($telegramChatId) {
            $user = User::query()
                ->where('telegram_chat_id', $telegramChatId)
                ->where('is_active', UserStatus::Active->value)
                ->first();

            if ($user) {
                return $user;
            }
        }

        if (! $telegramUserId && ! $telegramChatId) {
            // Fallback lama hanya dipakai kalau payload tidak membawa identitas Telegram.
            $configuredEmail = config('services.telegram.default_user_email');

            if ($configuredEmail) {
                $user = User::query()
                    ->where('email', $configuredEmail)
                    ->where('is_active', UserStatus::Active->value)
                    ->first();

                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }

    private function telegramUserId(array $update): ?string
    {
        $id = data_get($update, 'message.from.id')
            ?? data_get($update, 'edited_message.from.id')
            ?? data_get($update, 'callback_query.from.id');

        return $id !== null ? (string) $id : null;
    }

    private function telegramChatId(array $update): ?string
    {
        $id = data_get($update, 'message.chat.id')
            ?? data_get($update, 'edited_message.chat.id')
            ?? data_get($update, 'callback_query.message.chat.id');

        return $id !== null ? (string) $id : null;
    }
}
