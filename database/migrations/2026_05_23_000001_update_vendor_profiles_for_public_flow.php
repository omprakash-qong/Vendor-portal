<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make user_id nullable — vendors submit before having a login account
        // Add unique on primary_email — prevent duplicate applications
        // Add admin_notes — rejection feedback
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unique('primary_email');
            $table->text('admin_notes')->nullable();
        });

        // Safely add all missing columns that the model expects
        $additions = [
            'reg_address_line1'     => fn(Blueprint $t) => $t->string('reg_address_line1')->nullable(),
            'reg_address_line2'     => fn(Blueprint $t) => $t->string('reg_address_line2')->nullable(),
            'reg_city'              => fn(Blueprint $t) => $t->string('reg_city')->nullable(),
            'reg_state'             => fn(Blueprint $t) => $t->string('reg_state')->nullable(),
            'reg_pincode'           => fn(Blueprint $t) => $t->string('reg_pincode')->nullable(),
            'reg_country'           => fn(Blueprint $t) => $t->string('reg_country')->nullable(),
            'same_as_registered'    => fn(Blueprint $t) => $t->boolean('same_as_registered')->default(false),
            'op_address_line1'      => fn(Blueprint $t) => $t->string('op_address_line1')->nullable(),
            'op_address_line2'      => fn(Blueprint $t) => $t->string('op_address_line2')->nullable(),
            'op_city'               => fn(Blueprint $t) => $t->string('op_city')->nullable(),
            'op_state'              => fn(Blueprint $t) => $t->string('op_state')->nullable(),
            'op_pincode'            => fn(Blueprint $t) => $t->string('op_pincode')->nullable(),
            'op_country'            => fn(Blueprint $t) => $t->string('op_country')->nullable(),
            'primary_designation'   => fn(Blueprint $t) => $t->string('primary_designation')->nullable(),
            'secondary'             => fn(Blueprint $t) => $t->json('secondary')->nullable(),
            'msme'                  => fn(Blueprint $t) => $t->string('msme')->nullable(),
            'tax_id_intl'           => fn(Blueprint $t) => $t->string('tax_id_intl')->nullable(),
            'msme_certificate'      => fn(Blueprint $t) => $t->string('msme_certificate')->nullable(),
            'industry_focus'        => fn(Blueprint $t) => $t->json('industry_focus')->nullable(),
            'authorization_letter'  => fn(Blueprint $t) => $t->string('authorization_letter')->nullable(),
            'subdomain_pumps'       => fn(Blueprint $t) => $t->json('subdomain_pumps')->nullable(),
            'subdomain_compressors' => fn(Blueprint $t) => $t->json('subdomain_compressors')->nullable(),
            'subdomain_instruments' => fn(Blueprint $t) => $t->json('subdomain_instruments')->nullable(),
            'subdomain_valves'      => fn(Blueprint $t) => $t->json('subdomain_valves')->nullable(),
            'subdomain_turbines'    => fn(Blueprint $t) => $t->json('subdomain_turbines')->nullable(),
            'subdomain_motors'      => fn(Blueprint $t) => $t->json('subdomain_motors')->nullable(),
            'iso_certified'         => fn(Blueprint $t) => $t->string('iso_certified')->nullable(),
            'iso_number'            => fn(Blueprint $t) => $t->string('iso_number')->nullable(),
            'iso_certificate'       => fn(Blueprint $t) => $t->string('iso_certificate')->nullable(),
            'industry_standards'    => fn(Blueprint $t) => $t->json('industry_standards')->nullable(),
            'company_brochure'      => fn(Blueprint $t) => $t->string('company_brochure')->nullable(),
            'incorporation_cert'    => fn(Blueprint $t) => $t->string('incorporation_cert')->nullable(),
            'bank_details'          => fn(Blueprint $t) => $t->string('bank_details')->nullable(),
            'additional_certs'      => fn(Blueprint $t) => $t->json('additional_certs')->nullable(),
            'terms_accepted'        => fn(Blueprint $t) => $t->boolean('terms_accepted')->default(false),
            'data_accurate'         => fn(Blueprint $t) => $t->boolean('data_accurate')->default(false),
            'submitted_at'          => fn(Blueprint $t) => $t->timestamp('submitted_at')->nullable(),
            'reviewed_at'           => fn(Blueprint $t) => $t->timestamp('reviewed_at')->nullable(),
            'reviewer_notes'        => fn(Blueprint $t) => $t->text('reviewer_notes')->nullable(),
        ];

        $missing = array_filter($additions, fn($col) => !Schema::hasColumn('vendor_profiles', $col), ARRAY_FILTER_USE_KEY);

        if (!empty($missing)) {
            Schema::table('vendor_profiles', function (Blueprint $table) use ($missing) {
                foreach ($missing as $definition) {
                    $definition($table);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropUnique(['primary_email']);
            $table->dropColumn('admin_notes');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
