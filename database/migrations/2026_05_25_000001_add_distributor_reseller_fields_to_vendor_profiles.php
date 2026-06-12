<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('authorized_brands')->nullable()->after('iso_number');
            $table->string('distribution_region')->nullable()->after('authorized_brands');
            $table->string('inventory_capability')->nullable()->after('distribution_region');
            $table->string('warehouse_availability')->nullable()->after('inventory_capability');
            $table->string('dealer_certificate')->nullable()->after('warehouse_availability');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn(['authorized_brands','distribution_region','inventory_capability','warehouse_availability','dealer_certificate']);
        });
    }
};
