<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $replacementId = DB::table('document_types')->where('name', 'Kajian Hukum')->value('id');

        if (! $replacementId) {
            $replacementId = DB::table('document_types')->insertGetId([
                'name' => 'Kajian Hukum',
                'slug' => 'kajian-hukum',
                'code_prefix' => 'KAJ',
                'review_interval_months' => 6,
                'collection' => 'perpustakaan',
                'description' => 'Koleksi kajian dan referensi hukum SIPAKEM.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $removedTypeIds = DB::table('document_types')
            ->whereIn('name', $this->removedTypes())
            ->pluck('id');

        if ($removedTypeIds->isNotEmpty()) {
            DB::table('documents')
                ->whereIn('document_type_id', $removedTypeIds)
                ->update([
                    'document_type_id' => $replacementId,
                    'updated_at' => $now,
                ]);

            DB::table('document_types')
                ->whereIn('id', $removedTypeIds)
                ->delete();
        }
    }

    public function down(): void
    {
        $now = now();
        $types = [
            ['name' => 'Legal Opinion', 'code_prefix' => 'LO', 'review_interval_months' => 0],
            ['name' => 'Naskah Akademik', 'code_prefix' => 'LIB-NA', 'review_interval_months' => 12],
            ['name' => 'Yurisprudensi', 'code_prefix' => 'LIB-YURIS', 'review_interval_months' => 6],
            ['name' => 'Telaahan Hukum', 'code_prefix' => 'TEL', 'review_interval_months' => 6],
        ];

        foreach ($types as $type) {
            DB::table('document_types')->updateOrInsert(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
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

    private function removedTypes(): array
    {
        return [
            'Legal Opinion',
            'Naskah Akademik',
            'Yurisprudensi',
            'Telaahan Hukum',
        ];
    }
};
