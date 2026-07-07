<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename')->unique();
            $table->string('disk_path')->unique();
            $table->enum('type', ['manual', 'scheduled', 'pre_restore'])->default('manual');
            $table->enum('status', ['creating', 'completed', 'failed', 'restored'])->default('creating');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedBigInteger('database_size_bytes')->default(0);
            $table->unsignedInteger('documents_count')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
