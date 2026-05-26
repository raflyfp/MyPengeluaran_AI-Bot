<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:80', Rule::unique('categories')->where('type', $this->input('type'))->whereNull('deleted_at')],
            'icon' => ['nullable', 'string', 'max:80'],
            'type' => ['required', Rule::in(['income', 'expense'])],
        ];
    }
}
