<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use RuntimeException;

class CreateSystemBackup extends Command
{
    protected $signature = 'backups:create {--type=manual : Jenis backup: manual atau scheduled}';

    protected $description = 'Membuat backup database dan dokumen private SIPAKEM';

    public function handle(BackupService $backupService): int
    {
        $type = (string) $this->option('type');

        if (! in_array($type, ['manual', 'scheduled'], true)) {
            $this->error('Jenis backup harus manual atau scheduled.');

            return self::FAILURE;
        }

        try {
            $backup = $backupService->create(null, $type);
            $deleted = $type === 'scheduled' ? $backupService->pruneScheduled() : 0;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup selesai: {$backup->filename} ({$backup->formatted_size}).");

        if ($deleted > 0) {
            $this->line("{$deleted} backup terjadwal lama dihapus sesuai kebijakan retensi.");
        }

        return self::SUCCESS;
    }
}
