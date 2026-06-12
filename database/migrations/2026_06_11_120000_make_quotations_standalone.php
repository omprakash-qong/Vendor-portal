<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Vendor-created quotations are standalone (no incoming RFQ needed).
            $table->foreignId('rfq_id')->nullable()->change();
            $table->decimal('price', 12, 2)->nullable()->change();
            $table->string('lead_time')->nullable()->change();

            $table->string('customer_name')->nullable()->after('vendor_profile_id');
            $table->string('subject')->nullable()->after('customer_name');
            $table->string('original_filename')->nullable()->after('attachment_path');
            $table->string('status')->default('draft')->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'subject', 'original_filename', 'status']);
            $table->foreignId('rfq_id')->nullable(false)->change();
            $table->decimal('price', 12, 2)->nullable(false)->change();
            $table->string('lead_time')->nullable(false)->change();
        });
    }
};
