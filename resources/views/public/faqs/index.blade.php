@extends('layouts.app')

@section('title', 'FAQ Hukum - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="h3">FAQ Hukum</h1>
        <p class="text-muted">Pertanyaan umum seputar penggunaan portal dan informasi hukum dasar.</p>
        <div class="accordion" id="faqList">
            @forelse($faqs as $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-ui-toggle="collapse" data-ui-target="#faq{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="faq{{ $faq->id }}" class="accordion-collapse">
                        <div class="accordion-body">
                            <div class="small text-muted mb-2">{{ $faq->category }}</div>
                            {{ $faq->answer }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">Belum ada FAQ.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
