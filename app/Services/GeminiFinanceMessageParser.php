<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiFinanceMessageParser
{
    public function __construct(
        // Parser lama tetap disuntikkan sebagai cadangan saat AI tidak bisa dipakai.
        private readonly FinanceMessageParser $fallbackParser,
    ) {}

    /**
     * @return array{type: string, amount: float|null, note: string, category_hint: string, confidence: float, raw_message: string, parser: string, intent?: string}
     */
    public function parse(string $message): array
    {
        $apiKey = config('services.gemini.api_key');

        // Kalau API key belum diisi, bot tetap jalan memakai parser regex lama.
        if (! $apiKey) {
            return $this->fallback($message, 'gemini_api_key_missing');
        }

        try {
            // Kirim pesan mentah Telegram ke Gemini dan minta hasil JSON terstruktur.
            $response = Http::timeout(8)
                ->retry(2, 500)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->asJson()
                ->post($this->endpoint(), $this->payload($message));

            if ($response->failed()) {
                Log::warning('Gemini parser request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallback($message, 'gemini_http_'.$response->status());
            }

            $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            $parsed = $this->decodeJson($text);

            // Validasi output AI sebelum dipakai agar data transaksi tidak asal masuk database.
            if (! $this->isValidParsedTransaction($parsed)) {
                Log::warning('Gemini parser returned invalid transaction JSON.', [
                    'text' => $text,
                    'parsed' => $parsed,
                ]);

                return $this->fallback($message, 'gemini_invalid_json');
            }

            // Output AI dinormalisasi ke bentuk array yang dipakai TransactionService.
            return [
                'type' => $parsed['type'],
                'amount' => $parsed['amount'] !== null ? (float) $parsed['amount'] : null,
                'note' => trim((string) ($parsed['note'] ?: $message)),
                'category_hint' => $this->normalizeCategoryHint((string) $parsed['category_hint'], $parsed['type']),
                'confidence' => min(0.99, max(0.0, (float) ($parsed['confidence'] ?? 0.85))),
                'raw_message' => $message,
                'parser' => 'gemini',
                'intent' => (string) ($parsed['intent'] ?? 'record_transaction'),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Gemini parser exception.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($message, 'gemini_exception');
        }
    }

    private function endpoint(): string
    {
        $model = config('services.gemini.model', 'gemini-3.5-flash');

        // Endpoint REST Gemini: /models/{model}:generateContent.
        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $message): array
    {
        return [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->prompt($message)],
                    ],
                ],
            ],
            'generationConfig' => [
                // Paksa response berbentuk JSON supaya lebih aman untuk di-decode.
                'responseMimeType' => 'application/json',
            ],
        ];
    }

    private function prompt(string $message): string
    {
        // Kategori diambil langsung dari database agar Gemini memilih kategori yang valid.
        $expenseCategories = $this->categories('expense');
        $incomeCategories = $this->categories('income');

        return <<<PROMPT
You are the finance message parser for MyPengeluaran, an Indonesian personal finance app.
Parse this Telegram message into one JSON object for a finance Telegram bot.

Message:
"{$message}"

Rules:
- Return only valid JSON. No markdown.
- Return one JSON object, not an array.
- intent must be either "record_transaction" or "finance_advice".
- Use "record_transaction" only when the user is clearly reporting a completed income/expense transaction to save.
- Use "finance_advice" when the user is asking, comparing, planning to buy, asking if something is expensive/cheap/worth/affordable, asking for a budget recommendation, or asking for financial insight.
- If intent is "finance_advice", amount must be null even if the message contains a price, because it must not be saved as a transaction.
- type must be either "income" or "expense".
- amount must be numeric rupiah. Convert "18rb" to 18000, "1.5jt" to 1500000, "2 juta" to 2000000.
- If no clear amount exists, amount must be null and confidence below 0.5.
- category_hint must be one exact category from the allowed category lists.
- Use income categories only for income, expense categories only for expense.
- note should keep the original meaning in short Indonesian/English text.

Expense categories:
{$expenseCategories}

Income categories:
{$incomeCategories}

JSON schema:
{
  "intent": "record_transaction",
  "type": "expense",
  "amount": 25000,
  "note": "makan 25000",
  "category_hint": "Food & Drink",
  "confidence": 0.95
}
PROMPT;
    }

    private function categories(string $type): string
    {
        // Prompt sengaja tidak hardcode kategori agar kategori baru dari database otomatis ikut.
        return Category::query()
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $text): ?array
    {
        $text = trim($text);
        // Cadangan kalau model tetap membungkus JSON dengan markdown fence.
        $text = preg_replace('/^```json\s*|\s*```$/', '', $text) ?? $text;
        $decoded = json_decode($text, true);

        if (is_array($decoded) && array_is_list($decoded)) {
            $decoded = $decoded[0] ?? null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>|null  $parsed
     */
    private function isValidParsedTransaction(?array $parsed): bool
    {
        // Minimal kontrak data yang wajib ada sebelum transaksi dibuat.
        if (! $parsed) {
            return false;
        }

        if (! in_array($parsed['type'] ?? null, ['income', 'expense'], true)) {
            return false;
        }

        if (! array_key_exists('amount', $parsed)) {
            return false;
        }

        if ($parsed['amount'] !== null && ! is_numeric($parsed['amount'])) {
            return false;
        }

        return filled($parsed['category_hint'] ?? null);
    }

    private function normalizeCategoryHint(string $categoryHint, string $type): string
    {
        // Utamakan exact match dari tabel categories supaya perubahan nama kategori ikut terbaca.
        $category = Category::query()
            ->where('type', $type)
            ->whereRaw('LOWER(name) = ?', [strtolower($categoryHint)])
            ->first();

        if ($category) {
            return $category->name;
        }

        return match (strtolower($categoryHint)) {
            // Alias lama tetap diarahkan ke kategori baru.
            'food & dining', 'food and dining', 'food', 'drink', 'food and drink' => 'Food & Drink',
            default => $categoryHint,
        };
    }

    /**
     * @return array{type: string, amount: float|null, note: string, category_hint: string, confidence: float, raw_message: string, parser: string, fallback_reason: string}
     */
    private function fallback(string $message, string $reason): array
    {
        // Simpan alasan fallback di parsed_data agar gampang dicek dari database/log.
        return [
            ...$this->fallbackParser->parse($message),
            'parser' => 'regex',
            'fallback_reason' => $reason,
        ];
    }
}
