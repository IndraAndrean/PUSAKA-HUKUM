<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard Admin') - {{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            @include('layouts.partials.admin-navigation', ['idPrefix' => 'desktop'])
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="btn btn-outline-secondary btn-icon admin-mobile-menu-button" type="button" data-ui-toggle="offcanvas" data-ui-target="#adminNavigation" aria-controls="adminNavigation" title="Buka navigasi">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a class="admin-topbar-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand-mark">PH</span>
                    <strong>{{ $organizationProfile?->portal_name ?? 'PUSAKA' }}</strong>
                </a>
            </div>

            <div class="dropdown admin-user-menu">
                <button class="admin-user-trigger" type="button" data-ui-toggle="dropdown" aria-expanded="false" title="Menu akun">
                    <span class="admin-user-avatar" aria-hidden="true"></span>
                    <span class="admin-user-name">{{ auth()->user()->name }}</span>
                    <i data-lucide="chevron-down" class="admin-user-chevron"></i>
                </button>
                <ul class="dropdown-menu admin-user-dropdown">
                    <li><a class="dropdown-item" href="{{ route('home') }}"><i data-lucide="globe-2"></i> Lihat Portal</a></li>
                    <li><a class="dropdown-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i data-lucide="user"></i> Profil Saya</a></li>
                    <li><a class="dropdown-item {{ request()->routeIs('account.activity') ? 'active' : '' }}" href="{{ route('account.activity') }}"><i data-lucide="history"></i> Aktivitas Saya</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit"><i data-lucide="log-out"></i> Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="admin-content">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">@yield('page_title', 'Dashboard')</h1>
                    <div class="text-muted small">{{ auth()->user()->name }} &middot; {{ str(auth()->user()->role)->replace('_', ' ')->title() }}</div>
                </div>
                <div class="admin-page-actions">@yield('page_actions')</div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Periksa kembali input.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminNavigation" aria-labelledby="adminNavigationLabel">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h6 mb-0" id="adminNavigationLabel">Navigasi Admin</h2>
        <button type="button" class="btn-close" data-ui-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('layouts.partials.admin-navigation', ['idPrefix' => 'mobile'])
    </div>
</div>
@stack('scripts')
</body>
</html>
