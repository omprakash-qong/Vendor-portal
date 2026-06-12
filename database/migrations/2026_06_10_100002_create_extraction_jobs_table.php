<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extraction_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('vendor_document_id')->constrained('vendor_documents')->onDelete('cascade');
            $table->enum('status', [
                'pending',       // queued, not started
                'processing',    // extraction + structuring running
                'preview_ready', // vendor can review
                'approved',      // vendor approved & published
                'failed',        // extraction error
                'rejected',      // vendor discarded
            ])->default('pending');
            $table->json('raw_extracted')->nullable();    // raw content after file parsing
            $table->json('ai_structured')->nullable();   // after AI normalization
            $table->text('error_message')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extraction_jobs');
    }
};
