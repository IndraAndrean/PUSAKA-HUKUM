@extends('layouts.admin')

@section('title', 'Kelola Dokumen')
@section('page_title', 'Kelola Dokumen')

@section('page_actions')
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('admin.document-imports.create') }}"><i class="bi bi-file-earmark-arrow-up"></i> Import</a>
        <a class="btn btn-primary" href="{{ route('admin.documents.create') }}"><i class="bi bi-plus-lg"></i> Tambah Dokumen</a>
    </div>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-7">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari kode, judul, penulis, ISBN, atau nomor">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="collection">
                <option value="">Semua koleksi</option>
                @foreach($collections as $value => $label)
                    <option value="{{ $value }}" @selected(request('collection') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Judul</th>
                <th>Jenis</th>
                <th>Kualitas Metadata</th>
                <th>Akses</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($documents as $document)
                <tr>
                    <td class="fw-semibold">{{ $document->document_code }}</td>
                    <td>
                        <div>{{ $document->title }}</div>
                        <small class="text-muted">{{ $document->category?->name ?? '-' }} | {{ $document->year ?? '-' }}</small>
                    </td>
                    <td>{{ $document->type?->name }}</td>
                    <td>
                        <span class="badge {{ $document->metadata_completeness >= 95 ? 'text-bg-success' : 'text-bg-warning' }}">
                            {{ $document->metadata_completeness }}%
                        </span>
                        @if($document->needs_review)
                            <span class="badge text-bg-danger">Perlu review</span>
                        @elseif($document->next_review_at)
                            <div class="small text-muted mt-1">Review {{ $document->next_review_at->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td><span class="badge text-bg-light">{{ ucfirst($document->access_level) }}</span></td>
                    <td>{{ str_replace('_', ' ', ucfirst($document->document_status)) }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.show', $document) }}" target="_blank"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.documents.edit', $document) }}"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.documents.destroy', $document) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-muted">Belum ada dokumen.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $documents->links() }}
</div>
@endsection
