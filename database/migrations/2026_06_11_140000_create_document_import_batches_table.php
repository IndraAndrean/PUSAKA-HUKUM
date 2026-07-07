<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('spreadsheet_name');
            $table->string('pdf_archive_name');
            $table->unsignedSmallInteger('total_rows')->default(0);
            $table->unsignedSmallInteger('successful_rows')->default(0);
            $table->unsignedSmallInteger('failed_rows')->default(0);
            $table->enum('status', ['completed', 'completed_with_errors', 'failed']);
            $table->json('results')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_import_batches');
    }
};
