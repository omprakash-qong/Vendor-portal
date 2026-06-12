<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rebuild valve_specifications to match the standardized valve field set
 * (Emerson control-valve specification layout). The table is empty, so a
 * clean recreate is safe and keeps the columns aligned with
 * config/category_fields.php → 'Valves'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('valve_specifications');

        Schema::create('valve_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('certifications', 500)->nullable();
            $table->string('critical_service', 500)->nullable();
            $table->string('flow_characteristics', 500)->nullable();
            $table->string('material', 500)->nullable();
            $table->string('operating_temperature', 500)->nullable();
            $table->string('pressure_class', 500)->nullable();
            $table->string('process_connection_type', 500)->nullable();
            $table->string('shutoff_class', 500)->nullable();
            $table->string('valve_size', 500)->nullable();
            $table->string('valve_size_standard', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valve_specifications');

        Schema::create('valve_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('valve_type', 500)->nullable();
            $table->string('size_mm', 500)->nullable();
            $table->string('pressure_class', 500)->nullable();
            $table->string('body_material', 500)->nullable();
            $table->string('disc_material', 500)->nullable();
            $table->string('seat_material', 500)->nullable();
            $table->string('stem_material', 500)->nullable();
            $table->string('connection_type', 500)->nullable();
            $table->string('end_connection', 500)->nullable();
            $table->string('actuation_type', 500)->nullable();
            $table->string('temperature_range', 500)->nullable();
            $table->string('leakage_class', 500)->nullable();
            $table->string('cv_value', 500)->nullable();
            $table->timestamps();
        });
    }
};
