<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qs_match_requests', function (Blueprint $table) {
            $table->id();
            $table->string('qs_job_id')->unique();        // UUID sent by QS
            $table->string('qs_project_name')->nullable();
            $table->string('qs_user_email')->nullable();
            $table->json('instruments');                   // raw instrument list from QS
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('qs_job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qs_match_requests');
    }
};
