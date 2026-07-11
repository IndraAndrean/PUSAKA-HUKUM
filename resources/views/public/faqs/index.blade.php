@extends('layouts.app')

@section('title', 'FAQ Hukum - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">FAQ Hukum</span>
        </nav>

        <div class="mb-4">
            <h1 class="h3">FAQ Hukum</h1>
            <p class="text-muted mb-0">Pertanyaan umum seputar penggunaan portal dan informasi hukum dasar.</p>
        </div>

        <form method="get" class="search-toolbar mb-4">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white"><i data-lucide="search"></i></span>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari pertanyaan atau jawaban">
            </div>
            <button class="btn btn-pusaka" type="submit"><i data-lucide="search"></i> Cari</button>
            @if(request()->filled('q'))
                <a class="btn btn-outline-secondary" href="{{ route('faqs.index') }}"><i data-lucide="x"></i> Reset</a>
            @endif
        </form>

        @forelse($faqs as $category => $faqGroup)
            <div class="faq-category-group">
                <div class="faq-category-title"><i data-lucide="circle-help"></i> {{ $category ?: 'Umum' }}</div>
                <div class="accordion" id="faqList{{ Str::slug($category ?: 'umum') }}">
                    @foreach($faqGroup as $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-ui-toggle="collapse" data-ui-target="#faq{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h2>
                            <div id="faq{{ $faq->id }}" class="accordion-collapse">
                                <div class="accordion-body">{{ $faq->answer }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty-state">
                <span class="empty-state-icon"><i data-lucide="search-x"></i></span>
                <h2 class="h5 mb-0">Tidak ada FAQ yang sesuai</h2>
                <p class="text-muted mb-2">Coba ubah kata kunci pencarian.</p>
                @if(request()->filled('q'))
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('faqs.index') }}"><i data-lucide="rotate-ccw"></i> Reset Pencarian</a>
                @endif
            </div>
        @endforelse
    </div>
</section>
@endsection
