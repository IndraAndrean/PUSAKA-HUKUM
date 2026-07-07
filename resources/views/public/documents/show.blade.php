@extends('layouts.app')

@section('title', $document->title.' - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <a href="{{ $document->type?->isLibrary() ? route('library.index') : route('documents.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="item-card p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge text-bg-secondary">{{ $document->type?->name }}</span>
                        <span class="badge badge-access">{{ ucfirst($document->access_level) }}</span>
                        <span class="badge text-bg-light">{{ str_replace('_', ' ', ucfirst($document->document_status)) }}</span>
                    </div>
                    <h1 class="h3">{{ $document->title }}</h1>
                    <p class="text-muted">{{ $document->summary ?: 'Ringkasan dokumen belum diisi.' }}</p>

                    <h2 class="h5 mt-4">Metadata Dokumen</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <tbody>
                            <tr><th style="width: 220px;">ID Dokumen</th><td>{{ $document->document_code }}</td></tr>
                            @if($document->type?->isLibrary())
                                <tr><th>Penulis/Penyusun</th><td>{{ $document->author ?? '-' }}</td></tr>
                                <tr><th>Penerbit</th><td>{{ $document->publisher ?? '-' }}</td></tr>
                                <tr><th>ISBN/ISSN</th><td>{{ $document->isbn_issn ?? '-' }}</td></tr>
                                <tr><th>Edisi/Volume</th><td>{{ $document->edition_volume ?? '-' }}</td></tr>
                            @endif
                            <tr><th>Nomor Dokumen</th><td>{{ $document->document_number ?? '-' }}</td></tr>
                            <tr><th>Tahun</th><td>{{ $document->year ?? '-' }}</td></tr>
                            <tr><th>Tanggal Penetapan</th><td>{{ $document->enacted_date?->format('d/m/Y') ?? '-' }}</td></tr>
                            <tr><th>Tanggal Berlaku</th><td>{{ $document->effective_date?->format('d/m/Y') ?? '-' }}</td></tr>
                            <tr><th>Instansi Penerbit</th><td>{{ $document->issuing_institution ?? '-' }}</td></tr>
                            <tr><th>Kategori Hukum</th><td>{{ $document->category?->name ?? '-' }}</td></tr>
                            <tr><th>Bidang/Subbidang</th><td>{{ $document->bidang_subbidang ? ucfirst($document->bidang_subbidang) : '-' }}</td></tr>
                            <tr><th>Kata Kunci</th><td>{{ $document->keywords ?? '-' }}</td></tr>
                            <tr><th>Versi Dokumen</th><td>{{ $document->document_version ?? '-' }}</td></tr>
                            <tr><th>Review Berikutnya</th><td>{{ $document->next_review_at?->format('d/m/Y') ?? 'Tidak periodik' }}</td></tr>
                            <tr><th>Pengunggah</th><td>{{ $document->uploader?->name ?? '-' }}</td></tr>
                            <tr><th>Statistik</th><td>{{ $document->views_count }} dilihat, {{ $document->downloads_count }} diunduh</td></tr>
                            </tbody>
                        </table>
                    </div>

                    @if($document->abstract)
                        <h2 class="h5 mt-4">Abstrak</h2>
                        <p>{{ $document->abstract }}</p>
                    @endif

                    <h2 class="h5 mt-4">Preview Dokumen</h2>
                    @if($hasFile)
                        <div class="border rounded-2 overflow-hidden bg-light" style="height: min(75vh, 760px); min-height: 480px;">
                            <iframe
                                src="{{ route('documents.preview', $document) }}"
                                title="Preview {{ $document->title }}"
                                style="width: 100%; height: 100%; border: 0;"
                            ></iframe>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">File PDF belum tersedia atau tidak ditemukan pada penyimpanan.</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="item-card p-4">
                    <h2 class="h5">Aksi Dokumen</h2>
                    <p class="small text-muted">Unduh file PDF jika tersedia dan sesuai hak akses.</p>
                    @if($hasFile)
                        <a class="btn btn-outline-secondary w-100 mb-2" href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"><i class="bi bi-arrows-fullscreen"></i> Buka Layar Penuh</a>
                        <a class="btn btn-pusaka w-100" href="{{ route('documents.download', $document) }}"><i class="bi bi-download"></i> Unduh PDF</a>
                    @else
                        <div class="alert alert-warning small mt-3 mb-0">File PDF belum diunggah oleh admin.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
