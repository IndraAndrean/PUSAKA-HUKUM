@extends('layouts.admin')

@section('title', 'Kelola Dokumen')
@section('page_title', 'Kelola Dokumen')

@section('page_actions')
    <div class="d-flex gap-2">
        <a class="btn btn-primary" href="{{ route('admin.documents.create') }}"><i class="bi bi-plus-lg"></i> Tambah Dokumen</a>
    </div>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="search-toolbar mb-3">
        <div class="input-group flex-grow-1">
            <span class="input-group-text"><i data-lucide="search"></i></span>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari kode, judul, jenis, kategori, status, penulis, ISBN, atau nomor">
        </div>
        <select class="form-select" name="collection" aria-label="Filter koleksi dokumen">
            <option value="">Semua koleksi</option>
            @foreach($collections as $value => $label)
                <option value="{{ $value }}" @selected(request('collection') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary" type="submit"><i data-lucide="search"></i> Cari</button>
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
                <th class="text-end admin-table-actions-column">Aksi</th>
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
                    <td class="admin-table-actions-column">
                        <div class="admin-table-actions">
                            <a class="btn btn-sm btn-outline-secondary btn-icon" href="{{ route('documents.show', $document) }}" target="_blank" title="Lihat dokumen" aria-label="Lihat dokumen"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-sm btn-outline-primary btn-icon" href="{{ route('admin.documents.edit', $document) }}" title="Edit dokumen" aria-label="Edit dokumen"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.documents.destroy', $document) }}" method="post" data-confirm="Dokumen ini akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Dokumen" data-confirm-label="Ya, Hapus" data-confirm-variant="danger">
                                @csrf
                                @method('delete')
                                <button class="btn btn-sm btn-outline-danger btn-icon" type="submit" title="Hapus dokumen" aria-label="Hapus dokumen"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
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
