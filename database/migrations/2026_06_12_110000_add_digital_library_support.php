<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('collection', 30)->default('produk_hukum')->after('review_interval_months');
            $table->index('collection');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('author')->nullable()->after('title');
            $table->string('publisher')->nullable()->after('issuing_institution');
            $table->string('isbn_issn', 50)->nullable()->after('publisher');
            $table->string('edition_volume', 100)->nullable()->after('isbn_issn');
            $table->index('isbn_issn');
        });

        DB::table('document_types')
            ->whereIn('name', ['Kajian Hukum', 'Legal Opinion'])
            ->update(['collection' => 'perpustakaan']);

        DB::table('document_types')
            ->where('name', 'Materi Penyuluhan')
            ->update(['collection' => 'edukasi']);

        $now = now();
        $types = [
            ['name' => 'Buku Hukum', 'code_prefix' => 'LIB-BUKU', 'review_interval_months' => 12],
            ['name' => 'Jurnal Hukum', 'code_prefix' => 'LIB-JURNAL', 'review_interval_months' => 12],
            ['name' => 'Naskah Akademik', 'code_prefix' => 'LIB-NA', 'review_interval_months' => 12],
            ['name' => 'Yurisprudensi', 'code_prefix' => 'LIB-YURIS', 'review_interval_months' => 6],
            ['name' => 'Putusan Praperadilan', 'code_prefix' => 'PRAP', 'review_interval_months' => 6],
            ['name' => 'Telaahan Hukum', 'code_prefix' => 'TEL', 'review_interval_months' => 6],
            ['name' => 'Best Practice Hukum', 'code_prefix' => 'LIB-BP', 'review_interval_months' => 12],
        ];

        foreach ($types as $type) {
            DB::table('document_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'slug' => Str::slug($type['name']),
                    'code_prefix' => $type['code_prefix'],
                    'review_interval_months' => $type['review_interval_months'],
                    'collection' => 'perpustakaan',
                    'description' => 'Koleksi Perpustakaan Digital Hukum SIPAKEM.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('document_types')
            ->whereIn('code_prefix', ['LIB-BUKU', 'LIB-JURNAL', 'LIB-NA', 'LIB-YURIS', 'PRAP', 'TEL', 'LIB-BP'])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('documents')
                    ->whereColumn('documents.document_type_id', 'document_types.id');
            })
            ->delete();

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['isbn_issn']);
            $table->dropColumn(['author', 'publisher', 'isbn_issn', 'edition_volume']);
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropIndex(['collection']);
            $table->dropColumn('collection');
        });
    }
};
