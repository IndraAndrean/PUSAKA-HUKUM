<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentImportBatch;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Models\User;
use App\Services\DocumentImportTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DocumentBulkImportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_import_valid_csv_and_private_pdf_zip(): void
    {
        Storage::fake('documents');
        [$admin, $type, $category] = $this->references();
        $csv = $this->csvFile([
            $this->validRow($type, $category, '11', 'dokumen-11.pdf'),
        ]);
        $zip = $this->zipFile(['dokumen-11.pdf' => "%PDF-1.4\nDokumen pengujian"]);

        $response = $this->actingAs($admin)->post(route('admin.document-imports.store'), [
            'spreadsheet' => $csv,
            'pdf_archive' => $zip,
        ]);

        $batch = DocumentImportBatch::latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.document-imports.show', $batch));
        $this->assertSame(1, $batch->successful_rows, json_encode($batch->results));

        $document = Document::where('document_number', '11')->firstOrFail();
        $this->assertSame(0, $batch->failed_rows);
        $this->assertSame('completed', $batch->status);
        $this->assertSame($admin->id, $document->uploaded_by);
        $this->assertSame('POL-TEST-2026-001', $document->document_code);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'imported',
            'module' => 'Import Dokumen',
            'subject_id' => $batch->id,
        ]);
        Storage::disk('documents')->assertExists($document->file_path);
    }

    public function test_import_continues_and_reports_duplicate_row(): void
    {
        Storage::fake('documents');
        [$admin, $type, $category] = $this->references();
        $csv = $this->csvFile([
            $this->validRow($type, $category, '12', 'dokumen-a.pdf'),
            $this->validRow($type, $category, '12', 'dokumen-b.pdf'),
        ]);
        $zip = $this->zipFile([
            'dokumen-a.pdf' => "%PDF-1.4\nDokumen A",
            'dokumen-b.pdf' => "%PDF-1.4\nDokumen B",
        ]);

        $this->actingAs($admin)->post(route('admin.document-imports.store'), [
            'spreadsheet' => $csv,
            'pdf_archive' => $zip,
        ]);

        $batch = DocumentImportBatch::latest('id')->firstOrFail();
        $this->assertSame(1, $batch->successful_rows);
        $this->assertSame(1, $batch->failed_rows);
        $this->assertSame('completed_with_errors', $batch->status);
        $this->assertStringContainsString('duplikat', strtolower(implode(' ', $batch->results[1]['errors'])));
        $this->assertSame(1, Document::where('document_number', '12')->count());
    }

    public function test_missing_pdf_is_reported_without_creating_document(): void
    {
        Storage::fake('documents');
        [$admin, $type, $category] = $this->references();
        $csv = $this->csvFile([
            $this->validRow($type, $category, '13', 'tidak-ada.pdf'),
        ]);
        $zip = $this->zipFile(['file-lain.pdf' => "%PDF-1.4\nDokumen lain"]);

        $this->actingAs($admin)->post(route('admin.document-imports.store'), [
            'spreadsheet' => $csv,
            'pdf_archive' => $zip,
        ]);

        $batch = DocumentImportBatch::latest('id')->firstOrFail();
        $this->assertSame('failed', $batch->status);
        $this->assertSame(0, $batch->successful_rows);
        $this->assertStringContainsString('tidak ditemukan', strtolower(implode(' ', $batch->results[0]['errors'])));
        $this->assertFalse(Document::where('document_number', '13')->exists());
    }

    public function test_zip_path_traversal_is_rejected(): void
    {
        Storage::fake('documents');
        [$admin, $type, $category] = $this->references();
        $csv = $this->csvFile([
            $this->validRow($type, $category, '14', 'dokumen.pdf'),
        ]);
        $zip = $this->zipFile(['../dokumen.pdf' => "%PDF-1.4\nDokumen"]);

        $this->actingAs($admin)->from(route('admin.document-imports.create'))
            ->post(route('admin.document-imports.store'), [
                'spreadsheet' => $csv,
                'pdf_archive' => $zip,
            ])
            ->assertRedirect(route('admin.document-imports.create'))
            ->assertSessionHas('error', 'ZIP mengandung jalur file yang tidak aman.');

        $this->assertSame(0, DocumentImportBatch::count());
    }

    public function test_generated_xlsx_template_can_be_imported(): void
    {
        Storage::fake('documents');
        [$admin, $type, $category] = $this->references();
        $path = app(DocumentImportTemplateService::class)->createXlsx();
        $this->appendXlsxRow($path, $this->validRow($type, $category, '15', 'dokumen-15.pdf'));
        $xlsx = new UploadedFile(
            $path,
            'template-terisi.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
        $zip = $this->zipFile(['dokumen-15.pdf' => "%PDF-1.4\nDokumen XLSX"]);

        $response = $this->actingAs($admin)->post(route('admin.document-imports.store'), [
            'spreadsheet' => $xlsx,
            'pdf_archive' => $zip,
        ]);
        @unlink($path);

        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $this->assertSame(302, $response->getStatusCode(), $response->getContent());
        $this->assertNotSame(
            route('admin.document-imports.create'),
            $response->headers->get('Location'),
            json_encode($response->headers->all()),
        );
        $batch = DocumentImportBatch::latest('id')->first();
        $this->assertNotNull($batch, json_encode(session()->all()));
        $this->assertSame(1, $batch->successful_rows);
        $this->assertTrue(Document::where('document_number', '15')->exists());
    }

    public function test_internal_user_cannot_open_bulk_import_page(): void
    {
        $user = User::create([
            'name' => 'Pengguna Internal Import',
            'email' => 'internal-import-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.document-imports.create'))
            ->assertForbidden();
    }

    private function references(): array
    {
        $admin = User::create([
            'name' => 'Admin Import',
            'email' => 'admin-import-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $type = DocumentType::create([
            'name' => 'Jenis Import '.uniqid(),
            'slug' => 'jenis-import-'.uniqid(),
            'code_prefix' => 'POL-TEST',
            'review_interval_months' => 3,
        ]);
        $category = LegalCategory::create([
            'name' => 'Kategori Import '.uniqid(),
            'slug' => 'kategori-import-'.uniqid(),
        ]);

        return [$admin, $type, $category];
    }

    private function validRow(
        DocumentType $type,
        LegalCategory $category,
        string $number,
        string $pdf,
    ): array {
        return [
            'Dokumen Import Nomor '.$number,
            $type->code_prefix,
            $number,
            '2026',
            '2026-01-01',
            '2026-01-02',
            'Bidkum Polda Lampung',
            'berlaku',
            $category->name,
            'kum',
            'hukum, kepolisian, lampung',
            'Ringkasan dokumen hukum untuk pengujian fitur import massal.',
            'Abstrak pengujian.',
            'Undang-Undang Dasar 1945.',
            'Peraturan terkait.',
            '1.0',
            '2026-01-10',
            'internal',
            $pdf,
        ];
    }

    private function csvFile(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pusaka_csv_');
        $handle = fopen($path, 'wb');
        fputcsv($handle, [
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
        ], ';');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }

        fclose($handle);

        return new UploadedFile($path, 'import-dokumen.csv', 'text/csv', null, true);
    }

    private function zipFile(array $files): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pusaka_zip_');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $name => $contents) {
            $zip->addFromString($name, $contents);
        }

        $zip->close();

        return new UploadedFile($path, 'dokumen.zip', 'application/zip', null, true);
    }

    private function appendXlsxRow(string $path, array $values): void
    {
        $zip = new ZipArchive;
        $zip->open($path);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $cells = '';

        foreach ($values as $index => $value) {
            $reference = $this->columnName($index + 1).'2';
            $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $cells .= "<c r=\"{$reference}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>";
        }

        $sheet = str_replace('</sheetData>', "<row r=\"2\">{$cells}</row></sheetData>", $sheet);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
