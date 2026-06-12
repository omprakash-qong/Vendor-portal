<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDatasheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'vendor';
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'pdf' => 'required|file|mimes:pdf|max:10240',
        ];
    }
}
