<?php

namespace App\Services;

use App\Models\User;

class TelegramUserResolver
{
    public function resolve(array $update): ?User
    {
        $configuredEmail = config('services.telegram.default_user_email');

        if ($configuredEmail) {
            $user = User::query()->where('email', $configuredEmail)->first();

            if ($user) {
                return $user;
            }
        }

        return User::query()->oldest('id')->first();
    }
}
