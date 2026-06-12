<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->enum('source_type', ['website', 'pdf', 'excel', 'csv']);

            // Website source
            $table->string('website_url', 500)->nullable();

            // File source
            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 500)->nullable();

            // Progress
            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->integer('pages_crawled')->default(0);
            $table->integer('products_found')->default(0);
            $table->integer('failed_pages')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
