<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->route('transaction')?->user_id === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'type' => ['sometimes', 'required', Rule::in(['income', 'expense'])],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
            'source' => ['sometimes', 'nullable', Rule::in(['manual', 'telegram', 'whatsapp', 'import', 'system'])],
            'transaction_date' => ['sometimes', 'required', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $transaction = $this->route('transaction');
            $categoryId = $this->integer('category_id') ?: $transaction?->category_id;
            $type = $this->input('type', $transaction?->type);
            $category = $categoryId ? Category::query()->find($categoryId) : null;

            if ($category && $type && $category->type !== $type) {
                $validator->errors()->add('category_id', 'The category type must match the transaction type.');
            }
        });
    }
}
