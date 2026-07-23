@extends('layouts.admin')

@section('title', 'Jenis Dokumen')
@section('page_title', 'Jenis Dokumen')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.document-types.create') }}"><i class="bi bi-plus-lg"></i> Tambah Jenis</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="search-toolbar mb-3">
        <div class="input-group flex-grow-1">
            <span class="input-group-text"><i data-lucide="search"></i></span>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari jenis dokumen">
        </div>
        <button class="btn btn-outline-secondary" type="submit"><i data-lucide="search"></i> Cari</button>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama</th><th>Prefix</th><th>Review</th><th>Deskripsi</th><th class="text-center">Dokumen</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($documentTypes as $documentType)
                <tr>
                    <td class="fw-semibold">{{ $documentType->name }}</td>
                    <td><code>{{ $documentType->code_prefix ?: '-' }}</code></td>
                    <td>{{ $documentType->review_interval_months ? $documentType->review_interval_months.' bulan' : 'Setiap dokumen baru' }}</td>
                    <td>{{ filled($documentType->description) ? str($documentType->description)->limit(80) : '-' }}</td>
                    <td class="text-center"><span class="badge text-bg-light">{{ $documentType->documents_count }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.document-types.edit', $documentType) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="{{ route('admin.document-types.destroy', $documentType) }}" data-confirm="Jenis dokumen ini akan dihapus jika belum digunakan oleh dokumen lain." data-confirm-title="Hapus Jenis Dokumen" data-confirm-label="Ya, Hapus" data-confirm-variant="danger">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus" @disabled($documentType->documents_count > 0)><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">Belum ada jenis dokumen.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $documentTypes->links() }}
</div>
@endsection
