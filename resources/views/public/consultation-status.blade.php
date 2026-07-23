@extends('layouts.app')

@section('title', 'Cek Status Konsultasi - SIPAKEM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item"><a href="{{ route('consultation.create') }}">Konsultasi</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Cek Status</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="item-card p-4 mb-4">
                    <h1 class="h4">Cek Status Konsultasi</h1>
                    <p class="text-muted small">Masukkan kode pelacakan yang Anda terima saat mengirim pertanyaan konsultasi.</p>
                    <form method="get" action="{{ route('consultation.status') }}" class="search-toolbar search-toolbar-compact">
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text"><i data-lucide="search"></i></span>
                            <input class="form-control font-monospace" style="text-transform: uppercase;" name="tracking_code" value="{{ request('tracking_code') }}" placeholder="Contoh: KH-7X9K2M" required>
                        </div>
                        <button class="btn btn-pusaka" type="submit"><i data-lucide="search-check"></i> Cek</button>
                    </form>
                </div>

                @if($searched)
                    @if($consultation)
                        @php
                            $statusMap = [
                                'masuk' => ['label' => 'Menunggu diproses', 'class' => 'text-bg-light'],
                                'diproses' => ['label' => 'Sedang diproses', 'class' => 'text-bg-warning'],
                                'dijawab' => ['label' => 'Sudah dijawab', 'class' => 'text-bg-success'],
                                'selesai' => ['label' => 'Selesai', 'class' => 'text-bg-success'],
                            ];
                            $statusInfo = $statusMap[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'text-bg-light'];
                        @endphp
                        <div class="item-card p-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <span class="font-monospace small text-muted">{{ $consultation->tracking_code }}</span>
                                <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </div>
                            <div class="small text-muted mb-1">Pertanyaan Anda</div>
                            <p class="mb-3">{{ $consultation->question }}</p>
                            <div class="small text-muted mb-1">Dikirim</div>
                            <p class="mb-3">{{ $consultation->created_at->translatedFormat('d F Y, H:i') }}</p>

                            @if(filled($consultation->answer))
                                <hr class="my-3">
                                <div class="small text-muted mb-1">Jawaban Pengelola</div>
                                <p class="mb-1" style="white-space: pre-line;">{{ $consultation->answer }}</p>
                                @if($consultation->answered_at)
                                    <div class="small text-muted">Dijawab {{ $consultation->answered_at->translatedFormat('d F Y, H:i') }}</div>
                                @endif
                            @else
                                <div class="alert alert-info small mb-0">Pertanyaan Anda belum dijawab. Silakan cek kembali nanti menggunakan kode yang sama.</div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state">
                            <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                            <h2 class="h5 mb-0">Kode tidak ditemukan</h2>
                            <p class="text-muted mb-0">Periksa kembali kode pelacakan Anda, atau <a href="{{ route('consultation.create') }}">kirim pertanyaan baru</a>.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
