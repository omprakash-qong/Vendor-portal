<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_job_id')->constrained('import_jobs')->cascadeOnDelete();
            $table->enum('level', ['info', 'warning', 'error'])->default('info');
            $table->string('url', 500)->nullable();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_job_logs');
    }
};
