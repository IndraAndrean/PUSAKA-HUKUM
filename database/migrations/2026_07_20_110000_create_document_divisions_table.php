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
        Schema::create('document_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = collect([
            [
                'name' => 'Kum',
                'slug' => 'kum',
                'code' => 'kum',
                'description' => 'Bidang/Subbidang hukum umum.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bankum',
                'slug' => 'bankum',
                'code' => 'bankum',
                'description' => 'Bidang/Subbidang bantuan hukum.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sunluhkum',
                'slug' => 'sunluhkum',
                'code' => 'sunluhkum',
                'description' => 'Bidang/Subbidang penyuluhan hukum.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $existingDocumentValues = DB::table('documents')
            ->whereNotNull('bidang_subbidang')
            ->where('bidang_subbidang', '!=', '')
            ->distinct()
            ->pluck('bidang_subbidang')
            ->map(fn ($code) => (string) $code)
            ->reject(fn ($code) => $defaults->contains('code', $code))
            ->map(fn ($code) => [
                'name' => Str::headline(str_replace(['_', '-'], ' ', $code)),
                'slug' => Str::slug($code) ?: $code,
                'code' => $code,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('document_divisions')->insert($defaults->merge($existingDocumentValues)->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('document_divisions');
    }
};
