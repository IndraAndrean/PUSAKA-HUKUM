@extends('layouts.app')

@section('title', 'Konsultasi Saya - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Konsultasi Saya</span>
        </nav>
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h3">Konsultasi Saya</h1>
                <p class="text-muted mb-0">Riwayat pertanyaan konsultasi yang pernah Anda kirim beserta status dan jawabannya.</p>
            </div>
            <a class="btn btn-pusaka btn-sm" href="{{ route('consultation.create') }}"><i data-lucide="send"></i> Kirim Pertanyaan Baru</a>
        </div>

        @php
            $statusMap = [
                'masuk' => ['label' => 'Menunggu diproses', 'class' => 'text-bg-light'],
                'diproses' => ['label' => 'Sedang diproses', 'class' => 'text-bg-warning'],
                'dijawab' => ['label' => 'Sudah dijawab', 'class' => 'text-bg-success'],
                'selesai' => ['label' => 'Selesai', 'class' => 'text-bg-success'],
            ];
        @endphp

        <div class="row g-3">
            @forelse($consultations as $consultation)
                @php $statusInfo = $statusMap[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'text-bg-light']; @endphp
                <div class="col-12">
                    <div class="item-card p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <span class="font-monospace small text-muted">{{ $consultation->tracking_code }}</span>
                            <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                        </div>
                        <p class="mb-2">{{ str($consultation->question)->limit(180) }}</p>
                        <div class="small text-muted">Dikirim {{ $consultation->created_at->translatedFormat('d F Y, H:i') }}</div>
                        @if(filled($consultation->answer))
                            <hr class="my-3">
                            <div class="small text-muted mb-1">Jawaban Pengelola</div>
                            <p class="mb-0" style="white-space: pre-line;">{{ $consultation->answer }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info mb-0">Anda belum pernah mengirim pertanyaan konsultasi.</div></div>
            @endforelse
        </div>

        <div class="mt-4">{{ $consultations->links() }}</div>
    </div>
</section>
@endsection
