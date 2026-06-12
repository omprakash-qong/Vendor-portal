<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('quality_certificate_number')->nullable()->after('other_standards');
            $table->string('quality_certificate_file')->nullable()->after('quality_certificate_number');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn(['quality_certificate_number', 'quality_certificate_file']);
        });
    }
};
