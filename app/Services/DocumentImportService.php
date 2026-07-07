<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentImportBatch;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;
use ZipArchive;

class DocumentImportService
{
    private const REQUIRED_HEADERS = [
        'title',
        'document_type',
        'document_number',
        'year',
        'enacted_date',
        'effective_date',
        'issuing_institution',
        'document_status',
        'legal_category',
        'bidang_subbidang',
        'keywords',
        'summary',
        'document_version',
        'access_level',
        'pdf_filename',
    ];

    public function __construct(
        private readonly SpreadsheetTableReader $reader,
        private readonly DocumentStandardService $standards,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function import(UploadedFile $spreadsheet, UploadedFile $pdfArchive, User $user): DocumentImportBatch
    {
        $rows = $this->reader->read($spreadsheet);

        if (count($rows) > 500) {
            throw new RuntimeException('Satu proses impor maksimal 500 baris dokumen.');
        }

        $this->ensureRequiredHeaders($rows[0]);

        $zip = new ZipArchive;

        if ($zip->open($pdfArchive->getRealPath()) !== true) {
            throw new RuntimeException('Arsip ZIP PDF tidak dapat dibuka.');
        }

        try {
            $pdfEntries = $this->indexPdfEntries($zip);
            $batch = DocumentImportBatch::create([
                'imported_by' => $user->id,
                'spreadsheet_name' => $spreadsheet->getClientOriginalName(),
                'pdf_archive_name' => $pdfArchive->getClientOriginalName(),
                'status' => 'failed',
            ]);

            $results = [];
            $seenDocuments = [];
            $successCount = 0;

            foreach ($rows as $row) {
                $result = $this->importRow($zip, $pdfEntries, $row, $user, $seenDocuments);
                $results[] = $result;

                if ($result['status'] === 'success') {
                    $successCount++;
                }
            }

            $failedCount = count($results) - $successCount;
            $batch->update([
                'total_rows' => count($results),
                'successful_rows' => $successCount,
                'failed_rows' => $failedCount,
                'status' => $failedCount === 0 ? 'completed' : ($successCount > 0 ? 'completed_with_errors' : 'failed'),
                'results' => $results,
            ]);

            $batch = $batch->fresh();
            $this->auditLogger->record(
                'imported',
                $batch,
                [],
                [
                    'spreadsheet_name' => $batch->spreadsheet_name,
                    'pdf_archive_name' => $batch->pdf_archive_name,
                    'total_rows' => $batch->total_rows,
                    'successful_rows' => $batch->successful_rows,
                    'failed_rows' => $batch->failed_rows,
                    'status' => $batch->status,
                ],
                $user,
                "Mengimpor {$batch->total_rows} baris dokumen: {$batch->successful_rows} berhasil dan {$batch->failed_rows} gagal.",
            );

            return $batch;
        } finally {
            $zip->close();
        }
    }

    private function importRow(
        ZipArchive $zip,
        array $pdfEntries,
        array $row,
        User $user,
        array &$seenDocuments,
    ): array {
        $rowNumber = (int) ($row['_row'] ?? 0);
        $title = trim((string) ($row['title'] ?? ''));
        $errors = [];
        $type = $this->resolveType((string) ($row['document_type'] ?? ''));
        $category = $this->resolveCategory((string) ($row['legal_category'] ?? ''));
        $library = $type?->isLibrary() ?? false;

        if (! $type) {
            $errors[] = 'Jenis dokumen tidak ditemukan. Gunakan nama, slug, atau kode prefix yang terdaftar.';
        }

        if (! $category) {
            $errors[] = 'Kategori hukum tidak ditemukan. Gunakan nama atau slug yang terdaftar.';
        }

        $data = [
            'title' => $title,
            'author' => $this->nullable($row['author'] ?? null),
            'document_type_id' => $type?->id,
            'document_number' => trim((string) ($row['document_number'] ?? '')),
            'year' => trim((string) ($row['year'] ?? '')),
            'enacted_date' => $this->normalizeDate($row['enacted_date'] ?? null),
            'effective_date' => $this->normalizeDate($row['effective_date'] ?? null),
            'issuing_institution' => trim((string) ($row['issuing_institution'] ?? '')),
            'publisher' => $this->nullable($row['publisher'] ?? null),
            'isbn_issn' => $this->nullable($row['isbn_issn'] ?? null),
            'edition_volume' => $this->nullable($row['edition_volume'] ?? null),
            'document_status' => strtolower(trim((string) ($row['document_status'] ?? ''))),
            'legal_category_id' => $category?->id,
            'bidang_subbidang' => strtolower(trim((string) ($row['bidang_subbidang'] ?? ''))),
            'keywords' => trim((string) ($row['keywords'] ?? '')),
            'summary' => trim((string) ($row['summary'] ?? '')),
            'abstract' => $this->nullable($row['abstract'] ?? null),
            'legal_basis' => $this->nullable($row['legal_basis'] ?? null),
            'related_regulation' => $this->nullable($row['related_regulation'] ?? null),
            'document_version' => trim((string) ($row['document_version'] ?? '')),
            'last_reviewed_at' => $this->normalizeDate($row['last_reviewed_at'] ?? null, true),
            'access_level' => strtolower(trim((string) ($row['access_level'] ?? ''))),
        ];

        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'author' => [$library ? 'required' : 'nullable', 'string', 'max:255'],
            'document_type_id' => ['required', 'integer'],
            'document_number' => [$library ? 'nullable' : 'required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'enacted_date' => [$library ? 'nullable' : 'required', 'date_format:Y-m-d'],
            'effective_date' => [$library ? 'nullable' : 'required', 'date_format:Y-m-d', 'after_or_equal:enacted_date'],
            'issuing_institution' => ['required', 'string', 'max:255'],
            'publisher' => [$library ? 'required' : 'nullable', 'string', 'max:255'],
            'isbn_issn' => ['nullable', 'string', 'max:50'],
            'edition_volume' => ['nullable', 'string', 'max:100'],
            'document_status' => ['required', Rule::in(['berlaku', 'dicabut', 'diubah', 'tidak_berlaku'])],
            'legal_category_id' => ['required', 'integer'],
            'bidang_subbidang' => ['required', Rule::in(['kum', 'bankum', 'sunluhkum'])],
            'keywords' => ['required', 'string'],
            'summary' => ['required', 'string', 'min:20'],
            'abstract' => ['nullable', 'string'],
            'legal_basis' => ['nullable', 'string'],
            'related_regulation' => ['nullable', 'string'],
            'document_version' => ['required', 'string', 'max:30'],
            'last_reviewed_at' => ['nullable', 'date_format:Y-m-d'],
            'access_level' => ['required', Rule::in(['publik', 'internal', 'terbatas'])],
        ]);

        $validator->after(function ($validator) use ($data, $type, $library, &$seenDocuments) {
            $keywords = collect(preg_split('/[,;]+/', $data['keywords']))
                ->map(fn ($keyword) => trim($keyword))
                ->filter()
                ->unique(fn ($keyword) => mb_strtolower($keyword));

            if ($keywords->count() < 3) {
                $validator->errors()->add('keywords', 'Kata kunci minimal 3 dan dipisahkan dengan koma.');
            }

            if (! $type || ! is_numeric($data['year'])) {
                return;
            }

            $duplicateValue = filled($data['document_number'])
                ? 'number:'.mb_strtolower($data['document_number'])
                : 'title:'.mb_strtolower($data['title']);
            $duplicateKey = $type->id.'|'.$duplicateValue.'|'.$data['year'];

            if (isset($seenDocuments[$duplicateKey])) {
                $validator->errors()->add(
                    $library && blank($data['document_number']) ? 'title' : 'document_number',
                    'Referensi duplikat dengan identitas dan tahun yang sama terdapat pada baris lain.'
                );
            } else {
                $duplicate = Document::where('document_type_id', $type->id)
                    ->where('year', (int) $data['year'])
                    ->when(
                        filled($data['document_number']),
                        fn ($query) => $query->whereRaw('LOWER(document_number) = ?', [mb_strtolower($data['document_number'])]),
                        fn ($query) => $query->whereRaw('LOWER(title) = ?', [mb_strtolower($data['title'])]),
                    )
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        $library && blank($data['document_number']) ? 'title' : 'document_number',
                        'Referensi duplikat dengan identitas dan tahun tersebut sudah terdaftar.'
                    );
                }
            }

            $seenDocuments[$duplicateKey] = true;
        });

        if ($validator->fails()) {
            $errors = [...$errors, ...$validator->errors()->all()];
        }

        $pdfName = basename(str_replace('\\', '/', trim((string) ($row['pdf_filename'] ?? ''))));
        $pdfKey = mb_strtolower($pdfName);
        $pdfEntry = $pdfEntries[$pdfKey] ?? null;

        if ($pdfName === '' || strtolower(pathinfo($pdfName, PATHINFO_EXTENSION)) !== 'pdf') {
            $errors[] = 'Nama file PDF wajib diisi dan harus berakhiran .pdf.';
        } elseif (! $pdfEntry) {
            $errors[] = "File {$pdfName} tidak ditemukan di dalam ZIP.";
        } elseif ($pdfEntry['size'] > 20 * 1024 * 1024) {
            $errors[] = "Ukuran {$pdfName} melebihi batas 20 MB.";
        }

        if ($errors !== []) {
            return $this->failedResult($rowNumber, $title, $errors);
        }

        $storedPath = null;

        try {
            $document = DB::transaction(function () use ($zip, $pdfEntry, $data, $type, $user, &$storedPath) {
                $stream = $zip->getStream($pdfEntry['name']);

                if (! is_resource($stream)) {
                    throw new RuntimeException('File PDF di dalam ZIP tidak dapat dibaca.');
                }

                $signature = fread($stream, 5);
                fclose($stream);

                if ($signature !== '%PDF-') {
                    throw new RuntimeException('Isi file tidak dikenali sebagai PDF yang valid.');
                }

                $stream = $zip->getStream($pdfEntry['name']);

                if (! is_resource($stream)) {
                    throw new RuntimeException('File PDF di dalam ZIP tidak dapat dibaca ulang.');
                }

                $prepared = $this->standards->applyReviewSchedule($data, $type);
                $storedPath = $this->standards->storePdfStream($stream, $prepared, $type);
                fclose($stream);

                $prepared['document_code'] = $this->standards->nextCode($type, (int) $prepared['year']);
                $prepared['file_path'] = $storedPath;
                $prepared['uploaded_by'] = $user->id;

                return Document::create($prepared);
            });

            return [
                'row' => $rowNumber,
                'title' => $document->title,
                'status' => 'success',
                'document_id' => $document->id,
                'document_code' => $document->document_code,
                'errors' => [],
            ];
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('documents')->delete($storedPath);
            }

            return $this->failedResult($rowNumber, $title, [$exception->getMessage()]);
        }
    }

    private function indexPdfEntries(ZipArchive $zip): array
    {
        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));

            if ($name === '' || str_ends_with($name, '/')) {
                continue;
            }

            $segments = explode('/', $name);

            if (
                str_starts_with($name, '/')
                || preg_match('/^[A-Za-z]:/', $name)
                || in_array('..', $segments, true)
            ) {
                throw new RuntimeException('ZIP mengandung jalur file yang tidak aman.');
            }

            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            $key = mb_strtolower(basename($name));

            if (isset($entries[$key])) {
                throw new RuntimeException('ZIP mengandung nama PDF ganda: '.basename($name));
            }

            $entries[$key] = [
                'name' => $name,
                'size' => (int) ($stat['size'] ?? 0),
            ];
        }

        return $entries;
    }

    private function ensureRequiredHeaders(array $row): void
    {
        $missing = array_diff(self::REQUIRED_HEADERS, array_keys($row));

        if ($missing !== []) {
            throw new RuntimeException('Kolom wajib belum tersedia: '.implode(', ', $missing).'.');
        }
    }

    private function resolveType(string $value): ?DocumentType
    {
        $value = mb_strtolower(trim($value));

        return DocumentType::query()
            ->whereRaw('LOWER(name) = ?', [$value])
            ->orWhereRaw('LOWER(slug) = ?', [$value])
            ->orWhereRaw('LOWER(code_prefix) = ?', [$value])
            ->first();
    }

    private function resolveCategory(string $value): ?LegalCategory
    {
        $value = mb_strtolower(trim($value));

        return LegalCategory::query()
            ->whereRaw('LOWER(name) = ?', [$value])
            ->orWhereRaw('LOWER(slug) = ?', [$value])
            ->first();
    }

    private function normalizeDate(mixed $value, bool $nullable = false): ?string
    {
        if (blank($value)) {
            return $nullable ? null : '';
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) floor((float) $value))->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function failedResult(int $row, string $title, array $errors): array
    {
        return [
            'row' => $row,
            'title' => $title ?: '(tanpa judul)',
            'status' => 'failed',
            'document_id' => null,
            'document_code' => null,
            'errors' => array_values(array_unique($errors)),
        ];
    }
}
