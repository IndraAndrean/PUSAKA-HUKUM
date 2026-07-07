<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentImportBatch;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Services\DocumentImportService;
use App\Services\DocumentImportTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentImportController extends Controller
{
    public function create(): View
    {
        return view('admin.document-imports.create', [
            'types' => DocumentType::orderBy('name')->get(),
            'categories' => LegalCategory::orderBy('name')->get(),
            'recentBatches' => DocumentImportBatch::with('importer')->latest()->limit(10)->get(),
        ]);
    }

    public function store(Request $request, DocumentImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'spreadsheet' => ['required', 'file', 'extensions:csv,xlsx', 'max:10240'],
            'pdf_archive' => ['required', 'file', 'extensions:zip', 'max:1048576'],
        ], [
            'spreadsheet.extensions' => 'Spreadsheet harus berformat CSV atau XLSX.',
            'spreadsheet.max' => 'Ukuran spreadsheet maksimal 10 MB.',
            'pdf_archive.extensions' => 'Arsip dokumen harus berformat ZIP.',
            'pdf_archive.max' => 'Ukuran ZIP maksimal 1 GB.',
        ]);

        try {
            $batch = $importer->import(
                $validated['spreadsheet'],
                $validated['pdf_archive'],
                $request->user(),
            );
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.document-imports.show', $batch)
            ->with('success', 'Proses import selesai. Periksa ringkasan setiap baris.');
    }

    public function show(DocumentImportBatch $documentImport): View
    {
        $documentImport->load('importer');

        return view('admin.document-imports.show', ['batch' => $documentImport]);
    }

    public function template(
        string $format,
        DocumentImportTemplateService $templateService,
    ): StreamedResponse|BinaryFileResponse {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        if ($format === 'xlsx') {
            return response()
                ->download($templateService->createXlsx(), 'template_import_dokumen.xlsx')
                ->deleteFileAfterSend(true);
        }

        $headers = [
            'judul',
            'jenis_dokumen',
            'nomor_dokumen',
            'tahun',
            'tanggal_penetapan',
            'tanggal_berlaku',
            'instansi_penerbit',
            'status_dokumen',
            'kategori_hukum',
            'bidang_subbidang',
            'kata_kunci',
            'ringkasan',
            'abstrak',
            'dasar_hukum',
            'peraturan_terkait',
            'versi_dokumen',
            'tanggal_review_terakhir',
            'level_akses',
            'nama_file_pdf',
            'penulis',
            'penerbit',
            'isbn_issn',
            'edisi_volume',
        ];

        return response()->streamDownload(function () use ($headers) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, ';');
            fclose($output);
        }, 'template_import_dokumen.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
