<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorPortalSeeder extends Seeder
{
    /**
     * Seed a demo vendor (approved) with a few products — matches the live
     * UI flow: a vendor owns products, nothing else.
     */
    public function run(): void
    {
        // 1. Demo vendor user
        $user = User::firstOrCreate(
            ['email' => 'xyz@example.com'],
            ['name' => 'xyz', 'password' => Hash::make('password'), 'role' => 'vendor'],
        );

        // 2. Approved vendor profile
        $vendor = VendorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'legal_company_name' => 'XYZ Industrial Solutions',
                'trade_name'         => 'XYZ Industry',
                'company_type'       => 'Pvt Ltd',
                'company_website'    => 'https://xyzindustries.example.com',
                'primary_name'       => 'John Doe',
                'primary_email'      => 'xyz@example.com',
                'primary_phone'      => '1234567890',
                'submission_status'  => 'approved',
                'submitted_at'       => now(),
                'reviewed_at'        => now(),
                'terms_accepted'     => true,
                'data_accurate'      => true,
            ],
        );
        $vendor->update(['submission_status' => 'approved']);

        // 3. Fresh demo products
        Product::where('vendor_profile_id', $vendor->id)->forceDelete();

        Product::create([
            'vendor_profile_id' => $vendor->id,
            'name'              => 'High-Pressure Centrifugal Pump API 610',
            'category'          => 'Pumps',
            'description'       => 'Heavy duty centrifugal pump designed for petrochemical, oil, and gas processing applications.',
        ]);

        Product::create([
            'vendor_profile_id' => $vendor->id,
            'name'              => 'Rotary Screw Air Compressor 75kW',
            'category'          => 'Compressors',
            'description'       => 'Industrial rotary screw compressor delivering clean, oil-free compressed air.',
        ]);

        Product::create([
            'vendor_profile_id' => $vendor->id,
            'name'              => 'Digital Control Valve ANSI 300',
            'category'          => 'Valves',
            'description'       => 'Precision pneumatic control valve with digital smart positioner and HART protocol.',
        ]);
    }
}
