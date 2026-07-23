@extends('layouts.admin')

@section('title', 'Bidang/Subbidang')
@section('page_title', 'Bidang/Subbidang')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.document-divisions.create') }}"><i class="bi bi-plus-lg"></i> Tambah Bidang</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="search-toolbar mb-3">
        <div class="input-group flex-grow-1">
            <span class="input-group-text"><i data-lucide="search"></i></span>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari bidang/subbidang">
        </div>
        <button class="btn btn-outline-secondary" type="submit"><i data-lucide="search"></i> Cari</button>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama</th><th>Kode</th><th>Deskripsi</th><th class="text-center">Dokumen</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($documentDivisions as $documentDivision)
                <tr>
                    <td class="fw-semibold">{{ $documentDivision->name }}</td>
                    <td><code>{{ $documentDivision->code }}</code></td>
                    <td>{{ filled($documentDivision->description) ? str($documentDivision->description)->limit(80) : '-' }}</td>
                    <td class="text-center"><span class="badge text-bg-light">{{ $documentDivision->documents_count }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.document-divisions.edit', $documentDivision) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="{{ route('admin.document-divisions.destroy', $documentDivision) }}" data-confirm="Bidang/subbidang ini akan dihapus jika belum digunakan oleh dokumen lain." data-confirm-title="Hapus Bidang/Subbidang" data-confirm-label="Ya, Hapus" data-confirm-variant="danger">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus" @disabled($documentDivision->documents_count > 0)><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">Belum ada bidang/subbidang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $documentDivisions->links() }}
</div>
@endsection
