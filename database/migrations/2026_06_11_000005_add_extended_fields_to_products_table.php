<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model_number', 100)->nullable()->after('name');
            $table->string('brand', 150)->nullable()->after('model_number');
            $table->text('short_description')->nullable()->after('description');
            $table->longText('long_description')->nullable()->after('short_description');
            $table->string('datasheet_url', 500)->nullable()->after('image_path');
            $table->string('catalogue_url', 500)->nullable()->after('datasheet_url');
            $table->string('import_source', 20)->nullable()->after('catalogue_url'); // website/pdf/excel/csv/manual
            $table->string('status', 20)->default('published')->after('import_source');

            $table->index(['vendor_profile_id', 'status']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['model_number', 'brand', 'short_description', 'long_description',
                                'datasheet_url', 'catalogue_url', 'import_source', 'status']);
        });
    }
};
