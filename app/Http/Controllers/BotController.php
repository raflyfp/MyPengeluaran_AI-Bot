<?php

namespace App\Http\Controllers;

use App\Models\BotMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BotController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $latestMessages = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->latest()
            ->limit(8)
            ->get();

        $totalMessages = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->count();

        $parsedMessages = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->where('status', 'parsed')
            ->count();

        $failedMessages = BotMessage::query()
            ->where('user_id', $user->id)
            ->where('platform', 'telegram')
            ->where('status', 'failed')
            ->count();

        return view('bot.index', [
            'telegram' => [
                'connected' => $totalMessages > 0,
                'last_sync' => $latestMessages->first()?->created_at?->diffForHumans() ?? 'Waiting for first message',
                'total_messages' => $totalMessages,
                'parsed_messages' => $parsedMessages,
                'failed_messages' => $failedMessages,
                'accuracy' => $totalMessages > 0 ? round(($parsedMessages / $totalMessages) * 100) : 0,
            ],
            'activities' => $latestMessages->map(fn (BotMessage $message): array => $this->formatActivity($message)),
        ]);
    }

    /**
     * @return array{channel: string, title: string, detail: string, amount: string, time: string, tone: string, status: string}
     */
    private function formatActivity(BotMessage $message): array
    {
        $parsed = $message->parsed_data ?? [];
        $type = data_get($parsed, 'type', 'expense');
        $amount = (float) data_get($parsed, 'amount', 0);
        $category = (string) data_get($parsed, 'category_hint', 'Uncategorized');

        return [
            'channel' => 'Telegram',
            'title' => $message->status === 'parsed' ? $this->titleForType($type) : 'Message needs review',
            'detail' => $message->message ?: $category,
            'amount' => $amount > 0 ? $this->formatRupiah($amount, true, $type) : 'Review',
            'time' => $message->created_at?->diffForHumans() ?? 'Just now',
            'tone' => $type === 'income' ? 'income' : 'expense',
            'status' => $message->status,
        ];
    }

    private function titleForType(string $type): string
    {
        return $type === 'income' ? 'Income logged' : 'Expense logged';
    }

    private function formatRupiah(string|int|float|null $amount, bool $withSign = false, string $type = 'income'): string
    {
        $numericAmount = (float) ($amount ?? 0);
        $prefix = '';

        if ($withSign) {
            $prefix = $type === 'income' ? '+' : '-';
        }

        return $prefix.'Rp '.number_format(abs($numericAmount), 0, ',', '.');
    }
}
