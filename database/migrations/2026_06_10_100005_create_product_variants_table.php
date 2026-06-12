<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('series_id')->constrained('product_series')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('extraction_job_id')->nullable()->constrained('extraction_jobs')->onDelete('set null');

            // Identification
            $table->string('variant_name');          // e.g. "7.5kW 4P 132M"
            $table->string('equipment_type', 80);    // Motor / Gate Valve / Centrifugal Pump ...

            // ─── Matching Fields (indexed) ──────────────────────────────
            $table->decimal('power_kw', 10, 2)->nullable();
            $table->decimal('size_inch', 8, 2)->nullable();
            $table->decimal('size_mm', 10, 2)->nullable();
            $table->decimal('pressure_bar', 8, 2)->nullable();
            $table->decimal('flow_m3h', 12, 2)->nullable();
            $table->smallInteger('voltage_v')->nullable();
            $table->tinyInteger('poles')->nullable();

            // ─── Tag Fields ─────────────────────────────────────────────
            $table->json('industry_tags')->nullable();    // ["oil_gas","pharma","food"]
            $table->json('capability_tags')->nullable();  // ["ATEX","flame_proof","IE4"]
            $table->json('certifications')->nullable();   // ["IECEx","CE","BIS"]

            // ─── Flexible Specifications (display only) ─────────────────
            $table->json('specifications')->nullable();

            // ─── Assets ─────────────────────────────────────────────────
            $table->string('datasheet_url', 500)->nullable();

            // ─── Status ─────────────────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for fast matching queries
            $table->index(['is_published', 'is_active', 'category_id']);
            $table->index('power_kw');
            $table->index('size_inch');
            $table->index('size_mm');
            $table->index('pressure_bar');
            $table->index('flow_m3h');
            $table->fullText(['variant_name', 'equipment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
