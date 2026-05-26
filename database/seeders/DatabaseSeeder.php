<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'email' => 'genzirafp@gmail.com',
        ], [
            'name' => 'Rafly',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->call([
            CategorySeeder::class,
            // TransactionSeeder::class,
            // BotMessageSeeder::class,
        ]);
    }
}
