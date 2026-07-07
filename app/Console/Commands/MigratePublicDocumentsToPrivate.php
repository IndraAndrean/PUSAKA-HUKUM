<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigratePublicDocumentsToPrivate extends Command
{
    protected $signature = 'documents:migrate-to-private {--keep-public : Keep the source files after copying}';

    protected $description = 'Move legacy document PDFs from public storage to private document storage';

    public function handle(): int
    {
        $documents = Document::whereNotNull('file_path')->get();
        $moved = 0;
        $skipped = 0;
        $missing = 0;

        foreach ($documents as $document) {
            $path = $document->file_path;

            if (Storage::disk('documents')->exists($path)) {
                $this->line("Lewati {$document->document_code}: file sudah private.");
                $skipped++;
                continue;
            }

            if (! Storage::disk('public')->exists($path)) {
                $this->warn("Tidak ditemukan {$document->document_code}: {$path}");
                $missing++;
                continue;
            }

            Storage::disk('documents')->put($path, Storage::disk('public')->get($path));

            if (! $this->option('keep-public')) {
                Storage::disk('public')->delete($path);
            }

            $this->info("Dipindahkan {$document->document_code}: {$path}");
            $moved++;
        }

        $this->newLine();
        $this->table(
            ['Dipindahkan', 'Sudah private', 'Tidak ditemukan'],
            [[$moved, $skipped, $missing]]
        );

        return self::SUCCESS;
    }
}
