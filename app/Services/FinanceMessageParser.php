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
        ];
    }

    private function normalize(string $message): string
    {
        return strtolower(trim(str_replace(',', '.', $message)));
    }

    private function detectAmount(string $message): ?float
    {
        if (! preg_match('/(?<!\w)(\d[\d.,]*)(?:\s*)(rb|ribu|k|jt|juta|mio|m)?(?!\w)/i', $message, $matches)) {
            return null;
        }

        $rawNumber = $matches[1];
        $suffix = strtolower($matches[2] ?? '');

        $number = $suffix
            ? (float) str_replace(',', '.', $rawNumber)
            : (float) str_replace(['.', ','], '', $rawNumber);

        $multiplier = match ($suffix) {
            'rb', 'ribu', 'k' => 1000,
            'jt', 'juta', 'mio' => 1000000,
            'm' => 1000000,
            default => 1,
        };

        return $number * $multiplier;
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
            str_contains($message, 'makan') || str_contains($message, 'kopi') || str_contains($message, 'coffee') || str_contains($message, 'lunch') || str_contains($message, 'dinner') => 'Food & Dining',
            str_contains($message, 'grab') || str_contains($message, 'gojek') || str_contains($message, 'taxi') || str_contains($message, 'bensin') || str_contains($message, 'transport') => 'Transport',
            str_contains($message, 'listrik') || str_contains($message, 'pln') || str_contains($message, 'air') || str_contains($message, 'internet') || str_contains($message, 'token') => 'Bills & Utilities',
            str_contains($message, 'obat') || str_contains($message, 'dokter') || str_contains($message, 'pharmacy') || str_contains($message, 'health') => 'Health',
            str_contains($message, 'netflix') || str_contains($message, 'spotify') || str_contains($message, 'subscription') => 'Subscription',
            str_contains($message, 'market') || str_contains($message, 'groceries') || str_contains($message, 'belanja') => 'Groceries',
            default => 'Other Expense',
        };
    }
}
