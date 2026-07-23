@extends('layouts.admin')

@section('title', 'Pilih Jenis Input Dokumen')
@section('page_title', 'Pilih Jenis Input Dokumen')

@section('content')
@php
    $cards = [
        'produk_hukum' => [
            'icon' => 'bi-file-earmark-text',
            'title' => 'Bank Produk Hukum',
            'description' => 'Untuk peraturan, surat edaran, juklak/juknis, keputusan, dan dokumen resmi lain yang memiliki nomor serta tanggal penetapan.',
            'examples' => 'Contoh: Peraturan Kapolda, Keputusan Kabidkum, Surat Edaran, Petunjuk Pelaksanaan.',
        ],
        'perpustakaan' => [
            'icon' => 'bi-book',
            'title' => 'Perpustakaan Digital',
            'description' => 'Untuk referensi hukum seperti buku, jurnal, kajian, atau bahan pustaka yang memakai metadata penulis, penerbit, ISBN/ISSN, dan edisi.',
            'examples' => 'Contoh: Buku Hukum, Jurnal Hukum, Kajian Hukum.',
        ],
        'edukasi' => [
            'icon' => 'bi-mortarboard',
            'title' => 'Materi Edukasi',
            'description' => 'Untuk bahan penyuluhan, modul, paparan, atau materi pembelajaran hukum yang tidak selalu memiliki nomor dan tanggal regulasi.',
            'examples' => 'Contoh: Materi Penyuluhan Hukum, modul edukasi, bahan sosialisasi.',
        ],
    ];
@endphp

<div class="row g-3">
    @foreach($cards as $collection => $card)
        <div class="col-lg-4">
            <a class="content-card d-block h-100 p-4 text-decoration-none text-body" href="{{ route('admin.documents.create', ['collection' => $collection]) }}">
                <div class="d-flex align-items-start gap-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary text-white" style="width: 46px; height: 46px;">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </span>
                    <div>
                        <h2 class="h5 mb-2">{{ $card['title'] }}</h2>
                        <p class="text-muted mb-3">{{ $card['description'] }}</p>
                        <p class="small text-muted mb-0">{{ $card['examples'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <span class="btn btn-primary">Pilih <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
