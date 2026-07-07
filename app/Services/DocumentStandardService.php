<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentStandardService
{
    public function nextCode(DocumentType $type, int $year): string
    {
        $prefix = $type->code_prefix ?: 'DOC';
        $base = "{$prefix}-{$year}-";
        $lastCode = Document::where('document_code', 'like', $base.'%')
            ->orderByDesc('document_code')
            ->value('document_code');
        $lastSequence = $lastCode ? (int) Str::afterLast($lastCode, '-') : 0;

        do {
            $lastSequence++;
            $code = $base.str_pad((string) $lastSequence, 3, '0', STR_PAD_LEFT);
        } while (Document::where('document_code', $code)->exists());

        return $code;
    }

    public function applyReviewSchedule(array $data, DocumentType $type): array
    {
        $reviewedAt = filled($data['last_reviewed_at'] ?? null)
            ? Carbon::parse($data['last_reviewed_at'])
            : now();

        $data['last_reviewed_at'] = $reviewedAt->toDateString();
        $data['next_review_at'] = $type->review_interval_months > 0
            ? $reviewedAt->copy()->addMonths($type->review_interval_months)->toDateString()
            : null;

        return $data;
    }

    public function storeUploadedPdf(UploadedFile $file, array $data, DocumentType $type): string
    {
        return $file->storeAs('documents', $this->uniqueFilename($data, $type), 'documents');
    }

    /**
     * @param  resource  $stream
     */
    public function storePdfStream($stream, array $data, DocumentType $type): string
    {
        $path = 'documents/'.$this->uniqueFilename($data, $type);

        if (! Storage::disk('documents')->put($path, $stream)) {
            throw new RuntimeException('PDF gagal disimpan ke penyimpanan private.');
        }

        return $path;
    }

    private function uniqueFilename(array $data, DocumentType $type): string
    {
        $fileCode = Str::afterLast($type->code_prefix ?: 'DOC', '-');
        $number = $this->filenamePart($data['document_number'] ?? 'TANPA-NOMOR', 50);
        $year = $data['year'] ?? now()->year;
        $title = $this->filenamePart($data['title'] ?? 'DOKUMEN', 70);
        $base = "{$fileCode}_{$number}_{$year}_{$title}";
        $filename = $base.'.pdf';
        $counter = 2;

        while (Storage::disk('documents')->exists('documents/'.$filename)) {
            $filename = "{$base}_{$counter}.pdf";
            $counter++;
        }

        return $filename;
    }

    private function filenamePart(string $value, int $limit): string
    {
        $value = Str::upper(Str::ascii($value));
        $value = preg_replace('/[^A-Z0-9]+/', '_', $value);

        return trim(Str::limit($value, $limit, ''), '_') ?: 'DOKUMEN';
    }
}
