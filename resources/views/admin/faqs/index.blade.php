@extends('layouts.admin')

@section('title', 'Kelola FAQ')
@section('page_title', 'Kelola FAQ')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.faqs.create') }}"><i class="bi bi-plus-lg"></i> Tambah FAQ</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari pertanyaan atau kategori">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Pertanyaan</th><th>Kategori</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($faqs as $faq)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $faq->question }}</div>
                        <small class="text-muted">{{ str($faq->answer)->limit(90) }}</small>
                    </td>
                    <td>{{ $faq->category ?: '-' }}</td>
                    <td><span class="badge {{ $faq->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $faq->status === 'published' ? 'Terbit' : 'Draft' }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.faqs.edit', $faq) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">Belum ada FAQ.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $faqs->links() }}
</div>
@endsection
