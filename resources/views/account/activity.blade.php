@extends('layouts.app')

@section('title', 'Aktivitas Saya - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <h1 class="h3">Aktivitas Saya</h1>
            <p class="text-muted mb-0">Riwayat dokumen yang pernah Anda unduh.</p>
        </div>

        <div class="item-card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Jenis</th>
                        <th>Waktu Unduh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($downloads as $download)
                        <tr>
                            <td>{{ $download->document?->title ?? 'Dokumen tidak lagi tersedia' }}</td>
                            <td>{{ $download->document?->type?->name ?? '-' }}</td>
                            <td>{{ $download->downloaded_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                @if($download->document && $availableDocumentIds->contains($download->document_id))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.show', $download->document) }}"><i class="bi bi-eye"></i> Detail</a>
                                @else
                                    <span class="text-muted small">Tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Belum ada riwayat unduhan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $downloads->links() }}</div>
        </div>
    </div>
</section>
@endsection
