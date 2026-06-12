<?php

namespace Database\Factories;

use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorProfileFactory extends Factory
{
    protected $model = VendorProfile::class;

    public function definition(): array
    {
        return [
            'legal_company_name'  => fake()->company(),
            'trade_name'          => fake()->company(),
            'company_type'        => 'Private Limited',
            'company_website'     => fake()->url(),
            'reg_address_line1'   => fake()->streetAddress(),
            'reg_city'            => fake()->city(),
            'reg_state'           => 'Maharashtra',
            'reg_pincode'         => '400001',
            'reg_country'         => 'India',
            'primary_name'        => fake()->name(),
            'primary_designation' => 'Director',
            'primary_phone'       => '+91 9876543210',
            'primary_email'       => fake()->unique()->companyEmail(),
            'gstin'               => 'GST12345678901',
            'incorporation_number'=> 'U17110MH2024',
            'vendor_category'     => ['Manufacturer'],
            'industry_focus'      => ['Oil & Gas'],
            'submission_status'   => 'pending_review',
        ];
    }
}
