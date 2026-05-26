<?php

namespace App\Http\Controllers;

use App\Services\FinanceMessageParser;
use App\Services\TelegramBotClient;
use App\Services\TelegramUserResolver;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        FinanceMessageParser $parser,
        TelegramUserResolver $userResolver,
        TransactionService $transactionService,
        TelegramBotClient $telegram,
    ): JsonResponse {
        $update = $request->all();
        $messagePayload = data_get($update, 'message') ?? data_get($update, 'edited_message');
        $text = trim((string) data_get($messagePayload, 'text', ''));
        $chatId = data_get($messagePayload, 'chat.id');

        Log::info('Telegram webhook received.', [
            'update_id' => data_get($update, 'update_id'),
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        if ($text === '') {
            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'No text message found.',
            ]);
        }

        if (str_starts_with($text, '/start') || str_starts_with($text, '/help')) {
            $telegram->sendMessage($chatId, implode("\n", [
                '<b>MyPengeluaran Bot</b>',
                'Kirim transaksi dengan format bebas:',
                '',
                '- makan 25000',
                '- kopi 18rb',
                '- gaji 5000000',
                '',
                'Aku akan otomatis deteksi nominal, tipe transaksi, dan kategori.',
            ]));

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Help command handled.',
            ]);
        }

        $user = $userResolver->resolve($update);

        if (! $user) {
            $telegram->sendMessage($chatId, 'Akun MyPengeluaran belum ditemukan untuk bot ini.');

            return response()->json([
                'ok' => false,
                'message' => 'No user available for Telegram webhook.',
            ], 422);
        }

        $parsed = $parser->parse($text);
        $result = $transactionService->storeFromTelegramMessage($user, $text, $parsed, [
            'update_id' => data_get($update, 'update_id'),
            'message_id' => data_get($messagePayload, 'message_id'),
            'date' => data_get($messagePayload, 'date'),
            'chat_id' => data_get($messagePayload, 'chat.id'),
            'from_id' => data_get($messagePayload, 'from.id'),
            'username' => data_get($messagePayload, 'from.username'),
        ]);

        Log::info('Telegram webhook processed.', [
            'status' => $result['bot_message']->status,
            'bot_message_id' => $result['bot_message']->id,
            'transaction_id' => $result['transaction']?->id,
        ]);

        $telegram->sendMessage($chatId, $this->replyText($result['transaction'], $parsed));

        return response()->json([
            'ok' => true,
            'status' => $result['bot_message']->status,
            'parsed' => $parsed,
            'transaction_id' => $result['transaction']?->id,
            'bot_message_id' => $result['bot_message']->id,
        ]);
    }

    private function replyText($transaction, array $parsed): string
    {
        if (! $transaction) {
            return implode("\n", [
                'Aku belum bisa membaca nominalnya.',
                'Coba format seperti:',
                '- makan 25000',
                '- kopi 18rb',
                '- gaji 5000000',
            ]);
        }

        $typeLabel = $parsed['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran';
        $amount = 'Rp '.number_format((float) $parsed['amount'], 0, ',', '.');

        return implode("\n", [
            '<b>Transaksi berhasil dicatat</b>',
            "{$typeLabel}: <b>{$amount}</b>",
            'Kategori: '.$parsed['category_hint'],
            'Catatan: '.$parsed['note'],
        ]);
    }
}
