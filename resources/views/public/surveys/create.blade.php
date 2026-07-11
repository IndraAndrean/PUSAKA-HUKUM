@extends('layouts.app')

@section('title', 'Survei Kepuasan')

@section('content')
<section class="section-band py-5">
    <div class="container" style="max-width: 920px">
        <nav class="breadcrumb">
            <span class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right"></i></span>
            <span class="breadcrumb-item active">Survei Kepuasan</span>
        </nav>
        <div class="mb-4">
            <span class="text-uppercase small fw-semibold text-success">Evaluasi Layanan</span>
            <h1 class="h2 mt-2">Survei Kepuasan PUSAKA HUKUM</h1>
            <p class="text-muted mb-0">Penilaian ini digunakan untuk mengukur kemudahan akses, kecepatan pencarian, kualitas isi, dan manfaat portal. Survei dapat diisi satu kali setiap bulan.</p>
        </div>

        @if($alreadySubmitted)
            <div class="item-card p-4 text-center">
                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                <h2 class="h4 mt-3">Survei bulan ini sudah diisi</h2>
                <p class="text-muted">Terima kasih telah membantu evaluasi dan pengembangan PUSAKA HUKUM.</p>
                <a class="btn btn-pusaka" href="{{ route('home') }}"><i class="bi bi-house me-1"></i> Kembali ke Beranda</a>
            </div>
        @else
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Periksa kembali jawaban Anda.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="item-card p-4" method="post" action="{{ route('surveys.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="respondent_type">Kategori responden</label>
                    <select class="form-select" id="respondent_type" name="respondent_type" required>
                        <option value="">Pilih kategori</option>
                        @foreach($respondentTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('respondent_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $ratingQuestions = [
                        'accessibility_rating' => 'Kemudahan mengakses portal',
                        'speed_rating' => 'Kecepatan portal dan pencarian',
                        'content_rating' => 'Kelengkapan dan relevansi informasi',
                        'ease_rating' => 'Kemudahan menggunakan menu dan fitur',
                        'overall_rating' => 'Kepuasan secara keseluruhan',
                    ];
                @endphp

                <div class="mb-4">
                    <div class="fw-semibold mb-1">Berikan penilaian</div>
                    <div class="small text-muted mb-3">1 berarti sangat tidak puas dan 5 berarti sangat puas.</div>
                    @foreach($ratingQuestions as $field => $question)
                        <fieldset class="border-top py-3">
                            <legend class="fs-6 mb-2">{{ $question }}</legend>
                            <div class="rating-scale">
                                @for($rating = 1; $rating <= 5; $rating++)
                                    <input class="rating-scale-input" type="radio" name="{{ $field }}" id="{{ $field }}_{{ $rating }}" value="{{ $rating }}" @checked((int) old($field) === $rating) required>
                                    <label class="rating-scale-item" for="{{ $field }}_{{ $rating }}">{{ $rating }}</label>
                                @endfor
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1" style="max-width: 260px;">
                                <span>Sangat tidak puas</span>
                                <span>Sangat puas</span>
                            </div>
                        </fieldset>
                    @endforeach
                </div>

                <div class="row g-3 mb-4">
                    <fieldset class="col-md-6">
                        <legend class="form-label fw-semibold fs-6">Apakah dokumen yang dicari ditemukan?</legend>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="found_document" id="found_yes" value="1" @checked(old('found_document') === '1') required>
                                <label class="form-check-label" for="found_yes">Ya</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="found_document" id="found_no" value="0" @checked(old('found_document') === '0') required>
                                <label class="form-check-label" for="found_no">Tidak</label>
                            </div>
                        </div>
                    </fieldset>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="search_duration_minutes">Perkiraan waktu pencarian (menit)</label>
                        <input class="form-control" type="number" min="0.1" max="120" step="0.1" id="search_duration_minutes" name="search_duration_minutes" value="{{ old('search_duration_minutes') }}" placeholder="Contoh: 2.5">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="most_useful_feature">Fitur yang paling bermanfaat</label>
                    <select class="form-select" id="most_useful_feature" name="most_useful_feature" required>
                        <option value="">Pilih fitur</option>
                        @foreach($features as $value => $label)
                            <option value="{{ $value }}" @selected(old('most_useful_feature') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="feedback">Saran atau masukan <span class="text-muted fw-normal">(opsional)</span></label>
                    <textarea class="form-control" id="feedback" name="feedback" rows="4" maxlength="2000" placeholder="Tuliskan hal yang perlu dipertahankan atau diperbaiki">{{ old('feedback') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-pusaka" type="submit"><i class="bi bi-send me-1"></i> Kirim Penilaian</button>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection
