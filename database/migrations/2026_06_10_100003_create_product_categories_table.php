<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->tinyInteger('level')->default(1); // 1=root, 2=sub, 3=leaf
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('equipment_type', 50)->nullable(); // motor, valve, pump, compressor, blower...
            $table->json('spec_template')->nullable();   // dynamic form fields for vendor upload UI
            $table->json('match_fields')->nullable();    // which fields QS uses for matching
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('set null');
            $table->index('parent_id');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
