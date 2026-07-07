<a class="admin-brand d-flex align-items-center gap-2 mb-3 text-white text-decoration-none" href="{{ route('admin.dashboard') }}">
    @if($organizationProfile?->logo_url)
        <img class="brand-logo" src="{{ $organizationProfile->logo_url }}" alt="Logo {{ $organizationProfile->organization_name }}">
    @else
        <span class="admin-brand-mark">PH</span>
    @endif
    <span class="admin-brand-copy">
        <span class="d-block fw-bold text-truncate">{{ $organizationProfile?->portal_name ?? 'PUSAKA HUKUM' }}</span>
        <small>Panel Administrasi</small>
    </span>
</a>

<nav class="admin-nav nav flex-column">
    <div class="admin-nav-label">Ringkasan</div>
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
    </a>

    <div class="admin-nav-label">Dokumen Hukum</div>
    <a class="nav-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}" href="{{ route('admin.documents.index') }}">
        <i class="bi bi-file-earmark-text"></i><span>Kelola Dokumen</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.document-imports.*') ? 'active' : '' }}" href="{{ route('admin.document-imports.create') }}">
        <i class="bi bi-file-earmark-arrow-up"></i><span>Import Massal</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}" href="{{ route('admin.document-types.index') }}">
        <i class="bi bi-tags"></i><span>Jenis Dokumen</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.legal-categories.*') ? 'active' : '' }}" href="{{ route('admin.legal-categories.index') }}">
        <i class="bi bi-folder2-open"></i><span>Kategori Hukum</span>
    </a>

    <div class="admin-nav-label">Konten dan Layanan</div>
    <a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}">
        <i class="bi bi-newspaper"></i><span>Artikel</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
        <i class="bi bi-question-circle"></i><span>FAQ</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.consultations.*') ? 'active' : '' }}" href="{{ route('admin.consultations.index') }}">
        <i class="bi bi-chat-left-text"></i><span>Konsultasi</span>
    </a>

    <div class="admin-nav-label">Pemantauan</div>
    <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
        <i class="bi bi-shield-check"></i><span>Audit Aktivitas</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.kpi.*') ? 'active' : '' }}" href="{{ route('admin.kpi.index') }}">
        <i class="bi bi-graph-up-arrow"></i><span>Indikator dan Survei</span>
    </a>
    <a class="nav-link {{ request()->routeIs('admin.organization-profile.*') ? 'active' : '' }}" href="{{ route('admin.organization-profile.edit') }}">
        <i class="bi bi-building-gear"></i><span>Profil Instansi</span>
    </a>

    @if(auth()->user()->isSuperAdmin())
        <div class="admin-nav-label">Sistem</div>
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i><span>Pengguna</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}">
            <i class="bi bi-database-check"></i><span>Backup Sistem</span>
        </a>
    @endif

    <div class="admin-nav-label">Akun</div>
    <a class="nav-link" href="{{ route('profile.edit') }}">
        <i class="bi bi-person"></i><span>Profil Saya</span>
    </a>
    <a class="nav-link" href="{{ route('account.activity') }}">
        <i class="bi bi-clock-history"></i><span>Aktivitas Saya</span>
    </a>
    <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-globe2"></i><span>Lihat Portal</span>
    </a>
    <form action="{{ route('logout') }}" method="post" class="mt-2">
        @csrf
        <button class="nav-link w-100 border-0 bg-transparent text-start" type="submit">
            <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
        </button>
    </form>
</nav>
