<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NormalizeDocumentStandards extends Command
{
    protected $signature = 'documents:normalize-standards {--dry-run : Show changes without updating data or files}';

    protected $description = 'Normalize legacy document codes and PDF filenames to the institutional standard';

    public function handle(): int
    {
        $changed = 0;
        $skipped = 0;

        Document::with('type')->orderBy('id')->each(function (Document $document) use (&$changed, &$skipped) {
            if (! $document->type?->code_prefix || ! $document->year) {
                $this->warn("Lewati {$document->id}: jenis/prefix/tahun belum lengkap.");
                $skipped++;
                return;
            }

            $updates = [];
            $expectedPattern = '/^'.preg_quote($document->type->code_prefix, '/').'-'.$document->year.'-\d{3}$/';

            if (! preg_match($expectedPattern, $document->document_code)) {
                $updates['document_code'] = $this->nextCode($document);
            }

            if ($document->file_path && Storage::disk('documents')->exists($document->file_path)) {
                $newPath = 'documents/'.$this->standardFilename($document);

                if ($newPath !== $document->file_path) {
                    $newPath = $this->uniquePath($newPath, $document->file_path);

                    if (! $this->option('dry-run')) {
                        Storage::disk('documents')->move($document->file_path, $newPath);
                    }

                    $updates['file_path'] = $newPath;
                }
            }

            if ($updates === []) {
                $this->line("Sesuai {$document->document_code}");
                return;
            }

            $this->info(
                ($this->option('dry-run') ? '[DRY RUN] ' : '').
                "{$document->document_code} -> ".($updates['document_code'] ?? $document->document_code)
            );

            if (! $this->option('dry-run')) {
                $document->update($updates);
            }

            $changed++;
        });

        $this->newLine();
        $this->table(['Dinormalisasi', 'Dilewati'], [[$changed, $skipped]]);

        return self::SUCCESS;
    }

    private function nextCode(Document $document): string
    {
        $base = "{$document->type->code_prefix}-{$document->year}-";
        $lastCode = Document::where('document_code', 'like', $base.'%')
            ->whereKeyNot($document->id)
            ->orderByDesc('document_code')
            ->value('document_code');
        $sequence = $lastCode ? (int) Str::afterLast($lastCode, '-') : 0;

        do {
            $sequence++;
            $code = $base.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        } while (Document::where('document_code', $code)->whereKeyNot($document->id)->exists());

        return $code;
    }

    private function standardFilename(Document $document): string
    {
        $fileCode = Str::afterLast($document->type->code_prefix, '-');
        $number = $this->filenamePart($document->document_number ?: 'TANPA-NOMOR', 50);
        $title = $this->filenamePart($document->title, 70);

        return "{$fileCode}_{$number}_{$document->year}_{$title}.pdf";
    }

    private function filenamePart(string $value, int $limit): string
    {
        $value = Str::upper(Str::ascii($value));
        $value = preg_replace('/[^A-Z0-9]+/', '_', $value);

        return trim(Str::limit($value, $limit, ''), '_') ?: 'DOKUMEN';
    }

    private function uniquePath(string $targetPath, string $currentPath): string
    {
        if ($targetPath === $currentPath || ! Storage::disk('documents')->exists($targetPath)) {
            return $targetPath;
        }

        $directory = Str::beforeLast($targetPath, '/');
        $filename = Str::afterLast($targetPath, '/');
        $base = Str::beforeLast($filename, '.pdf');
        $counter = 2;

        do {
            $candidate = "{$directory}/{$base}_{$counter}.pdf";
            $counter++;
        } while (Storage::disk('documents')->exists($candidate));

        return $candidate;
    }
}
