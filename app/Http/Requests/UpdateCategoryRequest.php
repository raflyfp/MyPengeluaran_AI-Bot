<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $category = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                Rule::unique('categories')
                    ->where('type', $this->input('type', $category?->type))
                    ->whereNull('deleted_at')
                    ->ignore($category?->id),
            ],
            'icon' => ['nullable', 'string', 'max:80'],
            'type' => ['sometimes', 'required', Rule::in(['income', 'expense'])],
        ];
    }
}
