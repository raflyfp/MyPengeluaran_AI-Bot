<?php

namespace App\Services;

use App\Models\User;

class TelegramUserResolver
{
    public function resolve(array $update): ?User
    {
        // Untuk versi awal, semua pesan bot diarahkan ke email default dari .env.
        $configuredEmail = config('services.telegram.default_user_email');

        if ($configuredEmail) {
            $user = User::query()->where('email', $configuredEmail)->first();

            if ($user) {
                return $user;
            }
        }

        // Fallback supaya bot tetap bisa dipakai di demo walaupun email default belum diisi.
        return User::query()->oldest('id')->first();
    }
}
