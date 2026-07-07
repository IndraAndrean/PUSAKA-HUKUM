@extends('layouts.admin')

@section('title', 'Detail Audit')
@section('page_title', 'Detail Audit Aktivitas')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.audit-logs.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
@php
    $fieldLabels = [
        'document_code' => 'Kode Dokumen',
        'title' => 'Judul',
        'name' => 'Nama',
        'email' => 'Email',
        'role' => 'Peran',
        'is_active' => 'Status Aktif',
        'question' => 'Pertanyaan',
        'answer' => 'Jawaban',
        'status' => 'Status',
        'document_status' => 'Status Dokumen',
        'access_level' => 'Level Akses',
        'file_path' => 'Lokasi File',
        'spreadsheet_name' => 'Nama Spreadsheet',
        'pdf_archive_name' => 'Nama ZIP PDF',
        'total_rows' => 'Total Baris',
        'successful_rows' => 'Baris Berhasil',
        'failed_rows' => 'Baris Gagal',
    ];
    $oldValues = $log->old_values ?? [];
    $newValues = $log->new_values ?? [];
    $fields = collect(array_keys($oldValues))->merge(array_keys($newValues))->unique();
    $formatValue = function ($value) {
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if ($value === null || $value === '') {
            return '-';
        }
        return (string) $value;
    };
@endphp

<div class="content-card p-4 mb-4">
    <div class="row g-3">
        <div class="col-md-6 col-xl-3">
            <span class="text-muted small d-block">Waktu</span>
            <span class="fw-semibold">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="col-md-6 col-xl-3">
            <span class="text-muted small d-block">Pelaku</span>
            <span class="fw-semibold">{{ $log->user?->name ?? 'Akun telah dihapus' }}</span>
            <small class="d-block text-muted">{{ $log->user?->email ?? '-' }}</small>
        </div>
        <div class="col-md-6 col-xl-3">
            <span class="text-muted small d-block">Aksi dan Modul</span>
            <span class="fw-semibold">{{ $actions[$log->action] ?? ucfirst($log->action) }} · {{ $log->module }}</span>
        </div>
        <div class="col-md-6 col-xl-3">
            <span class="text-muted small d-block">Alamat IP</span>
            <span class="fw-semibold">{{ $log->ip_address ?: '-' }}</span>
        </div>
        <div class="col-12">
            <span class="text-muted small d-block">Keterangan</span>
            <span>{{ $log->description }}</span>
        </div>
    </div>
</div>

<div class="content-card p-3 mb-4">
    <h2 class="h5 px-2 pt-2 mb-3">Perubahan Data</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th style="width: 20%">Field</th>
                <th style="width: 40%">Sebelum</th>
                <th style="width: 40%">Sesudah</th>
            </tr>
            </thead>
            <tbody>
            @forelse($fields as $field)
                <tr>
                    <td class="fw-semibold">{{ $fieldLabels[$field] ?? str($field)->replace('_', ' ')->title() }}</td>
                    <td class="text-break"><pre class="mb-0 text-wrap font-monospace small">{{ $formatValue($oldValues[$field] ?? null) }}</pre></td>
                    <td class="text-break"><pre class="mb-0 text-wrap font-monospace small">{{ $formatValue($newValues[$field] ?? null) }}</pre></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada nilai data yang disimpan untuk aktivitas ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="content-card p-4">
    <h2 class="h5 mb-3">Informasi Teknis</h2>
    <dl class="row mb-0 small">
        <dt class="col-md-3">Objek</dt>
        <dd class="col-md-9">{{ $log->subject_label ?: '-' }}</dd>
        <dt class="col-md-3">ID Objek</dt>
        <dd class="col-md-9">{{ $log->subject_id ?: '-' }}</dd>
        <dt class="col-md-3">Browser / Perangkat</dt>
        <dd class="col-md-9 text-break">{{ $log->user_agent ?: '-' }}</dd>
    </dl>
</div>
@endsection
