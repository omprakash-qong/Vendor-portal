<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id');

            $table->string('legal_company_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('company_type')->nullable();

            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();

            $table->string('pan')->nullable();
            $table->string('gstin')->nullable();
            $table->string('cin')->nullable();

            $table->string('primary_name')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();

            $table->json('vendor_category')->nullable();

            $table->string('submission_status')
                  ->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};