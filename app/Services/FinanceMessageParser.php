<?php

namespace App\Services;

class FinanceMessageParser
{
    /**
     * @return array{type: string, amount: float|null, note: string, category_hint: string, confidence: float, raw_message: string}
     */
    public function parse(string $message): array
    {
        $normalized = $this->normalize($message);

        if ($this->looksLikeFinanceQuestion($normalized)) {
            return [
                'type' => 'expense',
                'amount' => null,
                'note' => trim($message),
                'category_hint' => 'Other Expense',
                'confidence' => 0.25,
                'raw_message' => $message,
                'intent' => 'finance_advice',
            ];
        }

        $amount = $this->detectAmount($normalized);
        $type = $this->detectType($normalized);
        $categoryHint = $this->detectCategoryHint($normalized, $type);

        return [
            'type' => $type,
            'amount' => $amount,
            'note' => trim($message),
            'category_hint' => $categoryHint,
            'confidence' => $amount !== null ? 0.9 : 0.35,
            'raw_message' => $message,
            'intent' => $amount !== null ? 'record_transaction' : 'finance_advice',
        ];
    }

    private function normalize(string $message): string
    {
        return strtolower(trim(str_replace(',', '.', $message)));
    }

    private function detectAmount(string $message): ?float
    {
        if (! preg_match_all('/(?<!\w)(\d[\d.,]*)(?:\s*)(rb|ribu|k|jt|juta|mio|m)?(?!\w)/i', $message, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $total = 0.0;

        foreach ($matches as $match) {
            $rawNumber = $match[1];
            $suffix = strtolower($match[2] ?? '');

            $number = $suffix
                ? (float) str_replace(',', '.', $rawNumber)
                : (float) str_replace(['.', ','], '', $rawNumber);

            $multiplier = match ($suffix) {
                'rb', 'ribu', 'k' => 1000,
                'jt', 'juta', 'mio' => 1000000,
                'm' => 1000000,
                default => 1,
            };

            $total += $number * $multiplier;
        }

        return $total;
    }

    private function looksLikeFinanceQuestion(string $message): bool
    {
        if (! $this->detectAmount($message)) {
            return false;
        }

        if (str_contains($message, '?')) {
            return true;
        }

        return (bool) preg_match(
            '/\b(mahal|murah|worth|aman|gasi|ga sih|gak sih|gak|nggak|ngga|boleh|mending|sebaiknya|budget|rekomendasi|saran|cocok|kemahalan)\b/i',
            $message,
        );
    }

    private function detectType(string $message): string
    {
        $incomeKeywords = [
            'gaji',
            'salary',
            'bonus',
            'freelance',
            'invoice',
            'dibayar',
            'bayaran',
            'masuk',
            'income',
            'pendapatan',
            'transfer masuk',
        ];

        foreach ($incomeKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return 'income';
            }
        }

        return 'expense';
    }

    private function detectCategoryHint(string $message, string $type): string
    {
        if ($type === 'income') {
            return match (true) {
                str_contains($message, 'gaji') || str_contains($message, 'salary') => 'Salary',
                str_contains($message, 'freelance') || str_contains($message, 'invoice') => 'Freelance',
                str_contains($message, 'cashback') => 'Cashback',
                default => 'Other Income',
            };
        }

        return match (true) {
            str_contains($message, 'makan') || str_contains($message, 'kopi') || str_contains($message, 'coffee') || str_contains($message, 'lunch') || str_contains($message, 'dinner') || str_contains($message, 'minum') || str_contains($message, 'teh') => 'Food & Drink',
            str_contains($message, 'grab') || str_contains($message, 'gojek') || str_contains($message, 'taxi') || str_contains($message, 'bensin') || str_contains($message, 'transport') => 'Transport',
            str_contains($message, 'listrik') || str_contains($message, 'pln') || str_contains($message, 'air') || str_contains($message, 'internet') || str_contains($message, 'token') => 'Bills & Utilities',
            str_contains($message, 'obat') || str_contains($message, 'dokter') || str_contains($message, 'pharmacy') || str_contains($message, 'health') => 'Health',
            str_contains($message, 'netflix') || str_contains($message, 'spotify') || str_contains($message, 'subscription') => 'Subscription',
            str_contains($message, 'market') || str_contains($message, 'groceries') || str_contains($message, 'belanja') => 'Groceries',
            default => 'Other Expense',
        };
    }
}
