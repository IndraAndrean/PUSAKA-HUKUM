@extends('layouts.admin')

@section('title', 'Backup Sistem')
@section('page_title', 'Backup dan Pemulihan Sistem')

@section('page_actions')
    <form method="post" action="{{ route('admin.backups.store') }}" onsubmit="return confirm('Buat backup database dan seluruh dokumen sekarang?')">
        @csrf
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-database-add"></i> Buat Backup
        </button>
    </form>
@endsection

@section('content')
@php
    $formatBytes = function ($bytes) {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }
        return number_format($bytes, $unit === 0 ? 0 : 2, ',', '.').' '.$units[$unit];
    };
@endphp

<div class="alert alert-warning d-flex gap-3 align-items-start">
    <i class="bi bi-exclamation-triangle fs-4"></i>
    <div>
        <div class="fw-semibold">Pemulihan mengganti database dan seluruh dokumen aktif.</div>
        <div class="small">Sistem otomatis membuat backup pengaman sebelum restore. Setelah selesai, seluruh pengguna akan diminta masuk kembali.</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Backup Tersedia</div>
            <div class="fs-3 fw-semibold">{{ $summary['completed'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Total Penyimpanan</div>
            <div class="fs-3 fw-semibold">{{ $formatBytes($summary['total_size']) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Backup Terakhir</div>
            <div class="fw-semibold mt-2">{{ $summary['latest']?->created_at?->format('d/m/Y H:i') ?? 'Belum ada' }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Backup Gagal</div>
            <div class="fs-3 fw-semibold {{ $summary['failed'] > 0 ? 'text-danger' : '' }}">{{ $summary['failed'] }}</div>
        </div>
    </div>
</div>

<div class="content-card p-3">
    <div class="d-flex justify-content-between align-items-center gap-3 px-1 mb-3">
        <div>
            <h2 class="h5 mb-1">Riwayat Backup</h2>
            <div class="small text-muted">Backup terjadwal dijalankan setiap hari pukul 01.30 ketika Laravel Scheduler aktif.</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Waktu</th>
                <th>Arsip</th>
                <th>Jenis</th>
                <th>Isi Backup</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($backups as $backup)
                @php
                    $statusBadge = match($backup->status) {
                        'completed' => 'text-bg-success',
                        'restored' => 'text-bg-primary',
                        'failed' => 'text-bg-danger',
                        default => 'text-bg-warning',
                    };
                    $typeLabel = match($backup->type) {
                        'scheduled' => 'Terjadwal',
                        'pre_restore' => 'Pengaman Restore',
                        default => 'Manual',
                    };
                @endphp
                <tr>
                    <td class="text-nowrap">
                        <div>{{ $backup->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $backup->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold text-break">{{ $backup->filename }}</div>
                        <small class="text-muted">{{ $backup->creator?->name ?? 'Sistem' }} · {{ $backup->formatted_size }}</small>
                        @if($backup->checksum_sha256)
                            <div class="small text-muted font-monospace text-truncate" style="max-width: 280px" title="{{ $backup->checksum_sha256 }}">
                                SHA-256 {{ $backup->checksum_sha256 }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $typeLabel }}</td>
                    <td>
                        <div>{{ $backup->documents_count }} PDF</div>
                        <small class="text-muted">Database {{ $formatBytes($backup->database_size_bytes) }}</small>
                    </td>
                    <td>
                        <span class="badge {{ $statusBadge }}">{{ ucfirst($backup->status) }}</span>
                        @if($backup->restored_at)
                            <div class="small text-muted mt-1">Dipulihkan {{ $backup->restored_at->format('d/m/Y H:i') }}</div>
                        @endif
                        @if($backup->error_message)
                            <div class="small text-danger mt-1">{{ $backup->error_message }}</div>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if(in_array($backup->status, ['completed', 'restored'], true))
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.backups.download', $backup) }}" title="Unduh backup">
                                <i class="bi bi-download"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-ui-toggle="modal" data-ui-target="#restoreBackup{{ $backup->id }}" title="Pulihkan backup">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" type="button" data-ui-toggle="modal" data-ui-target="#deleteBackup{{ $backup->id }}" title="Hapus backup">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>

            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada backup sistem.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($backups->hasPages())
        <div class="mt-3">{{ $backups->links() }}</div>
    @endif
</div>

@foreach($backups as $backup)
    @if(in_array($backup->status, ['completed', 'restored'], true))
        <div class="modal fade" id="restoreBackup{{ $backup->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.backups.restore', $backup) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Pulihkan Sistem</h2>
                            <button class="btn-close" type="button" data-ui-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p>Database dan dokumen aktif akan diganti dengan isi <strong>{{ $backup->filename }}</strong>.</p>
                            <div class="mb-3">
                                <label class="form-label" for="restore-password-{{ $backup->id }}">Kata Sandi saat ini</label>
                                <input class="form-control" id="restore-password-{{ $backup->id }}" name="current_password" type="password" autocomplete="current-password" required>
                            </div>
                            <div>
                                <label class="form-label" for="restore-confirmation-{{ $backup->id }}">Ketik <strong>PULIHKAN</strong></label>
                                <input class="form-control" id="restore-confirmation-{{ $backup->id }}" name="confirmation" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" type="button" data-ui-dismiss="modal">Batal</button>
                            <button class="btn btn-danger" type="submit"><i class="bi bi-arrow-counterclockwise"></i> Pulihkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteBackup{{ $backup->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.backups.destroy', $backup) }}">
                        @csrf
                        @method('delete')
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Hapus Arsip Backup</h2>
                            <button class="btn-close" type="button" data-ui-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p>Arsip <strong>{{ $backup->filename }}</strong> akan dihapus permanen.</p>
                            <label class="form-label" for="delete-password-{{ $backup->id }}">Kata Sandi saat ini</label>
                            <input class="form-control" id="delete-password-{{ $backup->id }}" name="current_password" type="password" autocomplete="current-password" required>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" type="button" data-ui-dismiss="modal">Batal</button>
                            <button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
