<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_staging', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->foreignId('import_job_id')->nullable()->constrained('import_jobs')->nullOnDelete();

            // Core product fields
            $table->string('product_name', 255);
            $table->string('model_number', 100)->nullable();
            $table->string('brand', 150)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('sku', 100)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            // URLs
            $table->string('source_url', 500)->nullable();
            $table->string('datasheet_url', 500)->nullable();
            $table->string('image_url', 500)->nullable();

            // Raw + structured spec data
            $table->json('raw_data_json')->nullable();
            $table->json('specifications_json')->nullable();

            // Workflow status
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['vendor_profile_id', 'status']);
            $table->index('import_job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_staging');
    }
};
