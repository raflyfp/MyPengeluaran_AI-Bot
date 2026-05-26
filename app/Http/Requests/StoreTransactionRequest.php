<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', Rule::in(['manual', 'telegram', 'whatsapp', 'import', 'system'])],
            'transaction_date' => ['required', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $category = Category::query()->find($this->integer('category_id'));

            if ($category && $category->type !== $this->input('type')) {
                $validator->errors()->add('category_id', 'The category type must match the transaction type.');
            }
        });
    }
}
