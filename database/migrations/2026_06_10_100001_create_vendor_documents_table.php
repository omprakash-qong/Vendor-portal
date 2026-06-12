<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->enum('file_type', ['excel', 'csv', 'pdf']);
            $table->timestamp('upload_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
