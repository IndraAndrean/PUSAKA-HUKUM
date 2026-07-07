@extends('layouts.app')

@section('title', 'Konsultasi Informasi Hukum - PUSAKA HUKUM')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="item-card p-4">
                    <h1 class="h3">Konsultasi Informasi Hukum</h1>
                    <p class="text-muted">Kirim pertanyaan atau kebutuhan rujukan dokumen hukum kepada pengelola.</p>
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
                                <button class="btn btn-pusaka" type="submit"><i class="bi bi-send"></i> Kirim Pertanyaan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
