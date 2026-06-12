<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'vendor';
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }
}
