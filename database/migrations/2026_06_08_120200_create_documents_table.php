<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code')->unique();
            $table->string('title');
            $table->foreignId('document_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('document_number')->nullable();
            $table->year('year')->nullable();
            $table->date('enacted_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('issuing_institution')->nullable();
            $table->enum('document_status', ['berlaku', 'dicabut', 'diubah', 'tidak_berlaku'])->default('berlaku');
            $table->foreignId('legal_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('keywords')->nullable();
            $table->text('summary')->nullable();
            $table->text('abstract')->nullable();
            $table->text('legal_basis')->nullable();
            $table->text('related_regulation')->nullable();
            $table->enum('access_level', ['publik', 'internal', 'terbatas'])->default('publik');
            $table->string('file_path')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('downloads_count')->default(0);
            $table->timestamps();

            $table->index(['title', 'document_number', 'year']);
            $table->index(['document_status', 'access_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
