@extends('layouts.admin')

@section('title', 'Kelola Artikel')
@section('page_title', 'Kelola Artikel')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.articles.create') }}"><i class="bi bi-plus-lg"></i> Tambah Artikel</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul atau kategori artikel">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Penulis</th>
                <th>Tanggal</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($articles as $article)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $article->title }}</div>
                        <small class="text-muted">{{ str($article->excerpt)->limit(70) }}</small>
                    </td>
                    <td>{{ $article->category ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $article->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $article->status === 'published' ? 'Terbit' : 'Draft' }}
                        </span>
                    </td>
                    <td>{{ $article->author?->name ?? '-' }}</td>
                    <td>{{ $article->published_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-end">
                        @if($article->status === 'published')
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('articles.show', $article->slug) }}" target="_blank" title="Lihat"><i class="bi bi-eye"></i></a>
                        @endif
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.articles.edit', $article) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">Belum ada artikel.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $articles->links() }}
</div>
@endsection
