<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qs_match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('qs_match_requests')->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('instrument_tag');              // e.g. "M-101"
            $table->string('instrument_type');             // e.g. "Motor"
            $table->decimal('match_score', 5, 4);         // 0.0000–1.0000
            $table->json('score_breakdown');              // {power: 0.35, size: 0.30, spec: 0.25, tag: 0.10}
            $table->smallInteger('rank');
            $table->timestamps();

            $table->index(['request_id', 'instrument_tag', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qs_match_results');
    }
};
