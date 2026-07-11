@extends('layouts.app')

@section('title', 'Profil '.$profile->organization_name.' - '.$profile->portal_name)

@section('content')
<section class="hero py-5">
    <div class="container py-4">
        <nav class="breadcrumb mb-3">
            <span class="breadcrumb-item"><a class="text-white-50" href="{{ route('home') }}">Beranda</a></span>
            <span class="breadcrumb-item"><i data-lucide="chevron-right" class="text-white-50"></i></span>
            <span class="breadcrumb-item active text-white">Profil Instansi</span>
        </nav>
        <div class="row align-items-center g-4">
            <div class="col-lg-9">
                <p class="text-warning fw-semibold mb-2">{{ $profile->portal_full_name }}</p>
                <h1 class="display-6 fw-bold mb-3">Profil {{ $profile->organization_name }}</h1>
                <p class="lead mb-0">{{ $profile->tagline }}</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="mb-5">
                    <div class="text-uppercase small fw-semibold text-success mb-2">Tentang Inovasi</div>
                    <h2 class="h3">{{ $profile->portal_name }}</h2>
                    <p class="text-muted">{{ $profile->about }}</p>
                </div>
                <div class="mb-5">
                    <div class="text-uppercase small fw-semibold text-success mb-2">Tugas dan Fungsi</div>
                    <h2 class="h4">{{ $profile->organization_name }}</h2>
                    <p class="text-muted mb-0">{{ $profile->institution_duties }}</p>
                </div>
                <div>
                    <div class="text-uppercase small fw-semibold text-success mb-2">Tujuan Umum</div>
                    <p class="fs-5 mb-0">{{ $profile->general_goal }}</p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="item-card p-4">
                    <h2 class="h5 mb-3">Layanan Terintegrasi</h2>
                    <div class="d-grid gap-3">
                        @foreach($profile->services as $service)
                            <div class="d-flex gap-3">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>{{ $service }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 section-band">
    <div class="container">
        <div class="mb-4">
            <div class="text-uppercase small fw-semibold text-success mb-2">Manfaat</div>
            <h2 class="h3 mb-0">Dibangun untuk organisasi, personel, dan masyarakat</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['Organisasi', 'bi-building', $profile->benefits_organization],
                ['Personel Polri', 'bi-person-badge', $profile->benefits_personnel],
                ['Masyarakat', 'bi-people', $profile->benefits_public],
            ] as [$title, $icon, $benefits])
                <div class="col-lg-4">
                    <div class="item-card p-4 h-100">
                        <i class="bi {{ $icon }} fs-3 text-success"></i>
                        <h3 class="h5 mt-3">{{ $title }}</h3>
                        <ul class="ps-3 mb-0 text-muted">
                            @foreach($benefits as $benefit)
                                <li class="mb-2">{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="text-uppercase small fw-semibold text-success mb-2">Nilai Organisasi</div>
                <h2 class="h3">BerAKHLAK dan Presisi</h2>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($profile->organization_values as $value)
                        <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $value }}</span>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5">
                <div class="item-card p-4 h-100">
                    <h2 class="h5">Informasi Kontak</h2>
                    @if($profile->hasContactInformation())
                        <dl class="row small mb-0 mt-3">
                            @if($profile->address)<dt class="col-4">Alamat</dt><dd class="col-8">{{ $profile->address }}</dd>@endif
                            @if($profile->phone)<dt class="col-4">Telepon</dt><dd class="col-8">{{ $profile->phone }}</dd>@endif
                            @if($profile->email)<dt class="col-4">Email</dt><dd class="col-8"><a href="mailto:{{ $profile->email }}">{{ $profile->email }}</a></dd>@endif
                            @if($profile->website)<dt class="col-4">Website</dt><dd class="col-8"><a href="{{ $profile->website }}" target="_blank" rel="noopener">{{ $profile->website }}</a></dd>@endif
                            @if($profile->office_hours)<dt class="col-4">Layanan</dt><dd class="col-8">{{ $profile->office_hours }}</dd>@endif
                        </dl>
                    @else
                        <p class="text-muted mb-3">Informasi kontak resmi belum dipublikasikan.</p>
                    @endif
                    <a class="btn btn-outline-success" href="{{ route('consultation.create') }}"><i class="bi bi-chat-left-text"></i> Konsultasi Informasi Hukum</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
