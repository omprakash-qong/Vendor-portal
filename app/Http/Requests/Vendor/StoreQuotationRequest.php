<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'vendor';
    }

    public function rules(): array
    {
        return [
            'price' => 'required|numeric|min:0',
            'lead_time' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }
}
