<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->string('category_slug', 100); // e.g. motors, valves — matches products.category lowercased

            $table->string('field_name', 100);   // e.g. power_kw
            $table->string('field_label', 150);  // e.g. Power (kW)
            $table->enum('field_type', ['number', 'text', 'select', 'range'])->default('number');
            $table->string('unit', 20)->nullable();        // kW, bar, mm
            $table->json('options_json')->nullable();       // for select type

            $table->boolean('is_required')->default(false);
            $table->boolean('is_matching_field')->default(true);  // used in QS scoring
            $table->boolean('is_filter')->default(true);           // show in product filter sidebar

            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_slug', 'is_filter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_fields');
    }
};
