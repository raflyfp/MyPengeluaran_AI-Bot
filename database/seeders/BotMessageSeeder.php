<?php

namespace Database\Seeders;

use App\Models\BotMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class BotMessageSeeder extends Seeder
{
    /**
     * Seed demo bot messages that mirror parsed expense flows.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $messages = [
            [
                'platform' => 'telegram',
                'message' => 'makan siang 58rb ayam geprek',
                'parsed_data' => [
                    'type' => 'expense',
                    'amount' => 58000,
                    'category' => 'Food & Dining',
                    'merchant' => 'Ayam Geprek Sambal Ijo',
                    'confidence' => 0.98,
                ],
                'status' => 'parsed',
                'created_at' => now()->subMinutes(2),
            ],
            [
                'platform' => 'whatsapp',
                'message' => 'freelance landing page masuk 1.25jt',
                'parsed_data' => [
                    'type' => 'income',
                    'amount' => 1250000,
                    'category' => 'Freelance',
                    'merchant' => 'Freelance Client',
                    'confidence' => 0.94,
                ],
                'status' => 'parsed',
                'created_at' => now()->subMinutes(18),
            ],
            [
                'platform' => 'telegram',
                'message' => 'Ranch Market groceries 384500',
                'parsed_data' => [
                    'type' => 'expense',
                    'amount' => 384500,
                    'category' => 'Groceries',
                    'merchant' => 'Ranch Market',
                    'confidence' => 0.96,
                ],
                'status' => 'parsed',
                'created_at' => now()->subMinutes(42),
            ],
            [
                'platform' => 'whatsapp',
                'message' => 'tolong catat token listrik 300rb',
                'parsed_data' => [
                    'type' => 'expense',
                    'amount' => 300000,
                    'category' => 'Bills & Utilities',
                    'merchant' => 'PLN',
                    'confidence' => 0.91,
                ],
                'status' => 'received',
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($messages as $message) {
            BotMessage::query()->firstOrCreate([
                'user_id' => $user->id,
                'platform' => $message['platform'],
                'message' => $message['message'],
            ], [
                'parsed_data' => $message['parsed_data'],
                'status' => $message['status'],
                'created_at' => $message['created_at'],
                'updated_at' => $message['created_at'],
            ]);
        }
    }
}
