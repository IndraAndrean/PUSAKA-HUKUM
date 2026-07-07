<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_download_logs');
    }
};
