@extends('layouts.admin')

@section('title', 'Jenis Dokumen')
@section('page_title', 'Jenis Dokumen')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.document-types.create') }}"><i class="bi bi-plus-lg"></i> Tambah Jenis</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari jenis dokumen">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
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
                        <form class="d-inline" method="post" action="{{ route('admin.document-types.destroy', $documentType) }}" onsubmit="return confirm('Hapus jenis dokumen ini?')">
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
