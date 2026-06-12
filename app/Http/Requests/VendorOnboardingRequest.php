<?php

namespace App\Http\Requests;
 
use Illuminate\Foundation\Http\FormRequest;
 
class VendorOnboardingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
 
    public function rules(): array
    {
        return [
            // Company
            'legal_company_name'  => 'required|string|max:255',
            'trade_name'          => 'nullable|string|max:255',
            'company_type'        => 'required|string|max:100',
 
            // Registered Address
            'reg_address_line1'   => 'required|string|max:255',
            'reg_address_line2'   => 'nullable|string|max:255',
            'reg_city'            => 'required|string|max:100',
            'reg_state'           => 'required|string|max:100',
            'reg_pincode'         => 'required|string|max:20',
            'reg_country'         => 'required|string|max:100',
 
            // Operating Address (optional if same_as_registered)
            'same_as_registered'  => 'nullable|boolean',
            'op_address_line1'    => 'nullable|string|max:255',
            'op_address_line2'    => 'nullable|string|max:255',
            'op_city'             => 'nullable|string|max:100',
            'op_state'            => 'nullable|string|max:100',
            'op_pincode'          => 'nullable|string|max:20',
            'op_country'          => 'nullable|string|max:100',
 
            // Online presence
            'company_website'     => 'nullable|url|max:255',
            'company_logo'        => 'nullable|file|mimes:png,jpg,jpeg,svg|max:2048',
 
            // Primary POC
            'primary_name'        => 'required|string|max:255',
            'primary_designation' => 'required|string|max:255',
            'primary_phone'       => ['required','string','max:30','regex:/^\+?[0-9]{1,4}[\s\-\.]?(\(?\d{1,4}\)?[\s\-\.]?){1,4}\d{3,10}$/'],
            'primary_email'       => [
                'required', 'email',
                function ($attr, $val, $fail) {
                    $blocked = ['gmail.com','yahoo.com','hotmail.com','outlook.com','rediffmail.com'];
                    $domain  = strtolower(substr(strrchr($val, '@'), 1));
                    if (in_array($domain, $blocked)) {
                        $fail('Please use an official company email address.');
                    }
                },
            ],
 
            // Secondary POC array
            'secondary'               => 'nullable|array|max:4',
            'secondary.*.name'        => 'nullable|string|max:255',
            'secondary.*.designation' => 'nullable|string|max:255',
            'secondary.*.phone'       => ['nullable','string','max:30','regex:/^\+?[0-9]{1,4}[\s\-\.]?(\(?\d{1,4}\)?[\s\-\.]?){1,4}\d{3,10}$/'],
            'secondary.*.email'       => 'nullable|email|max:255',
 
            // Tax
            'gstin'               => ['required','string','min:5','max:20','regex:/^[A-Z0-9\/\-]{5,20}$/i'],
            'incorporation_number'=> ['nullable','string','max:50','regex:/^[A-Z0-9\/\-\.]{3,50}$/i'],
            'msme'                => 'nullable|string|max:50',
            'tax_id_intl'         => 'nullable|string|max:100',
            'msme_certificate'    => 'nullable|file|mimes:pdf,jpg,png|max:5120',
 
            // Vendor category & capabilities
            'vendor_category'         => 'required|array|min:1',
            'vendor_category.*'       => 'string',
            'vendor_category_other'   => 'nullable|string|max:255',
            'authorization_letter'    => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'industry_focus'          => 'required|array|min:1',
            'industry_focus.*'        => 'string',
            'industry_focus_other'    => 'nullable|string|max:255',
 
            // Sub-domains
            'subdomain_pumps.*'       => 'nullable|string',
            'subdomain_compressors.*' => 'nullable|string',
            'subdomain_instruments.*' => 'nullable|string',
            'subdomain_valves.*'      => 'nullable|string',
            'subdomain_turbines.*'    => 'nullable|string',
            'subdomain_motors.*'      => 'nullable|string',
 
            // Vendor details (all categories)
            'authorized_brands'       => 'nullable|string|max:500',
            'distribution_region'     => 'nullable|string|max:255',
            'inventory_capability'    => 'nullable|in:yes,no',
            'warehouse_availability'  => 'nullable|in:yes,no',

            // Quality
            'iso_certified'       => 'nullable|in:yes,no',
            'iso_number'          => ['nullable','string','max:50','regex:/^[A-Z0-9\s\/\:\-\.]{3,50}$/i'],
            'iso_certificate'     => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'industry_standards'  => 'nullable|array',
            'industry_standards.*'=> 'string',
            'other_standards'     => 'nullable|string|max:1000',
            'quality_certificate_number' => 'nullable|string|max:100',
            'quality_certificate_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
 
            // Documents
            'company_brochure'    => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'incorporation_cert'  => 'required|file|mimes:pdf,jpg,png|max:5120',
            'additional_certs'    => 'nullable|array',
            'additional_certs.*'  => 'file|mimes:pdf,jpg,png|max:10240',
 
            // Terms
            'terms_accepted'      => 'required|accepted',
            'data_accurate'       => 'required|accepted',
        ];
    }
 
    public function messages(): array
    {
        return [
            'legal_company_name.required'  => 'Legal company name is required.',
            'company_type.required'        => 'Please select your company type.',
            'reg_address_line1.required'   => 'Registered address is required.',
            'gstin.required'               => 'VAT / GST / Tax ID is required.',
            'gstin.min'                    => 'Tax ID must be at least 5 characters.',
            'gstin.max'                    => 'Tax ID must not exceed 20 characters.',
            'gstin.regex'                  => 'Enter a valid tax number (5–20 alphanumeric characters, e.g. 22AAAAA0000A1Z5).',
            'incorporation_number.regex'   => 'Incorporation number must contain only letters, numbers, hyphens, or slashes.',
            'primary_phone.required'       => 'Phone number is required.',
            'primary_phone.regex'          => 'Enter a valid phone number (e.g. +91 98765 43210 or +1 212 555 0100).',
            'secondary.*.phone.regex'      => 'Enter a valid phone number for secondary contact.',
            'iso_number.regex'             => 'Enter a valid ISO number (e.g. ISO 9001:2015).',
            'vendor_category.required'     => 'Please select at least one vendor category.',
            'industry_focus.required'      => 'Please select at least one industry focus.',
            'incorporation_cert.required'  => 'Certificate of Incorporation is required.',
            'terms_accepted.accepted'      => 'You must accept the Terms & Conditions.',
            'data_accurate.accepted'       => 'You must certify the accuracy of your data.',
        ];
    }
}