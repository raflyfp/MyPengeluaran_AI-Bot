<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvaFinanceAssistant
{
    public function reply(User $user, string $message): string
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            return $this->fallbackReply($user, $message);
        }

        try {
            $response = Http::timeout(8)
                ->retry(2, 500)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->asJson()
                ->post($this->endpoint(), $this->payload($user, $message));

            if ($response->failed()) {
                Log::warning('Eva assistant request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackReply($user, $message);
            }

            $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));

            if ($text === '') {
                return $this->fallbackReply($user, $message);
            }

            return $this->cleanReply($text);
        } catch (\Throwable $exception) {
            Log::warning('Eva assistant exception.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackReply($user, $message);
        }
    }

    private function endpoint(): string
    {
        $model = config('services.gemini.model', 'gemini-3.5-flash');

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user, string $message): array
    {
        return [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->prompt($user, $message)],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.35,
            ],
        ];
    }

    private function prompt(User $user, string $message): string
    {
        $context = json_encode($this->financeContext($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Kamu adalah Eva, AI finance assistant pribadi di dalam bot Telegram bernama Eva-Assist.

Identitas:
- Nama kamu Eva.
- Kamu bukan ChatGPT.
- Kamu hanya fokus pada keuangan pribadi: transaksi, pemasukan, pengeluaran, budgeting, tabungan, cashflow, kebiasaan finansial, dan insight finansial.

Gaya:
- Bahasa Indonesia casual.
- Ramah, santai, cerdas, modern.
- Jawaban singkat, natural, maksimal 3 kalimat pendek.
- Emoji boleh secukupnya, jangan berlebihan.
- Jangan terdengar seperti customer service formal.
- Jangan pakai markdown table.

Aturan penting:
- Jangan mengarang transaksi, nominal, statistik, atau data palsu.
- Gunakan hanya data pada finance_context.
- Kalau data belum cukup, katakan jujur.
- Kalau user bertanya di luar topik keuangan, balas singkat: "Aku lebih fokus bantu urusan keuangan pribadi ya 💰"
- Jangan memberikan nasihat finansial ekstrem.
- Jika user bertanya rencana beli barang, rekomendasi budget, min/max budget, atau "aman gak", jawab sebagai konsultasi budget.
- Untuk konsultasi pembelian, bandingkan nominal rencana beli dengan current_balance dan net_cashflow bulan ini.
- Jika user meminta rekomendasi budget tanpa nominal, beri range konservatif berdasarkan current_balance dan net_cashflow bulan ini.
- Jika user meminta rekomendasi barang sesuai budget, beri rekomendasi tipe/kelas barang dan prioritas spesifikasi, bukan klaim harga live marketplace.
- Jangan mengarang harga terbaru, stok, promo, atau link toko. Kalau butuh harga real-time, bilang perlu cek marketplace dulu.
- Jangan mencatat rencana pembelian sebagai transaksi. Beri saran aman/tunda/turunkan budget secara ringan.

finance_context:
{$context}

Pesan user:
"{$message}"

Balas sebagai Eva.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function financeContext(User $user): array
    {
        $today = $this->summary($user, 'today');
        $month = $this->summary($user, 'month');

        return [
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'current_balance' => $this->currentBalance($user),
            'today' => $today,
            'month' => $month,
            'has_enough_data' => $month['transaction_count'] > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(User $user, string $period): array
    {
        [$start, $end] = $this->range($period);

        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$start->utc(), $end->utc()]);

        $income = (float) (clone $transactions)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $transactions)->where('type', 'expense')->sum('amount');
        $count = (clone $transactions)->count();

        $topCategory = (clone $transactions)
            ->where('transactions.type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COALESCE(SUM(transactions.amount), 0) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->first();

        return [
            'income' => $income,
            'expense' => $expense,
            'net_cashflow' => $income - $expense,
            'transaction_count' => $count,
            'top_expense_category' => $topCategory ? [
                'name' => $topCategory->name,
                'amount' => (float) $topCategory->total,
            ] : null,
        ];
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function range(string $period): array
    {
        $now = CarbonImmutable::now(config('app.timezone', 'Asia/Jakarta'));

        if ($period === 'today') {
            return [$now->startOfDay(), $now->endOfDay()];
        }

        return [$now->startOfMonth(), $now->endOfMonth()];
    }

    private function fallbackReply(User $user, string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'halo') || str_contains($message, 'hai') || str_contains($message, 'hi')) {
            return 'Halo 👋 Ada yang mau dicatat hari ini?';
        }

        if (! $this->looksFinanceRelated($message)) {
            return 'Aku lebih fokus bantu urusan keuangan pribadi ya 💰';
        }

        $month = $this->summary($user, 'month');

        if ($this->looksPurchaseQuestion($message)) {
            return $this->fallbackPurchaseAdvice($user, $message, $month);
        }

        if ($month['transaction_count'] === 0) {
            return 'Aku belum punya cukup data transaksi untuk analisis itu 😄';
        }

        $top = $month['top_expense_category'];
        $suffix = $top ? ' Kategori terbesar: '.$top['name'].' '.$this->rupiah($top['amount'], $user).' ' : '';

        return 'Bulan ini pengeluaranmu '.$this->rupiah($month['expense'], $user).'.'.$suffix;
    }

    private function looksFinanceRelated(string $message): bool
    {
        $keywords = [
            'uang',
            'keuangan',
            'pengeluaran',
            'pemasukan',
            'income',
            'expense',
            'cashflow',
            'saldo',
            'budget',
            'tabungan',
            'hemat',
            'boros',
            'transaksi',
            'kategori',
            'gaji',
            'jajan',
            'makan',
            'kopi',
            'bayar',
            'tagihan',
            'beli',
            'barang',
            'keyboard',
            'laptop',
            'hp',
            'mouse',
            'monitor',
            'keyboard',
            'earphone',
            'headset',
            'rekomendasi',
            'aman',
            'worth',
            'min',
            'max',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function currentBalance(User $user): float
    {
        $summary = $user->transactions()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as balance")
            ->first();

        return (float) ($summary?->balance ?? 0);
    }

    private function looksPurchaseQuestion(string $message): bool
    {
        $keywords = [
            'beli',
            'budget',
            'rekomendasi',
            'aman',
            'worth',
            'barang',
            'mouse',
            'keyboard',
            'monitor',
            'laptop',
            'hp',
            'earphone',
            'headset',
            'min max',
            'minimal',
            'maksimal',
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

    /**
     * @param  array<string, mixed>  $month
     */
    private function fallbackPurchaseAdvice(User $user, string $message, array $month): string
    {
        $amount = $this->extractAmount($message);
        $balance = $this->currentBalance($user);
        $netCashflow = (float) $month['net_cashflow'];

        if ($balance <= 0 && $month['transaction_count'] === 0) {
            return 'Aku belum punya cukup data transaksi buat nilai aman atau nggaknya 😄';
        }

        if (! $amount) {
            $safeMax = max(min($balance * 0.1, max($netCashflow * 0.3, 0)), 0);

            if ($safeMax <= 0) {
                return 'Untuk sekarang Eva belum bisa kasih angka aman. Cashflow bulan ini belum cukup jelas, jadi mending tentukan budget kecil dulu.';
            }

            return 'Kalau lihat cashflow sekarang, budget aman kira-kira sampai '.$this->rupiah($safeMax, $user).'. Kalau mau lebih dari itu, Eva saranin tunggu pemasukan berikutnya.';
        }

        if ($amount <= max($netCashflow * 0.3, 0) || $amount <= max($balance * 0.1, 0)) {
            return 'Menurut Eva, '.$this->rupiah($amount, $user).' masih cukup aman kalau memang barangnya kepakai. Tetap pastikan kebutuhan utama bulan ini sudah aman ya.';
        }

        if ($amount <= max($netCashflow * 0.6, 0) || $amount <= max($balance * 0.2, 0)) {
            return 'Masih bisa, tapi agak mepet. Eva saranin pasang budget maksimal '.$this->rupiah($amount, $user).' dan jangan nambah aksesori dulu.';
        }

        return 'Untuk sekarang agak berat di cashflow. Lebih aman tunda dulu atau cari opsi di bawah '.$this->rupiah($amount * 0.7, $user).'.';
    }

    private function extractAmount(string $message): ?float
    {
        $normalized = strtolower(str_replace(',', '.', $message));

        if (! preg_match('/(\d+(?:\.\d+)?)\s*(rb|ribu|k|jt|juta|mio|m)?\b/i', $normalized, $matches)) {
            return null;
        }

        $amount = (float) $matches[1];
        $suffix = $matches[2] ?? '';

        return match ($suffix) {
            'rb', 'ribu', 'k' => $amount * 1000,
            'jt', 'juta', 'mio', 'm' => $amount * 1000000,
            default => $amount,
        };
    }

    private function cleanReply(string $text): string
    {
        $text = strip_tags($text, '<b><i><code>');
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function rupiah(float|int|string $amount, ?User $user = null): string
    {
        $currency = $user?->currency ?? 'Rp';

        return $currency . ' ' . number_format((float) $amount, 0, ',', '.');
    }
}
