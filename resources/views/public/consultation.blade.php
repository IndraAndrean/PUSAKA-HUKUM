@extends('layouts.app')

@section('title', 'Konsultasi Informasi Hukum - SIPAKEM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Konsultasi</span>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                @if(session('tracking_code'))
                    <div class="item-card p-4 mb-4" style="border-left: 4px solid #14776d;">
                        <div class="d-flex align-items-center gap-2 mb-2 fw-semibold"><i data-lucide="circle-check-big" style="color:#14776d"></i> Pertanyaan terkirim</div>
                        <p class="small text-muted mb-2">Simpan kode ini untuk mengecek status dan jawaban Anda nanti. Kode tidak dikirim ulang lewat email.</p>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="font-monospace fw-bold fs-5" style="letter-spacing: 0.05em;">{{ session('tracking_code') }}</span>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('consultation.status', ['tracking_code' => session('tracking_code')]) }}"><i data-lucide="search"></i> Cek Status Sekarang</a>
                        </div>
                    </div>
                @endif
                <div class="item-card p-4">
                    <h1 class="h3">Konsultasi Informasi Hukum</h1>
                    <p class="text-muted mb-2">Kirim pertanyaan atau kebutuhan rujukan dokumen hukum kepada pengelola.</p>
                    <p class="small text-muted">Konsultasi dijawab oleh pengelola SIPAKEM sesuai ketersediaan waktu &mdash; waktu respons dapat bervariasi. Setelah mengirim, Anda akan menerima kode pelacakan untuk mengecek status jawaban kapan saja di <a href="{{ route('consultation.status') }}">halaman cek status</a>.</p>
                    <form method="post" action="{{ route('consultation.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nama</label>
                                <input class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="question">Pertanyaan</label>
                                <textarea class="form-control" id="question" name="question" rows="6" required>{{ old('question') }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-pusaka" type="submit"><i data-lucide="send"></i> Kirim Pertanyaan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="item-card p-4 mb-4">
                    <h2 class="h6 d-flex align-items-center gap-2"><i data-lucide="circle-help"></i> Sudah cek FAQ?</h2>
                    <p class="small text-muted">Sebagian besar pertanyaan umum seputar produk hukum dan penggunaan portal sudah terjawab di FAQ Hukum.</p>
                    <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('faqs.index') }}"><i data-lucide="circle-help"></i> Buka FAQ Hukum</a>
                </div>
                <div class="item-card p-4">
                    <h2 class="h6 d-flex align-items-center gap-2"><i data-lucide="search"></i> Sudah pernah kirim pertanyaan?</h2>
                    <p class="small text-muted">Cek status dan jawaban konsultasi Anda menggunakan kode pelacakan yang diberikan saat pengiriman.</p>
                    <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('consultation.status') }}"><i data-lucide="search"></i> Cek Status Konsultasi</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
