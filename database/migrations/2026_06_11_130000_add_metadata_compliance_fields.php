<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('code_prefix', 30)->nullable()->unique()->after('slug');
            $table->unsignedTinyInteger('review_interval_months')->default(6)->after('code_prefix');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('bidang_subbidang', ['kum', 'bankum', 'sunluhkum'])->nullable()->after('legal_category_id');
            $table->string('document_version', 30)->default('1.0')->after('related_regulation');
            $table->date('last_reviewed_at')->nullable()->after('document_version');
            $table->date('next_review_at')->nullable()->after('last_reviewed_at');
            $table->index('next_review_at');
        });

        $typeSettings = [
            'undang-undang' => ['REG-UU', 3],
            'peraturan-pemerintah' => ['REG-PP', 3],
            'peraturan-presiden' => ['REG-PERPRES', 3],
            'peraturan-kapolri' => ['POL-PERKAP', 3],
            'surat-edaran' => ['SE', 3],
            'petunjuk-pelaksanaan' => ['JUKLAK', 3],
            'petunjuk-teknis' => ['JUKNIS', 3],
            'kajian-hukum' => ['KAJ', 6],
            'legal-opinion' => ['LO', 0],
            'materi-penyuluhan' => ['SUNLUH', 6],
        ];

        foreach ($typeSettings as $slug => [$prefix, $months]) {
            DB::table('document_types')
                ->where('slug', $slug)
                ->update([
                    'code_prefix' => $prefix,
                    'review_interval_months' => $months,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['next_review_at']);
            $table->dropColumn([
                'bidang_subbidang',
                'document_version',
                'last_reviewed_at',
                'next_review_at',
            ]);
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropUnique(['code_prefix']);
            $table->dropColumn(['code_prefix', 'review_interval_months']);
        });
    }
};
