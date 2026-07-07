<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('admin.backups.index', [
            'backups' => Backup::with(['creator', 'restorer'])
                ->latest()
                ->paginate(15),
            'summary' => [
                'completed' => Backup::whereIn('status', ['completed', 'restored'])->count(),
                'total_size' => Backup::whereIn('status', ['completed', 'restored'])->sum('size_bytes'),
                'latest' => Backup::whereIn('status', ['completed', 'restored'])->latest()->first(),
                'failed' => Backup::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function store(
        Request $request,
        BackupService $backupService,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        try {
            $backup = $backupService->create($request->user(), 'manual');
            $auditLogger->record(
                'created',
                $backup,
                [],
                [
                    'filename' => $backup->filename,
                    'size_bytes' => $backup->size_bytes,
                    'documents_count' => $backup->documents_count,
                    'checksum_sha256' => $backup->checksum_sha256,
                ],
                $request->user(),
                "Membuat backup manual {$backup->filename}.",
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Backup berhasil dibuat dan disimpan secara private.');
    }

    public function download(
        Request $request,
        Backup $backup,
        BackupService $backupService,
        AuditLogger $auditLogger,
    ): BinaryFileResponse|RedirectResponse {
        try {
            $backupService->validateBackup($backup);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $auditLogger->record(
            'downloaded',
            $backup,
            [],
            ['filename' => $backup->filename],
            $request->user(),
            "Mengunduh backup {$backup->filename}.",
        );

        return response()->download(
            Storage::disk(config('backup.disk'))->path($backup->disk_path),
            $backup->filename,
            ['Content-Type' => 'application/zip'],
        );
    }

    public function restore(
        Request $request,
        Backup $backup,
        BackupService $backupService,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:PULIHKAN'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak benar.',
            'confirmation.in' => 'Ketik PULIHKAN untuk mengonfirmasi pemulihan.',
        ]);

        try {
            $restoredBackup = $backupService->restore($backup, $request->user());
            $actor = $request->user()->fresh();
            $auditLogger->record(
                'restored',
                $restoredBackup,
                [],
                [
                    'filename' => $restoredBackup->filename,
                    'restored_at' => $restoredBackup->restored_at?->toIso8601String(),
                ],
                $actor,
                "Memulihkan sistem dari backup {$restoredBackup->filename}.",
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'success',
            'Pemulihan selesai. Silakan masuk kembali menggunakan akun yang tersedia pada backup.',
        );
    }

    public function destroy(
        Request $request,
        Backup $backup,
        BackupService $backupService,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak benar.',
        ]);

        $snapshot = clone $backup;
        $backupService->delete($backup);
        $auditLogger->record(
            'deleted',
            $snapshot,
            $snapshot->getAttributes(),
            [],
            $request->user(),
            "Menghapus arsip backup {$snapshot->filename}.",
        );

        return back()->with('success', 'Arsip backup berhasil dihapus.');
    }
}
