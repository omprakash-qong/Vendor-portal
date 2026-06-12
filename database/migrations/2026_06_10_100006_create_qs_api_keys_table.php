<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qs_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // e.g. "Qong Studio Production"
            $table->string('key_hash', 64)->unique();  // SHA-256 of the raw key
            $table->string('key_prefix', 8);           // first 8 chars for display
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qs_api_keys');
    }
};
