<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            [
                'name' => 'Peraturan Kapolda',
                'code_prefix' => 'PERKAPOLDA',
                'review_interval_months' => 3,
                'collection' => 'produk_hukum',
                'description' => 'Produk hukum berupa peraturan yang ditetapkan oleh Kapolda.',
            ],
            [
                'name' => 'Keputusan Kabidkum',
                'code_prefix' => 'KEP-KABIDKUM',
                'review_interval_months' => 3,
                'collection' => 'produk_hukum',
                'description' => 'Produk hukum berupa keputusan yang ditetapkan oleh Kabidkum.',
            ],
        ];

        foreach ($types as $type) {
            $slug = Str::slug($type['name']);
            $existing = DB::table('document_types')->where('slug', $slug)->exists();

            if ($existing) {
                DB::table('document_types')
                    ->where('slug', $slug)
                    ->update([
                        ...$type,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('document_types')->insert([
                'slug' => $slug,
                ...$type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('document_types')
            ->whereIn('slug', ['peraturan-kapolda', 'keputusan-kabidkum'])
            ->delete();
    }
};
