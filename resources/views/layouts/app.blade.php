<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $organizationProfile?->portal_name ?? 'PUSAKA HUKUM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-xl navbar-dark topbar public-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            @if($organizationProfile?->logo_url)
                <img class="brand-logo" src="{{ $organizationProfile->logo_url }}" alt="Logo {{ $organizationProfile->organization_name }}">
            @else
                <span class="brand-mark">PH</span>
            @endif
            <span class="brand-copy">
                <span class="d-block fw-bold lh-sm">{{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</span>
                <small class="text-white-50">{{ $organizationProfile?->organization_name ?? 'Bidang Hukum Polda Lampung' }}</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-ui-toggle="collapse" data-ui-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Buka navigasi">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-xl-center gap-xl-1">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">Produk Hukum</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('library.*') ? 'active' : '' }}" href="{{ route('library.index') }}">Perpustakaan</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('organization-profile.*') ? 'active' : '' }}" href="{{ route('organization-profile.show') }}">Profil</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('articles.*', 'faqs.*', 'consultation.*', 'surveys.*') ? 'active' : '' }}" href="#" role="button" data-ui-toggle="dropdown" aria-expanded="false">Layanan</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('articles.index') }}"><i class="bi bi-newspaper me-2"></i>Artikel Hukum</a></li>
                        <li><a class="dropdown-item" href="{{ route('faqs.index') }}"><i class="bi bi-question-circle me-2"></i>FAQ Hukum</a></li>
                        <li><a class="dropdown-item" href="{{ route('consultation.create') }}"><i class="bi bi-chat-left-text me-2"></i>Konsultasi</a></li>
                        <li><a class="dropdown-item" href="{{ route('surveys.create') }}"><i class="bi bi-ui-checks-grid me-2"></i>Survei Kepuasan</a></li>
                    </ul>
                </li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item ms-xl-2"><a class="btn btn-sm btn-warning" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2 me-1"></i> Admin</a></li>
                    @endif
                    <li class="nav-item dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-ui-toggle="dropdown" aria-expanded="false" title="Menu akun">
                            <i class="bi bi-person-circle me-1"></i> {{ str(auth()->user()->name)->before(' ') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item" href="{{ route('account.activity') }}"><i class="bi bi-clock-history me-2"></i>Aktivitas Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item ms-xl-2"><a class="btn btn-sm btn-outline-light" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="container flash-stack mt-3">
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span></div>
    </div>
@endif
@if(session('error'))
    <div class="container flash-stack mt-3">
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-circle-fill"></i><span>{{ session('error') }}</span></div>
    </div>
@endif

@yield('content')

<footer class="site-footer mt-5">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand">
                <a class="footer-logo" href="{{ route('home') }}">
                    @if($organizationProfile?->logo_url)
                        <img class="brand-logo" src="{{ $organizationProfile->logo_url }}" alt="Logo {{ $organizationProfile->organization_name }}">
                    @else
                        <span class="brand-emblem brand-emblem-footer" aria-hidden="true">
                            <i class="bi bi-book"></i>
                        </span>
                    @endif
                    <span>
                        <span class="footer-logo-title">{{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</span>
                        <small>{{ $organizationProfile?->organization_name ?? 'Bidang Hukum Polda Lampung' }}</small>
                    </span>
                </a>
                <p>{{ $organizationProfile?->about ? str($organizationProfile->about)->limit(150) : 'Pusat akses pengetahuan dan kajian hukum untuk mendukung profesionalisme dan akuntabilitas Polri.' }}</p>
            </div>

            <div>
                <div class="footer-title">Navigasi</div>
                <div class="footer-links">
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('documents.index') }}">Produk Hukum</a>
                    <a href="{{ route('library.index') }}">Perpustakaan Digital</a>
                    <a href="{{ route('articles.index') }}">Knowledge Center</a>
                    <a href="{{ route('faqs.index') }}">FAQ</a>
                    <a href="{{ route('consultation.create') }}">Konsultasi</a>
                    <a href="{{ route('organization-profile.show') }}">Profil Instansi</a>
                </div>
            </div>

            <div>
                <div class="footer-title">Kategori Populer</div>
                <div class="footer-links">
                    <a href="{{ route('documents.index') }}">Peraturan Perundang-undangan</a>
                    <a href="{{ route('documents.index') }}">Produk Hukum Polri</a>
                    <a href="{{ route('documents.index') }}">Surat Edaran</a>
                    <a href="{{ route('documents.index') }}">Juklak/Juknis</a>
                    <a href="{{ route('library.index') }}">Kajian Hukum</a>
                    <a href="{{ route('documents.index') }}">Materi Penyuluhan</a>
                </div>
            </div>

            <div>
                <div class="footer-title">Kontak Kami</div>
                <div class="footer-contact">
                    @if($organizationProfile?->address)
                        <div><i class="bi bi-map-pin"></i><span>{{ $organizationProfile->address }}</span></div>
                    @endif
                    @if($organizationProfile?->phone)
                        <div><i class="bi bi-phone"></i><a href="tel:{{ $organizationProfile->phone }}">{{ $organizationProfile->phone }}</a></div>
                    @endif
                    @if($organizationProfile?->email)
                        <div><i class="bi bi-envelope"></i><a href="mailto:{{ $organizationProfile->email }}">{{ $organizationProfile->email }}</a></div>
                    @endif
                    @if($organizationProfile?->website)
                        <div><i class="bi bi-globe2"></i><a href="{{ $organizationProfile->website }}" target="_blank" rel="noopener">{{ str($organizationProfile->website)->replace(['https://', 'http://'], '') }}</a></div>
                    @endif
                    @unless($organizationProfile?->hasContactInformation())
                        <div><i class="bi bi-building"></i><span>{{ $organizationProfile?->organization_name ?? 'Bidang Hukum Polda Lampung' }}</span></div>
                    @endunless
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; {{ now()->year }} {{ $organizationProfile?->organization_name ?? 'Bidang Hukum Polda Lampung' }}. Semua Hak Dilindungi.</div>
            <div class="footer-bottom-links">
                <a href="{{ route('organization-profile.show') }}">Kebijakan Privasi</a>
                <span></span>
                <a href="{{ route('organization-profile.show') }}">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
