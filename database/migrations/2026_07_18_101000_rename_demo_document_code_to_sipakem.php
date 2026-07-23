<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')
            ->where('document_code', 'PUSAKA-0001')
            ->update([
                'document_code' => 'SIPAKEM-0001',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')
            ->where('document_code', 'SIPAKEM-0001')
            ->update([
                'document_code' => 'PUSAKA-0001',
                'updated_at' => now(),
            ]);
    }
};
