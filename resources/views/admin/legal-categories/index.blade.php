@extends('layouts.admin')

@section('title', 'Kategori Hukum')
@section('page_title', 'Kategori Hukum')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.legal-categories.create') }}"><i class="bi bi-plus-lg"></i> Tambah Kategori</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari kategori hukum">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama</th><th>Slug</th><th>Deskripsi</th><th class="text-center">Dokumen</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($legalCategories as $legalCategory)
                <tr>
                    <td class="fw-semibold">{{ $legalCategory->name }}</td>
                    <td><code>{{ $legalCategory->slug }}</code></td>
                    <td>{{ filled($legalCategory->description) ? str($legalCategory->description)->limit(80) : '-' }}</td>
                    <td class="text-center"><span class="badge text-bg-light">{{ $legalCategory->documents_count }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.legal-categories.edit', $legalCategory) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="{{ route('admin.legal-categories.destroy', $legalCategory) }}" onsubmit="return confirm('Hapus kategori hukum ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus" @disabled($legalCategory->documents_count > 0)><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">Belum ada kategori hukum.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $legalCategories->links() }}
</div>
@endsection
