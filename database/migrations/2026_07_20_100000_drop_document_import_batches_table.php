<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('document_import_batches');
    }

    public function down(): void
    {
        // Import massal sudah tidak didukung. Tabel riwayat tidak dibuat ulang.
    }
};
