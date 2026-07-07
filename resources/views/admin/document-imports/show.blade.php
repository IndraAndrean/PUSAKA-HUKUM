@extends('layouts.admin')

@section('title', 'Hasil Import Dokumen')
@section('page_title', 'Hasil Import Dokumen')

@section('page_actions')
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.document-imports.create') }}"><i class="bi bi-arrow-repeat"></i> Import Lagi</a>
        <a class="btn btn-primary" href="{{ route('admin.documents.index') }}"><i class="bi bi-file-earmark-text"></i> Daftar Dokumen</a>
    </div>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="metric p-3 h-100">
            <div class="text-muted">Total Baris</div>
            <div class="fs-3 fw-semibold">{{ $batch->total_rows }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric p-3 h-100">
            <div class="text-muted">Berhasil</div>
            <div class="fs-3 fw-semibold text-success">{{ $batch->successful_rows }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric p-3 h-100">
            <div class="text-muted">Gagal</div>
            <div class="fs-3 fw-semibold text-danger">{{ $batch->failed_rows }}</div>
        </div>
    </div>
</div>

<div class="content-card p-3 mb-4">
    <div class="row g-3 small">
        <div class="col-md-4"><span class="text-muted d-block">Spreadsheet</span>{{ $batch->spreadsheet_name }}</div>
        <div class="col-md-4"><span class="text-muted d-block">Arsip PDF</span>{{ $batch->pdf_archive_name }}</div>
        <div class="col-md-4"><span class="text-muted d-block">Diproses oleh</span>{{ $batch->importer?->name ?? '-' }} pada {{ $batch->created_at->format('d/m/Y H:i') }}</div>
    </div>
</div>

<div class="content-card p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Baris</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Kode / Keterangan</th>
            </tr>
            </thead>
            <tbody>
            @foreach($batch->results ?? [] as $result)
                <tr>
                    <td>{{ $result['row'] }}</td>
                    <td>{{ $result['title'] }}</td>
                    <td>
                        @if($result['status'] === 'success')
                            <span class="badge text-bg-success">Berhasil</span>
                        @else
                            <span class="badge text-bg-danger">Gagal</span>
                        @endif
                    </td>
                    <td>
                        @if($result['status'] === 'success')
                            <a href="{{ route('admin.documents.edit', $result['document_id']) }}">{{ $result['document_code'] }}</a>
                        @else
                            <ul class="mb-0 ps-3 text-danger">
                                @foreach($result['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
