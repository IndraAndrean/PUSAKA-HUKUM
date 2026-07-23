@extends('layouts.admin')

@section('title', 'Audit Aktivitas')
@section('page_title', 'Audit Aktivitas')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Aktivitas Hari Ini</div>
            <div class="fs-3 fw-semibold">{{ $summary['today'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Tujuh Hari Terakhir</div>
            <div class="fs-3 fw-semibold">{{ $summary['last_seven_days'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="metric p-3 h-100">
            <div class="text-muted">Penghapusan 30 Hari</div>
            <div class="fs-3 fw-semibold text-danger">{{ $summary['deletions'] }}</div>
        </div>
    </div>
</div>

<div class="content-card p-3 mb-4">
    <form method="get" class="row g-2">
        <div class="col-lg-4">
            <label class="form-label small" for="q">Pencarian</label>
            <input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Objek, deskripsi, atau nama admin">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small" for="action">Aksi</label>
            <select class="form-select" id="action" name="action">
                <option value="">Semua aksi</option>
                @foreach($actions as $value => $label)
                    <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small" for="module">Modul</label>
            <select class="form-select" id="module" name="module">
                <option value="">Semua modul</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small" for="user_id">Pelaku</label>
            <select class="form-select" id="user_id" name="user_id">
                <option value="">Semua admin</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2 d-grid align-self-end">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Terapkan</button>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small" for="date_from">Dari Tanggal</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small" for="date_to">Sampai Tanggal</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
        </div>
        @if(request()->hasAny(['q', 'action', 'module', 'user_id', 'date_from', 'date_to']))
            <div class="col-sm-6 col-lg-2 d-grid align-self-end">
                <a class="btn btn-outline-secondary" href="{{ route('admin.audit-logs.index') }}"><i class="bi bi-x-lg"></i> Reset</a>
            </div>
        @endif
    </form>
</div>

<div class="content-card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Waktu</th>
                <th>Pelaku</th>
                <th>Aksi</th>
                <th>Modul dan Objek</th>
                <th>Alamat IP</th>
                <th class="text-end">Detail</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                @php
                    $badge = match($log->action) {
                        'created' => 'text-bg-success',
                        'updated' => 'text-bg-primary',
                        'deleted' => 'text-bg-danger',
                        'imported' => 'text-bg-info',
                        default => 'text-bg-secondary',
                    };
                @endphp
                <tr>
                    <td class="text-nowrap">
                        <div>{{ $log->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        <div>{{ $log->user?->name ?? 'Akun telah dihapus' }}</div>
                        <small class="text-muted">{{ $log->user?->email ?? '-' }}</small>
                    </td>
                    <td><span class="badge {{ $badge }}">{{ $actions[$log->action] ?? ucfirst($log->action) }}</span></td>
                    <td>
                        <div class="fw-semibold">{{ $log->module }}</div>
                        <small class="text-muted">{{ $log->subject_label ?: '-' }}</small>
                    </td>
                    <td class="text-nowrap">{{ $log->ip_address ?: '-' }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.audit-logs.show', $log) }}" title="Lihat detail">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada aktivitas yang sesuai dengan filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="mt-3">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
