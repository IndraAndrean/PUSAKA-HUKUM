<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KpiTarget;
use App\Models\SatisfactionSurvey;
use App\Services\KpiMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KpiController extends Controller
{
    public function index(KpiMetricsService $metricsService): View
    {
        $target = KpiTarget::current();
        $metrics = $metricsService->build($target);

        return view('admin.kpi.index', [
            'target' => $target,
            'actuals' => $metrics['actuals'],
            'indicators' => $metrics['indicators'],
            'trends' => $metrics['trends'],
            'detectedUnits' => $metrics['detectedUnits'],
            'surveys' => SatisfactionSurvey::with('user')->latest()->paginate(10),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'documents_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'legislation_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'internal_documents_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'legal_studies_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'education_materials_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'registered_users_target' => ['required', 'integer', 'min:1', 'max:1000000'],
            'accesses_target' => ['required', 'integer', 'min:1', 'max:100000000'],
            'satisfaction_target_percent' => ['required', 'numeric', 'between:1,100'],
            'utilization_target_percent' => ['required', 'numeric', 'between:1,100'],
            'search_time_target_seconds' => ['required', 'integer', 'min:1', 'max:7200'],
            'satker_coverage_target_percent' => ['required', 'numeric', 'between:1,100'],
            'polres_coverage_target_percent' => ['required', 'numeric', 'between:1,100'],
            'satker_coverage_percent' => ['required', 'numeric', 'between:0,100'],
            'polres_coverage_percent' => ['required', 'numeric', 'between:0,100'],
            'sop_available' => ['nullable', 'boolean'],
            'user_guide_available' => ['nullable', 'boolean'],
            'verification_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $validated['sop_available'] = $request->boolean('sop_available');
        $validated['user_guide_available'] = $request->boolean('user_guide_available');
        $validated['updated_by'] = $request->user()->id;

        KpiTarget::current()->update($validated);

        return back()->with('success', 'Target dan verifikasi indikator berhasil diperbarui.');
    }

    public function export(KpiMetricsService $metricsService): StreamedResponse
    {
        $target = KpiTarget::current();
        $metrics = $metricsService->build($target);
        $filename = 'laporan-kpi-pusaka-hukum-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($metrics, $target) {
            $output = fopen('php://output', 'w');
            $write = fn (array $row) => fputcsv($output, array_map(self::csvSafe(...), $row));

            fwrite($output, "\xEF\xBB\xBF");
            $write(['LAPORAN INDIKATOR KEBERHASILAN PUSAKA HUKUM']);
            $write(['Tanggal laporan', now()->format('d/m/Y H:i')]);
            $write([]);
            $write(['Indikator', 'Realisasi', 'Target', 'Satuan', 'Capaian (%)', 'Status', 'Sumber']);

            foreach ($metrics['indicators'] as $indicator) {
                $write([
                    $indicator['label'],
                    $indicator['actual'],
                    $indicator['target'],
                    $indicator['unit'],
                    $indicator['progress'],
                    $indicator['achieved'] ? 'Tercapai' : 'Belum tercapai',
                    $indicator['manual'] ? 'Verifikasi admin' : 'Data sistem',
                ]);
            }

            $write([]);
            $write(['Respons survei', $metrics['actuals']['survey_responses']]);
            $write(['Dokumen diperbarui 30 hari terakhir', $metrics['actuals']['monthly_updates']]);
            $write(['Admin ditunjuk', $metrics['actuals']['appointed_admins']]);
            $write(['SOP tersedia', $target->sop_available ? 'Ya' : 'Belum']);
            $write(['Panduan pengguna tersedia', $target->user_guide_available ? 'Ya' : 'Belum']);
            $write(['Catatan verifikasi', $target->verification_notes]);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prefix values that start with a formula-triggering character so
     * spreadsheet apps (Excel/LibreOffice) never interpret them as formulas.
     */
    private static function csvSafe(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
