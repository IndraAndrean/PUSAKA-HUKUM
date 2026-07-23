<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\DocumentDownloadLog;
use App\Models\KpiTarget;
use App\Models\SatisfactionSurvey;
use App\Models\User;
use Illuminate\Support\Collection;

class KpiMetricsService
{
    public function build(KpiTarget $target): array
    {
        $activeUsers = User::where('is_active', true)->count();
        $activeUserIds = DocumentAccessLog::whereNotNull('user_id')
            ->where('accessed_at', '>=', now()->subDays(30))
            ->pluck('user_id')
            ->merge(
                DocumentDownloadLog::whereNotNull('user_id')
                    ->where('downloaded_at', '>=', now()->subDays(30))
                    ->pluck('user_id')
            )
            ->unique()
            ->count();

        $satisfaction = (float) (SatisfactionSurvey::query()
            ->selectRaw('AVG((accessibility_rating + speed_rating + content_rating + ease_rating + overall_rating) * 4) AS score')
            ->value('score') ?? 0);
        $averageSearchSeconds = (float) (SatisfactionSurvey::whereNotNull('search_duration_seconds')
            ->avg('search_duration_seconds') ?? 0);
        $utilization = $activeUsers > 0 ? ($activeUserIds / $activeUsers) * 100 : 0;

        $actuals = [
            'documents' => Document::count(),
            'legislation' => $this->countTypes(['Undang-Undang', 'Peraturan Pemerintah', 'Peraturan Presiden']),
            'internal_documents' => $this->countTypes(['Peraturan Kapolri', 'Peraturan Kapolda', 'Keputusan Kabidkum', 'Surat Edaran', 'Petunjuk Pelaksanaan', 'Petunjuk Teknis']),
            'legal_studies' => $this->countTypes(['Kajian Hukum']),
            'education_materials' => $this->countTypes(['Materi Penyuluhan']),
            'registered_users' => $activeUsers,
            'accesses' => DocumentAccessLog::count(),
            'satisfaction' => round($satisfaction, 1),
            'utilization' => round($utilization, 1),
            'search_time' => (int) round($averageSearchSeconds),
            'satker_coverage' => $target->satker_coverage_percent,
            'polres_coverage' => $target->polres_coverage_percent,
            'appointed_admins' => User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->count(),
            'sop_available' => $target->sop_available,
            'user_guide_available' => $target->user_guide_available,
            'monthly_updates' => Document::where('updated_at', '>=', now()->subMonth())->count(),
            'survey_responses' => SatisfactionSurvey::count(),
        ];

        return [
            'actuals' => $actuals,
            'indicators' => $this->indicators($target, $actuals),
            'trends' => $this->trends(),
            'detectedUnits' => $this->detectedUnits(),
        ];
    }

    /**
     * Satuan kerja unik yang terdeteksi dari data pengguna aktif — bukti pendukung untuk
     * mengecek kewajaran angka cakupan Satker/Polres yang masih diinput manual oleh admin
     * (belum ada daftar resmi seluruh Satker & Polres jajaran Polda Lampung di sistem ini
     * sebagai penyebut, jadi belum bisa dihitung sebagai persentase otomatis yang pasti).
     */
    private function detectedUnits(): array
    {
        $units = User::where('is_active', true)
            ->whereNotNull('satuan_kerja')
            ->where('satuan_kerja', '!=', '')
            ->distinct()
            ->orderBy('satuan_kerja')
            ->pluck('satuan_kerja');

        $polres = $units->filter(fn ($unit) => str_contains(mb_strtolower($unit), 'polres'))->values();
        $satker = $units->diff($polres)->values();

        return [
            'satker' => $satker,
            'polres' => $polres,
        ];
    }

    private function countTypes(array $names): int
    {
        return Document::whereHas('type', fn ($query) => $query->whereIn('name', $names))->count();
    }

    private function indicators(KpiTarget $target, array $actuals): array
    {
        return [
            $this->higherIndicator('Total dokumen digital', $actuals['documents'], $target->documents_target, 'dokumen', group: 'Dokumen'),
            $this->higherIndicator('Peraturan perundang-undangan', $actuals['legislation'], $target->legislation_target, 'dokumen', group: 'Dokumen'),
            $this->higherIndicator('Produk hukum internal Polri', $actuals['internal_documents'], $target->internal_documents_target, 'dokumen', group: 'Dokumen'),
            $this->higherIndicator('Kajian hukum', $actuals['legal_studies'], $target->legal_studies_target, 'dokumen', group: 'Dokumen'),
            $this->higherIndicator('Materi penyuluhan', $actuals['education_materials'], $target->education_materials_target, 'dokumen', group: 'Dokumen'),
            $this->higherIndicator('Pengguna aktif terdaftar', $actuals['registered_users'], $target->registered_users_target, 'akun', group: 'Pengguna & Akses'),
            $this->higherIndicator('Akses dokumen', $actuals['accesses'], $target->accesses_target, 'akses', group: 'Pengguna & Akses'),
            $this->higherIndicator('Pemanfaatan pengguna 30 hari', $actuals['utilization'], $target->utilization_target_percent, '%', group: 'Pengguna & Akses'),
            $this->higherIndicator('Kepuasan pengguna', $actuals['satisfaction'], $target->satisfaction_target_percent, '%', group: 'Kualitas Layanan'),
            $this->lowerIndicator('Rata-rata waktu pencarian', $actuals['search_time'], $target->search_time_target_seconds, 'detik', group: 'Kualitas Layanan'),
            $this->higherIndicator('Cakupan Satker', $actuals['satker_coverage'], $target->satker_coverage_target_percent, '%', manual: true, group: 'Cakupan Wilayah'),
            $this->higherIndicator('Cakupan Polres', $actuals['polres_coverage'], $target->polres_coverage_target_percent, '%', manual: true, group: 'Cakupan Wilayah'),
        ];
    }

    private function higherIndicator(
        string $label,
        float|int $actual,
        float|int $target,
        string $unit,
        bool $manual = false,
        string $group = 'Lainnya'
    ): array {
        $progress = $target > 0 ? min(100, ($actual / $target) * 100) : 0;

        return [
            'label' => $label,
            'actual' => $actual,
            'target' => $target,
            'unit' => $unit,
            'progress' => round($progress, 1),
            'achieved' => $target > 0 && $actual >= $target,
            'manual' => $manual,
            'group' => $group,
        ];
    }

    private function lowerIndicator(string $label, float|int $actual, float|int $target, string $unit, string $group = 'Lainnya'): array
    {
        $hasData = $actual > 0;
        $progress = $hasData && $actual > 0 ? min(100, ($target / $actual) * 100) : 0;

        return [
            'label' => $label,
            'actual' => $actual,
            'target' => $target,
            'unit' => $unit,
            'progress' => round($progress, 1),
            'achieved' => $hasData && $actual <= $target,
            'manual' => false,
            'has_data' => $hasData,
            'group' => $group,
        ];
    }

    private function trends(): Collection
    {
        return collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $end = $month->copy()->endOfMonth();
            $surveyQuery = SatisfactionSurvey::whereBetween('created_at', [$month, $end]);

            return [
                'label' => $month->translatedFormat('M Y'),
                'accesses' => DocumentAccessLog::whereBetween('accessed_at', [$month, $end])->count(),
                'surveys' => (clone $surveyQuery)->count(),
                'satisfaction' => round((float) ((clone $surveyQuery)
                    ->selectRaw('AVG((accessibility_rating + speed_rating + content_rating + ease_rating + overall_rating) * 4) AS score')
                    ->value('score') ?? 0), 1),
            ];
        });
    }
}
