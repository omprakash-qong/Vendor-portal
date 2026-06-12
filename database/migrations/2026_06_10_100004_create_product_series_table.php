<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('extraction_job_id')->nullable()->constrained('extraction_jobs')->onDelete('set null');
            $table->string('name');                          // e.g. "IE4 Premium Motors"
            $table->string('brand')->nullable();             // e.g. "LHP", "CompAir"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vendor_profile_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_series');
    }
};
