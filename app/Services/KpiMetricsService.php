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
            'internal_documents' => $this->countTypes(['Peraturan Kapolri', 'Surat Edaran', 'Petunjuk Pelaksanaan', 'Petunjuk Teknis']),
            'legal_studies' => $this->countTypes(['Kajian Hukum', 'Legal Opinion']),
            'education_materials' => $this->countTypes(['Materi Penyuluhan']),
            'registered_users' => $activeUsers,
            'accesses' => DocumentAccessLog::count(),
            'satisfaction' => round($satisfaction, 1),
            'utilization' => round($utilization, 1),
            'search_time' => (int) round($averageSearchSeconds),
            'satker_coverage' => $target->satker_coverage_percent,
            'polres_coverage' => $target->polres_coverage_percent,
            'appointed_admins' => $target->appointed_admin_count,
            'sop_available' => $target->sop_available,
            'user_guide_available' => $target->user_guide_available,
            'monthly_updates' => Document::where('updated_at', '>=', now()->subMonth())->count(),
            'survey_responses' => SatisfactionSurvey::count(),
        ];

        return [
            'actuals' => $actuals,
            'indicators' => $this->indicators($target, $actuals),
            'trends' => $this->trends(),
        ];
    }

    private function countTypes(array $names): int
    {
        return Document::whereHas('type', fn ($query) => $query->whereIn('name', $names))->count();
    }

    private function indicators(KpiTarget $target, array $actuals): array
    {
        return [
            $this->higherIndicator('Total dokumen digital', $actuals['documents'], $target->documents_target, 'dokumen'),
            $this->higherIndicator('Peraturan perundang-undangan', $actuals['legislation'], $target->legislation_target, 'dokumen'),
            $this->higherIndicator('Produk hukum internal Polri', $actuals['internal_documents'], $target->internal_documents_target, 'dokumen'),
            $this->higherIndicator('Kajian dan legal opinion', $actuals['legal_studies'], $target->legal_studies_target, 'dokumen'),
            $this->higherIndicator('Materi penyuluhan', $actuals['education_materials'], $target->education_materials_target, 'dokumen'),
            $this->higherIndicator('Pengguna aktif terdaftar', $actuals['registered_users'], $target->registered_users_target, 'akun'),
            $this->higherIndicator('Akses dokumen', $actuals['accesses'], $target->accesses_target, 'akses'),
            $this->higherIndicator('Kepuasan pengguna', $actuals['satisfaction'], $target->satisfaction_target_percent, '%'),
            $this->higherIndicator('Pemanfaatan pengguna 30 hari', $actuals['utilization'], $target->utilization_target_percent, '%'),
            $this->lowerIndicator('Rata-rata waktu pencarian', $actuals['search_time'], $target->search_time_target_seconds, 'detik'),
            $this->higherIndicator('Cakupan Satker', $actuals['satker_coverage'], $target->satker_coverage_target_percent, '%', true),
            $this->higherIndicator('Cakupan Polres', $actuals['polres_coverage'], $target->polres_coverage_target_percent, '%', true),
        ];
    }

    private function higherIndicator(
        string $label,
        float|int $actual,
        float|int $target,
        string $unit,
        bool $manual = false
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
        ];
    }

    private function lowerIndicator(string $label, float|int $actual, float|int $target, string $unit): array
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
