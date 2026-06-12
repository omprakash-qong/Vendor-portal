<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Product;
use App\Models\Datasheet;
use App\Models\Rfq;
use App\Models\Quotation;
use App\Models\SupportTicket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorPortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure the test vendor user exists
        $user = User::where('email', 'xyz@example.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'xyz',
                'email' => 'xyz@example.com',
                'password' => Hash::make('password'),
                'role' => 'vendor',
            ]);
        }

        // 2. Ensure the vendor user has an APPROVED vendor profile
        $vendor = VendorProfile::where('user_id', $user->id)->first();
        if (!$vendor) {
            $vendor = VendorProfile::create([
                'user_id' => $user->id,
                'legal_company_name' => 'XYZ Industrial Solutions',
                'trade_name' => 'XYZ Industry',
                'company_type' => 'Pvt Ltd',
                'company_website' => 'https://xyzindustries.example.com',
                'primary_name' => 'John Doe',
                'primary_email' => 'xyz@example.com',
                'primary_phone' => '1234567890',
                'submission_status' => 'approved',
                'submitted_at' => now(),
                'reviewed_at' => now(),
                'terms_accepted' => true,
                'data_accurate' => true,
            ]);
        } else {
            $vendor->update([
                'submission_status' => 'approved',
            ]);
        }

        // 3. Clear existing data to avoid duplicates on re-run
        Product::where('vendor_profile_id', $vendor->id)->forceDelete();
        Rfq::where('vendor_profile_id', $vendor->id)->delete();
        SupportTicket::where('vendor_profile_id', $vendor->id)->delete();

        // 4. Seed Products
        $pump = Product::create([
            'vendor_profile_id' => $vendor->id,
            'name' => 'High-Pressure Centrifugal Pump API 610',
            'category' => 'Pumps',
            'description' => 'Heavy duty centrifugal pump designed for petrochemical, oil, and gas processing applications.',
        ]);

        $compressor = Product::create([
            'vendor_profile_id' => $vendor->id,
            'name' => 'Rotary Screw Air Compressor 75kW',
            'category' => 'Compressors',
            'description' => 'Industrial rotary screw compressor delivering clean, oil-free compressed air.',
        ]);

        $valve = Product::create([
            'vendor_profile_id' => $vendor->id,
            'name' => 'Digital Control Valve ANSI 300',
            'category' => 'Valves',
            'description' => 'Precision pneumatic control valve with digital smart positioner and HART protocol.',
        ]);

        // 5. Seed Datasheets
        Datasheet::create([
            'vendor_profile_id' => $vendor->id,
            'product_id' => $pump->id,
            'name' => 'Pump Performance Curves & Specs.pdf',
            'pdf_path' => 'datasheets/pdfs/sample_pump_spec.pdf',
        ]);

        Datasheet::create([
            'vendor_profile_id' => $vendor->id,
            'product_id' => $compressor->id,
            'name' => 'Screw Compressor User Manual.pdf',
            'pdf_path' => 'datasheets/pdfs/sample_compressor_manual.pdf',
        ]);

        // 6. Seed RFQs
        $rfq1 = Rfq::create([
            'vendor_profile_id' => $vendor->id,
            'rfq_number' => 'RFQ-2026-001',
            'product_name' => '15x Heavy Duty Centrifugal Pumps',
            'description' => 'Requirements for cooling water circulation project. Material: Duplex Stainless Steel. API 610 compliant. Target delivery: 12 weeks.',
        ]);

        $rfq2 = Rfq::create([
            'vendor_profile_id' => $vendor->id,
            'rfq_number' => 'RFQ-2026-002',
            'product_name' => '2x Oil-Free Rotary Screw Air Compressors',
            'description' => 'Supply and commissioning of 2 identical packages. Flow rate: 450 cfm at 7 bar. Enclosed type with integrated dryer.',
        ]);

        $rfq3 = Rfq::create([
            'vendor_profile_id' => $vendor->id,
            'rfq_number' => 'RFQ-2026-003',
            'product_name' => '10x Control Valves (3-inch Class 300)',
            'description' => 'Pneumatic globe control valves, equal percentage characteristic, fail-safe close. Body material: Carbon steel.',
        ]);

        // 7. Seed Quotations (Pre-submit one quotation for rfq1 to test the submitted tab)
        Quotation::create([
            'rfq_id' => $rfq1->id,
            'vendor_profile_id' => $vendor->id,
            'price' => 85400.00,
            'lead_time' => '10 Weeks',
            'remarks' => 'Quoted with API 610 specifications, matching the Duplex Stainless Steel material requirement.',
            'attachment_path' => null,
        ]);

        // 8. Seed Support Tickets
        SupportTicket::create([
            'vendor_profile_id' => $vendor->id,
            'subject' => 'HART Protocol Communication Issue',
            'description' => 'Our positioner on the control valve is experiencing communication lag when connected to Honeywell DCS.',
            'status' => 'in_progress',
        ]);

        SupportTicket::create([
            'vendor_profile_id' => $vendor->id,
            'subject' => 'Portal upload timeout',
            'description' => 'Experienced a timeout error while uploading a 9MB pdf brochure file. Please verify upload limit configurations.',
            'status' => 'resolved',
        ]);
    }
}
