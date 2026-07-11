<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupService
{
    public function create(?User $user = null, string $type = 'manual'): Backup
    {
        $this->ensureMysqlConnection();
        $filename = 'PUSAKA_HUKUM_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.zip';
        $diskPath = trim(config('backup.directory'), '/').'/'.$filename;
        $backup = Backup::create([
            'created_by' => $user?->id,
            'filename' => $filename,
            'disk_path' => $diskPath,
            'type' => $type,
            'status' => 'creating',
        ]);
        $temporaryDirectory = $this->temporaryDirectory('create_'.$backup->id);
        $dumpPath = $temporaryDirectory.'/database.sql';
        $archivePath = Storage::disk(config('backup.disk'))->path($diskPath);

        try {
            File::ensureDirectoryExists(dirname($archivePath));
            $this->dumpDatabase($dumpPath);
            [$documents, $documentBytes] = $this->filesFromDisk('documents', 'documents');
            [$branding, $brandingBytes] = $this->filesFromDisk('public', 'branding');
            $manifest = [
                'application' => 'PUSAKA HUKUM',
                'format_version' => 1,
                'created_at' => now()->toIso8601String(),
                'database' => config('database.connections.mysql.database'),
                'database_dump' => 'database.sql',
                'documents_directory' => 'documents',
                'documents_count' => count($documents),
                'documents_size_bytes' => $documentBytes,
                'branding_files_count' => count($branding),
                'branding_size_bytes' => $brandingBytes,
            ];

            $this->buildArchive($archivePath, $dumpPath, $documents, $branding, $manifest);
            $backup->update([
                'status' => 'completed',
                'size_bytes' => filesize($archivePath) ?: 0,
                'database_size_bytes' => filesize($dumpPath) ?: 0,
                'documents_count' => count($documents),
                'checksum_sha256' => hash_file('sha256', $archivePath),
            ]);

            return $backup->fresh();
        } catch (Throwable $exception) {
            if (is_file($archivePath)) {
                @unlink($archivePath);
            }

            $backup->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
            ]);

            throw new RuntimeException('Backup gagal dibuat: '.$exception->getMessage(), 0, $exception);
        } finally {
            File::deleteDirectory($temporaryDirectory);
        }
    }

    public function restore(Backup $backup, User $user): Backup
    {
        $this->ensureMysqlConnection();
        $this->validateBackup($backup);
        $selectedAttributes = $backup->getAttributes();
        $safetyBackup = $this->create($user, 'pre_restore');
        $safetyAttributes = $safetyBackup->getAttributes();
        $temporaryDirectory = $this->temporaryDirectory('restore_'.$backup->id);

        try {
            $archivePath = Storage::disk(config('backup.disk'))->path($backup->disk_path);
            $this->extractArchive($archivePath, $temporaryDirectory);
            $manifest = json_decode((string) file_get_contents($temporaryDirectory.'/manifest.json'), true);
            $this->validateManifest($manifest);
            $dumpPath = $temporaryDirectory.'/database.sql';

            if (! is_file($dumpPath) || filesize($dumpPath) === 0) {
                throw new RuntimeException('Dump database tidak ditemukan atau kosong.');
            }

            $this->restoreDatabase($dumpPath);
            DB::purge();
            DB::reconnect();
            $this->replaceDirectory(
                $temporaryDirectory.'/documents',
                Storage::disk('documents')->path('documents'),
                'documents_restore_old_',
            );
            $this->replaceDirectory(
                $temporaryDirectory.'/branding',
                Storage::disk('public')->path('branding'),
                'branding_restore_old_',
            );

            Backup::updateOrCreate(
                ['id' => $safetyAttributes['id']],
                collect($safetyAttributes)->except(['id'])->all(),
            );
            $restoredBackup = Backup::updateOrCreate(
                ['id' => $selectedAttributes['id']],
                collect($selectedAttributes)
                    ->except(['id'])
                    ->merge([
                        'status' => 'restored',
                        'restored_by' => $user->id,
                        'restored_at' => now(),
                        'error_message' => null,
                    ])
                    ->all(),
            );

            return $restoredBackup->fresh();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Pemulihan gagal. Backup pengaman tersimpan sebagai '.$safetyBackup->filename.'. '.$exception->getMessage(),
                0,
                $exception,
            );
        } finally {
            File::deleteDirectory($temporaryDirectory);
        }
    }

    public function validateBackup(Backup $backup): void
    {
        if (! in_array($backup->status, ['completed', 'restored'], true)) {
            throw new RuntimeException('Backup belum selesai atau tidak dapat dipulihkan.');
        }

        $this->ensureSafeBackupPath($backup);

        if (! Storage::disk(config('backup.disk'))->exists($backup->disk_path)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        $path = Storage::disk(config('backup.disk'))->path($backup->disk_path);
        $checksum = hash_file('sha256', $path);

        if (! hash_equals((string) $backup->checksum_sha256, $checksum)) {
            throw new RuntimeException('Checksum backup tidak cocok. File mungkin berubah atau rusak.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Arsip backup tidak dapat dibuka.');
        }

        try {
            $manifest = $zip->getFromName('manifest.json');

            if ($manifest === false || $zip->locateName('database.sql') === false) {
                throw new RuntimeException('Struktur arsip backup tidak lengkap.');
            }

            $this->validateManifest(json_decode($manifest, true));
        } finally {
            $zip->close();
        }
    }

    public function delete(Backup $backup): void
    {
        $this->ensureSafeBackupPath($backup);
        Storage::disk(config('backup.disk'))->delete($backup->disk_path);
        $backup->delete();
    }

    public function pruneScheduled(): int
    {
        $retention = max(1, (int) config('backup.retention', 20));
        $expired = Backup::where('type', 'scheduled')
            ->whereIn('status', ['completed', 'restored'])
            ->latest()
            ->skip($retention)
            ->take(PHP_INT_MAX)
            ->get();

        foreach ($expired as $backup) {
            $this->delete($backup);
        }

        return $expired->count();
    }

    private function dumpDatabase(string $path): void
    {
        $process = new Process([
            $this->mysqlBinary('mysqldump'),
            '--host='.$this->databaseConfig('host'),
            '--port='.$this->databaseConfig('port'),
            '--user='.$this->databaseConfig('username'),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--set-gtid-purged=OFF',
            '--no-tablespaces',
            '--add-drop-table',
            $this->databaseConfig('database'),
        ], null, $this->mysqlEnvironment());
        $process->setTimeout((int) config('backup.timeout', 600));
        $handle = fopen($path, 'wb');

        if (! is_resource($handle)) {
            throw new RuntimeException('File dump sementara tidak dapat dibuat.');
        }

        try {
            $process->run(function (string $type, string $buffer) use ($handle) {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);
                }
            });
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump gagal dijalankan.');
        }
    }

    private function restoreDatabase(string $path): void
    {
        $process = new Process([
            $this->mysqlBinary('mysql'),
            '--host='.$this->databaseConfig('host'),
            '--port='.$this->databaseConfig('port'),
            '--user='.$this->databaseConfig('username'),
            '--default-character-set=utf8mb4',
            $this->databaseConfig('database'),
        ], null, $this->mysqlEnvironment());
        $process->setTimeout((int) config('backup.timeout', 600));
        $handle = fopen($path, 'rb');

        if (! is_resource($handle)) {
            throw new RuntimeException('Dump database tidak dapat dibaca.');
        }

        try {
            $process->setInput($handle);
            $process->run();
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysql restore gagal dijalankan.');
        }
    }

    private function buildArchive(
        string $archivePath,
        string $dumpPath,
        array $documents,
        array $branding,
        array $manifest,
    ): void {
        $zip = new ZipArchive;

        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Arsip ZIP backup tidak dapat dibuat.');
        }

        try {
            $zip->addFile($dumpPath, 'database.sql');
            $zip->addFromString(
                'manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            );

            foreach ($documents as $relativePath => $absolutePath) {
                $zip->addFile($absolutePath, 'documents/'.str_replace('\\', '/', $relativePath));
            }

            foreach ($branding as $relativePath => $absolutePath) {
                $zip->addFile($absolutePath, 'branding/'.str_replace('\\', '/', $relativePath));
            }
        } finally {
            $zip->close();
        }

        if (! is_file($archivePath) || filesize($archivePath) === 0) {
            throw new RuntimeException('Arsip backup gagal disimpan.');
        }
    }

    private function filesFromDisk(string $disk, string $directory): array
    {
        $root = Storage::disk($disk)->path($directory);

        if (! is_dir($root)) {
            return [[], 0];
        }

        $files = [];
        $size = 0;

        foreach (File::allFiles($root) as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $files[$relativePath] = $file->getPathname();
            $size += $file->getSize();
        }

        return [$files, $size];
    }

    private function extractArchive(string $archivePath, string $directory): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException('Arsip backup tidak dapat dibuka.');
        }

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = str_replace('\\', '/', (string) $zip->getNameIndex($index));

                if (
                    $entry === ''
                    || str_starts_with($entry, '/')
                    || preg_match('/^[A-Za-z]:/', $entry)
                    || in_array('..', explode('/', $entry), true)
                ) {
                    throw new RuntimeException('Arsip backup mengandung jalur file yang tidak aman.');
                }

                if (
                    ! in_array($entry, ['database.sql', 'manifest.json'], true)
                    && ! str_starts_with($entry, 'documents/')
                    && ! str_starts_with($entry, 'branding/')
                ) {
                    continue;
                }

                if (str_ends_with($entry, '/')) {
                    continue;
                }

                $source = $zip->getStream($entry);

                if (! is_resource($source)) {
                    throw new RuntimeException("File {$entry} tidak dapat dibaca dari arsip.");
                }

                $target = $directory.'/'.$entry;
                File::ensureDirectoryExists(dirname($target));
                $destination = fopen($target, 'wb');

                if (! is_resource($destination)) {
                    fclose($source);
                    throw new RuntimeException("File sementara {$entry} tidak dapat dibuat.");
                }

                stream_copy_to_stream($source, $destination);
                fclose($source);
                fclose($destination);
            }
        } finally {
            $zip->close();
        }
    }

    private function replaceDirectory(
        string $stagedDirectory,
        string $currentDirectory,
        string $oldDirectoryPrefix,
    ): void {
        $oldDirectory = dirname($currentDirectory).'/'.$oldDirectoryPrefix.Str::lower(Str::random(8));
        File::ensureDirectoryExists($stagedDirectory);

        if (is_dir($currentDirectory) && ! rename($currentDirectory, $oldDirectory)) {
            throw new RuntimeException('Folder aset aktif tidak dapat diamankan.');
        }

        try {
            if (! rename($stagedDirectory, $currentDirectory)) {
                throw new RuntimeException('Aset hasil backup tidak dapat diaktifkan.');
            }

            File::deleteDirectory($oldDirectory);
        } catch (Throwable $exception) {
            File::deleteDirectory($currentDirectory);

            if (is_dir($oldDirectory)) {
                rename($oldDirectory, $currentDirectory);
            }

            throw $exception;
        }
    }

    private function validateManifest(mixed $manifest): void
    {
        if (
            ! is_array($manifest)
            || ($manifest['application'] ?? null) !== 'PUSAKA HUKUM'
            || (int) ($manifest['format_version'] ?? 0) !== 1
            || ($manifest['database_dump'] ?? null) !== 'database.sql'
            || ($manifest['database'] ?? null) !== $this->databaseConfig('database')
        ) {
            throw new RuntimeException('Manifest backup tidak valid atau tidak kompatibel.');
        }
    }

    private function ensureSafeBackupPath(Backup $backup): void
    {
        $directory = trim((string) config('backup.directory'), '/').'/';
        $normalized = str_replace('\\', '/', $backup->disk_path);

        if (
            ! str_starts_with($normalized, $directory)
            || str_contains($normalized, '../')
            || basename($normalized) !== $backup->filename
            || strtolower(pathinfo($normalized, PATHINFO_EXTENSION)) !== 'zip'
        ) {
            throw new RuntimeException('Lokasi file backup tidak valid.');
        }
    }

    private function temporaryDirectory(string $suffix): string
    {
        $directory = Storage::disk(config('backup.disk'))
            ->path(trim(config('backup.temporary_directory'), '/').'/'.$suffix.'_'.Str::random(8));
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    private function ensureMysqlConnection(): void
    {
        if (config('database.default') !== 'mysql') {
            throw new RuntimeException('Fitur backup saat ini memerlukan koneksi database MySQL.');
        }
    }

    private function databaseConfig(string $key): string
    {
        return (string) config('database.connections.mysql.'.$key);
    }

    private function mysqlEnvironment(): array
    {
        $password = $this->databaseConfig('password');
        $environment = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $systemRoot = getenv('SystemRoot')
                ?: getenv('WINDIR')
                ?: ($_SERVER['SystemRoot'] ?? null)
                ?: ($_SERVER['WINDIR'] ?? null)
                ?: 'C:\\Windows';

            if (is_string($systemRoot) && $systemRoot !== '' && is_dir($systemRoot)) {
                $environment['SystemRoot'] = $systemRoot;
                $environment['WINDIR'] = $systemRoot;
            }
        }

        if ($password !== '') {
            $environment['MYSQL_PWD'] = $password;
        }

        return $environment;
    }

    private function mysqlBinary(string $binary): string
    {
        $configured = config("backup.{$binary}_binary");

        if (filled($configured) && is_file($configured)) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $matches = glob("C:/laragon/bin/mysql/*/bin/{$binary}.exe") ?: [];
            rsort($matches, SORT_NATURAL);

            if ($matches !== []) {
                return $matches[0];
            }
        }

        return $binary;
    }
}
