<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Services\EvaFinanceAssistant;
use App\Services\GeminiFinanceMessageParser;
use App\Services\TelegramBotClient;
use App\Services\TelegramUserResolver;
use App\Services\TransactionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        // Laravel service container otomatis membuat GeminiFinanceMessageParser beserta fallback parser-nya.
        GeminiFinanceMessageParser $parser,
        EvaFinanceAssistant $evaAssistant,
        TelegramUserResolver $userResolver,
        TransactionService $transactionService,
        TelegramBotClient $telegram,
    ): JsonResponse {
        if (! $this->hasValidTelegramSecret($request)) {
            Log::warning('Telegram webhook rejected because secret token is invalid.', [
                'ip' => $request->ip(),
                'update_id' => data_get($request->all(), 'update_id'),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invalid Telegram webhook secret.',
            ], 403);
        }

        // Telegram bisa mengirim message biasa atau edited_message; keduanya diproses sama.
        $update = $request->all();
        $callbackPayload = data_get($update, 'callback_query');
        $messagePayload = data_get($update, 'message') ?? data_get($update, 'edited_message');
        $text = trim((string) data_get($messagePayload, 'text', ''));
        $chatId = data_get($messagePayload, 'chat.id');

        Log::info('Telegram webhook received.', [
            'update_id' => data_get($update, 'update_id'),
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        if ($callbackPayload) {
            return $this->handleCallback($callbackPayload, $userResolver, $telegram);
        }

        // Abaikan update non-teks seperti sticker, gambar, atau event lain.
        if ($text === '') {
            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'No text message found.',
            ]);
        }

        // Command bantuan tidak diproses AI, cukup balas panduan singkat Eva.
        if ($this->isCommand($text, 'start') || $this->isCommand($text, 'help')) {
            $telegram->sendMessage($chatId, $this->helpText(), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Help command handled.',
            ]);
        }

        if ($this->isCommand($text, 'categories')) {
            $telegram->sendMessage($chatId, $this->categoriesText(), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Categories command handled.',
            ]);
        }

        // Resolve user menentukan transaksi Telegram ini masuk ke akun MyPengeluaran siapa.
        $user = $userResolver->resolve($update);

        if (! $user) {
            $telegram->sendMessage($chatId, 'Akun MyPengeluaran belum ditemukan untuk bot ini.');

            return response()->json([
                'ok' => false,
                'message' => 'No user available for Telegram webhook.',
            ], 422);
        }

        if ($this->isCommand($text, 'today')) {
            $telegram->sendMessage($chatId, $this->summaryText($user, 'today'), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Today summary command handled.',
            ]);
        }

        if ($this->isCommand($text, 'summary') || $this->isCommand($text, 'month')) {
            $telegram->sendMessage($chatId, $this->summaryText($user, 'month'), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Month summary command handled.',
            ]);
        }

        if ($this->isCommand($text, 'eva')) {
            return $this->handleEvaCommand($text, $update, $messagePayload, $chatId, $user, $parser, $evaAssistant, $transactionService, $telegram);
        }

        if ($this->isEvaAdvisoryQuestion($text)) {
            $telegram->sendMessage($chatId, $evaAssistant->reply($user, $text), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Eva freeform advisory handled.',
            ]);
        }

        // Di titik ini AI parsing jalan: nominal, type, note, dan kategori ditebak dari teks bebas user.
        $parsed = $parser->parse($text);

        if (! $parsed['amount']) {
            $telegram->sendMessage($chatId, $evaAssistant->reply($user, $text), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Eva freeform chat handled.',
                'parsed' => $parsed,
            ]);
        }

        // Hasil parsing dan metadata Telegram disimpan dalam satu transaksi database.
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

        // Balasan dikirim setelah database selesai, jadi user tahu inputnya berhasil atau gagal dibaca.
        $telegram->sendMessage($chatId, $this->replyText($result['transaction'], $parsed), $this->mainKeyboard());

        return response()->json([
            'ok' => true,
            'status' => $result['bot_message']->status,
            'parsed' => $parsed,
            'transaction_id' => $result['transaction']?->id,
            'bot_message_id' => $result['bot_message']->id,
        ]);
    }

    private function hasValidTelegramSecret(Request $request): bool
    {
        $secret = config('services.telegram.webhook_secret');

        if (! $secret) {
            return true;
        }

        return hash_equals((string) $secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'));
    }

    private function handleCallback(array $callbackPayload, TelegramUserResolver $userResolver, TelegramBotClient $telegram): JsonResponse
    {
        $callbackId = data_get($callbackPayload, 'id');
        $chatId = data_get($callbackPayload, 'message.chat.id');
        $data = (string) data_get($callbackPayload, 'data', '');
        $user = $userResolver->resolve(['callback_query' => $callbackPayload]);

        $telegram->answerCallbackQuery($callbackId, 'Siap');

        match ($data) {
            'bot:help' => $telegram->sendMessage($chatId, $this->helpText(), $this->mainKeyboard()),
            'bot:eva' => $telegram->sendMessage($chatId, $this->evaGreetingText(), $this->mainKeyboard()),
            'bot:examples' => $telegram->sendMessage($chatId, $this->examplesText(), $this->mainKeyboard()),
            'bot:categories' => $telegram->sendMessage($chatId, $this->categoriesText(), $this->mainKeyboard()),
            'bot:today' => $telegram->sendMessage($chatId, $user ? $this->summaryText($user, 'today') : 'Akun MyPengeluaran belum ditemukan.', $this->mainKeyboard()),
            'bot:month' => $telegram->sendMessage($chatId, $user ? $this->summaryText($user, 'month') : 'Akun MyPengeluaran belum ditemukan.', $this->mainKeyboard()),
            default => $telegram->sendMessage($chatId, 'Menu belum tersedia.', $this->mainKeyboard()),
        };

        return response()->json([
            'ok' => true,
            'callback' => $data,
        ]);
    }

    private function handleEvaCommand(
        string $text,
        array $update,
        array $messagePayload,
        int|string|null $chatId,
        User $user,
        GeminiFinanceMessageParser $parser,
        EvaFinanceAssistant $evaAssistant,
        TransactionService $transactionService,
        TelegramBotClient $telegram,
    ): JsonResponse {
        $message = $this->commandBody($text, 'eva');

        if ($message === '') {
            $telegram->sendMessage($chatId, $this->evaGreetingText(), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Eva greeting handled.',
            ]);
        }

        if ($this->isEvaAdvisoryQuestion($message)) {
            $telegram->sendMessage($chatId, $evaAssistant->reply($user, $message), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Eva advisory handled.',
            ]);
        }

        $parsed = $parser->parse($message);

        if (! $parsed['amount']) {
            $telegram->sendMessage($chatId, $evaAssistant->reply($user, $message), $this->mainKeyboard());

            return response()->json([
                'ok' => true,
                'ignored' => true,
                'reason' => 'Eva chat handled.',
                'parsed' => $parsed,
            ]);
        }

        $result = $transactionService->storeFromTelegramMessage($user, $message, $parsed, [
            'update_id' => data_get($update, 'update_id'),
            'message_id' => data_get($messagePayload, 'message_id'),
            'date' => data_get($messagePayload, 'date'),
            'chat_id' => data_get($messagePayload, 'chat.id'),
            'from_id' => data_get($messagePayload, 'from.id'),
            'username' => data_get($messagePayload, 'from.username'),
            'command' => '/eva',
        ]);

        $telegram->sendMessage($chatId, $this->replyText($result['transaction'], $parsed), $this->mainKeyboard());

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
        // Kalau amount null, bot message tetap tercatat sebagai failed tetapi transaksi tidak dibuat.
        if (! $transaction) {
            return implode("\n", [
                'Aku belum nemu nominalnya.',
                'Coba kirim kayak gini ya:',
                '<code>/eva makan 25000</code>',
                '<code>/eva kopi 18rb</code>',
                '<code>/eva gaji 5000000</code>',
                '',
                'Ketik /categories kalau mau lihat kategori.',
            ]);
        }

        $typeLabel = $parsed['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran';
        $emoji = $parsed['type'] === 'income' ? '💰' : '✅';
        $amount = 'Rp '.number_format((float) $parsed['amount'], 0, ',', '.');

        // Parser ditampilkan agar debug mudah: gemini berarti AI dipakai, regex berarti fallback.
        return implode("\n", [
            "{$emoji} Oke, {$typeLabel} <b>{$amount}</b> berhasil dicatat.",
            'Kategori: '.$parsed['category_hint'],
            'Catatan: '.$parsed['note'].' · '.$this->evaParserLabel($parsed),
        ]);
    }

    private function helpText(): string
    {
        return implode("\n", [
            '👋 Aku Eva, finance assistant kamu.',
            '',
            'Kamu bisa pakai:',
            '• <code>/eva makan 20rb</code>',
            '• <code>/eva gaji 2jt</code>',
            '• <code>/summary</code>',
            '• <code>/today</code>',
        ]);
    }

    private function examplesText(): string
    {
        return implode("\n", [
            '<b>Contoh chat ke Eva</b>',
            '',
            '<code>/eva kopi 18rb</code>',
            '<code>/eva makan malam 25000</code>',
            '<code>/eva gaji 5000000</code>',
            '<code>/eva bulan ini aku boros gak?</code>',
            '',
            'Eva akan catat transaksi atau kasih insight singkat.',
        ]);
    }

    private function categoriesText(): string
    {
        // Help mengambil kategori langsung dari database supaya daftar kategori selalu terbaru.
        $expenseCategories = Category::query()
            ->where('type', 'expense')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');
        $incomeCategories = Category::query()
            ->where('type', 'income')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');

        return implode("\n", [
            '<b>Kategori yang Eva kenal</b>',
            '',
            '<b>Expense:</b>',
            $expenseCategories ?: 'Belum ada kategori expense.',
            '',
            '<b>Income:</b>',
            $incomeCategories ?: 'Belum ada kategori income.',
        ]);
    }

    private function summaryText(User $user, string $period): string
    {
        [$start, $end, $title] = $this->summaryRange($period);

        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$start->utc(), $end->utc()]);

        $income = (clone $transactions)->where('type', 'income')->sum('amount');
        $expense = (clone $transactions)->where('type', 'expense')->sum('amount');
        $count = (clone $transactions)->count();
        $balance = $income - $expense;

        $topCategory = (clone $transactions)
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COALESCE(SUM(transactions.amount), 0) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->first();

        return implode("\n", [
            "📊 <b>{$title}</b>",
            '',
            'Pemasukan: <b>'.$this->rupiah($income).'</b>',
            'Pengeluaran: <b>'.$this->rupiah($expense).'</b>',
            'Net cashflow: <b>'.$this->rupiah($balance).'</b>',
            'Jumlah transaksi: '.$count,
            'Kategori terbesar: '.($topCategory ? $topCategory->name.' '.$this->rupiah((float) $topCategory->total) : '-'),
        ]);
    }

    private function evaGreetingText(): string
    {
        return implode("\n", [
            'Halo 👋 Aku Eva.',
            'Mau catat transaksi, cek pengeluaran, atau tanya budget?',
            '',
            'Coba: <code>budget beli mouse max berapa?</code>',
        ]);
    }

    private function evaChatText(User $user, string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'halo') || str_contains($message, 'hai') || str_contains($message, 'hi')) {
            return 'Halo 👋 Ada yang mau dicatat hari ini?';
        }

        if (str_contains($message, 'hari ini')) {
            return $this->summaryText($user, 'today');
        }

        if (
            str_contains($message, 'boros') ||
            str_contains($message, 'hemat') ||
            str_contains($message, 'bulan ini') ||
            str_contains($message, 'pengeluaran')
        ) {
            return $this->evaInsightText($user);
        }

        return implode("\n", [
            'Aku bisa bantu catat transaksi atau kasih insight singkat.',
            'Coba tulis: <code>/eva makan 20rb</code> atau <code>/summary</code>.',
        ]);
    }

    private function evaInsightText(User $user): string
    {
        [$start, $end] = $this->summaryRange('month');

        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$start->utc(), $end->utc()]);

        $income = (clone $transactions)->where('type', 'income')->sum('amount');
        $expense = (clone $transactions)->where('type', 'expense')->sum('amount');
        $topCategory = (clone $transactions)
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COALESCE(SUM(transactions.amount), 0) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->first();

        if ((float) $expense <= 0) {
            return 'Belum ada pengeluaran bulan ini, jadi aku belum bisa nilai boros atau hemat.';
        }

        if ((float) $income <= 0) {
            return 'Bulan ini sudah keluar '.$this->rupiah($expense).'. Belum ada pemasukan tercatat, jadi coba pantau dulu ya.';
        }

        $ratio = ((float) $expense / max((float) $income, 1)) * 100;
        $status = $ratio >= 80
            ? 'cukup tinggi, mulai agak perlu direm'
            : ($ratio >= 50 ? 'masih lumayan aman, tapi tetap pantau' : 'masih aman 👍');

        return implode("\n", [
            'Menurut Eva, pengeluaran bulan ini '.$status.'.',
            'Expense: <b>'.$this->rupiah($expense).'</b> dari income <b>'.$this->rupiah($income).'</b>.',
            'Kategori paling besar: '.($topCategory ? $topCategory->name.' '.$this->rupiah((float) $topCategory->total) : '-').'.',
        ]);
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable, string}
     */
    private function summaryRange(string $period): array
    {
        $now = CarbonImmutable::now(config('app.timezone', 'Asia/Jakarta'));

        if ($period === 'today') {
            return [$now->startOfDay(), $now->endOfDay(), 'Ringkasan hari ini'];
        }

        return [$now->startOfMonth(), $now->endOfMonth(), 'Ringkasan bulan ini'];
    }

    private function rupiah(float|int|string $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    private function isCommand(string $text, string $command): bool
    {
        return (bool) preg_match('/^\/'.preg_quote($command, '/').'(?:@\w+)?(?:\s|$)/i', $text);
    }

    private function commandBody(string $text, string $command): string
    {
        return trim(preg_replace('/^\/'.preg_quote($command, '/').'(?:@\w+)?\s*/i', '', $text) ?? '');
    }

    private function isEvaAdvisoryQuestion(string $message): bool
    {
        $message = strtolower($message);
        $keywords = [
            'aman gak',
            'aman ga',
            'aman nggak',
            'aman ngga',
            'boleh gak',
            'boleh ga',
            'worth',
            'rekomendasi',
            'budget',
            'min max',
            'minimal',
            'maksimal',
            'maximal',
            'mau beli',
            'pengen beli',
            'rencana beli',
            'kalau beli',
            'kalo beli',
            'sebaiknya',
            'saran',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function evaParserLabel(array $parsed): string
    {
        return ($parsed['parser'] ?? 'regex') === 'gemini'
            ? 'Eva catet'
            : 'Eva tidur';
    }

    /**
     * @return array<string, mixed>
     */
    private function mainKeyboard(): array
    {
        return [];
    }
}
