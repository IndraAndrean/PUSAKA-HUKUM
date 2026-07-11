@extends('layouts.app')

@section('title', $document->title.' - PUSAKA HUKUM')

@section('content')
@php
    $isLibrary = $document->type?->isLibrary();
    $isEducation = $document->type?->isEducation();
    $listRoute = match(true) {
        $isEducation => route('education-materials.index'),
        $isLibrary => route('library.index'),
        default => route('documents.index'),
    };
    $listLabel = match(true) {
        $isEducation => 'Materi Penyuluhan Hukum',
        $isLibrary => 'Perpustakaan Digital',
        default => 'Bank Produk Hukum',
    };
@endphp
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item"><a href="{{ $listRoute }}">{{ $listLabel }}</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">{{ $document->title }}</span>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="item-card p-4">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge text-bg-secondary">{{ $document->type?->name }}</span>
                        @include('layouts.partials.status-badge', ['document' => $document])
                        @include('layouts.partials.access-badge', ['document' => $document])
                    </div>
                    <h1 class="h3 mb-1">{{ $document->title }}</h1>
                    <div class="small text-muted mb-3">Kode Dokumen: <span class="font-monospace">{{ $document->document_code }}</span></div>
                    <p class="text-muted mb-0">{{ $document->summary ?: 'Ringkasan dokumen belum diisi.' }}</p>

                    <h2 class="h5 mt-4 mb-3">Metadata Dokumen</h2>
                    <div class="metadata-grid">
                        <div>
                            <div class="metadata-item-label"><i data-lucide="hash"></i> ID Dokumen</div>
                            <div class="metadata-item-value font-monospace">{{ $document->document_code }}</div>
                        </div>
                        @if($isLibrary)
                            <div>
                                <div class="metadata-item-label"><i data-lucide="user"></i> Penulis/Penyusun</div>
                                <div class="metadata-item-value">{{ $document->author ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="metadata-item-label"><i data-lucide="building-2"></i> Penerbit</div>
                                <div class="metadata-item-value">{{ $document->publisher ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="metadata-item-label"><i data-lucide="book"></i> ISBN/ISSN</div>
                                <div class="metadata-item-value">{{ $document->isbn_issn ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="metadata-item-label"><i data-lucide="files"></i> Edisi/Volume</div>
                                <div class="metadata-item-value">{{ $document->edition_volume ?? '-' }}</div>
                            </div>
                        @endif
                        <div>
                            <div class="metadata-item-label"><i data-lucide="hash"></i> Nomor Dokumen</div>
                            <div class="metadata-item-value">{{ $document->document_number ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="calendar"></i> Tahun</div>
                            <div class="metadata-item-value">{{ $document->year ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="calendar"></i> Tanggal Penetapan</div>
                            <div class="metadata-item-value">{{ $document->enacted_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="calendar"></i> Tanggal Berlaku</div>
                            <div class="metadata-item-value">{{ $document->effective_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="building-2"></i> Instansi Penerbit</div>
                            <div class="metadata-item-value">{{ $document->issuing_institution ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="tags"></i> Kategori Hukum</div>
                            <div class="metadata-item-value">{{ $document->category?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="folder-open"></i> Bidang/Subbidang</div>
                            <div class="metadata-item-value">{{ $document->bidang_subbidang ? ucfirst($document->bidang_subbidang) : '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="history"></i> Versi Dokumen</div>
                            <div class="metadata-item-value">{{ $document->document_version ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="refresh-cw"></i> Review Berikutnya</div>
                            <div class="metadata-item-value">{{ $document->next_review_at?->format('d/m/Y') ?? 'Tidak periodik' }}</div>
                        </div>
                        <div>
                            <div class="metadata-item-label"><i data-lucide="user"></i> Pengunggah</div>
                            <div class="metadata-item-value">{{ $document->uploader?->name ?? '-' }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="metadata-item-label"><i data-lucide="tags"></i> Kata Kunci</div>
                            <div class="metadata-item-value">{{ $document->keywords ?? '-' }}</div>
                        </div>
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
                <div class="item-card p-4" style="position: sticky; top: 90px;">
                    <h2 class="h5">Aksi Dokumen</h2>
                    <p class="small text-muted">Unduh file PDF jika tersedia dan sesuai hak akses.</p>
                    @if($hasFile)
                        <a class="btn btn-outline-secondary w-100 mb-2" href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"><i data-lucide="maximize"></i> Buka Layar Penuh</a>
                        <a class="btn btn-pusaka w-100" href="{{ route('documents.download', $document) }}"><i data-lucide="download"></i> Unduh PDF</a>
                    @else
                        <div class="alert alert-warning small mt-3 mb-0">File PDF belum diunggah oleh admin.</div>
                    @endif

                    <hr class="my-4 border-pusaka-line">

                    <h2 class="h6">Info Cepat</h2>
                    <div class="d-flex flex-column gap-2 small text-muted">
                        <div class="d-flex align-items-center gap-2"><i data-lucide="eye"></i> {{ $document->views_count }} kali dilihat</div>
                        <div class="d-flex align-items-center gap-2"><i data-lucide="download"></i> {{ $document->downloads_count }} kali diunduh</div>
                        <div class="d-flex align-items-center gap-2"><i data-lucide="calendar"></i> Diunggah {{ $document->created_at?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($relatedDocuments->isNotEmpty())
            <div class="mt-5">
                <h2 class="h5 mb-3">Dokumen Terkait</h2>
                <div class="related-docs">
                    @foreach($relatedDocuments as $related)
                        @include('public.documents._document-card', ['document' => $related])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
