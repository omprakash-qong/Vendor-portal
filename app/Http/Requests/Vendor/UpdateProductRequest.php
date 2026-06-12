<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'vendor';
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'category'     => ['required', 'string', Rule::in(array_keys((array) config('category_fields', [])))],
            'model_number' => 'nullable|string|max:100',
            'brand'        => 'nullable|string|max:150',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'Please choose a product type from the list.',
        ];
    }
}
