<div class="admin-brand-wrap">
    <a class="admin-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('admin.dashboard') }}">
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
</div>

@php
    $dokumenActive = request()->routeIs('admin.documents.*', 'admin.document-imports.*', 'admin.document-types.*', 'admin.legal-categories.*');
    $kontenActive = request()->routeIs('admin.articles.*', 'admin.faqs.*', 'admin.consultations.*');
    $pemantauanActive = request()->routeIs('admin.audit-logs.*', 'admin.kpi.*', 'admin.organization-profile.*');
    $sistemActive = request()->routeIs('admin.users.*', 'admin.backups.*');
@endphp

<nav class="admin-nav nav flex-column">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>

    <button class="admin-nav-group-toggle {{ $dokumenActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupDokumen{{ $idPrefix }}" aria-expanded="{{ $dokumenActive ? 'true' : 'false' }}" aria-controls="navGroupDokumen{{ $idPrefix }}">
        <span class="d-flex align-items-center gap-2"><i data-lucide="file-text"></i><span>Dokumen Hukum</span></span>
        <i data-lucide="chevron-down" class="chevron"></i>
    </button>
    <div class="accordion-collapse {{ $dokumenActive ? 'show' : '' }}" id="navGroupDokumen{{ $idPrefix }}">
        <a class="nav-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}" href="{{ route('admin.documents.index') }}">
            <i data-lucide="file-text"></i><span>Kelola Dokumen</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.document-imports.*') ? 'active' : '' }}" href="{{ route('admin.document-imports.create') }}">
            <i data-lucide="file-up"></i><span>Import Massal</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}" href="{{ route('admin.document-types.index') }}">
            <i data-lucide="tags"></i><span>Jenis Dokumen</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.legal-categories.*') ? 'active' : '' }}" href="{{ route('admin.legal-categories.index') }}">
            <i data-lucide="folder-open"></i><span>Kategori Hukum</span>
        </a>
    </div>

    <button class="admin-nav-group-toggle {{ $kontenActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupKonten{{ $idPrefix }}" aria-expanded="{{ $kontenActive ? 'true' : 'false' }}" aria-controls="navGroupKonten{{ $idPrefix }}">
        <span class="d-flex align-items-center gap-2"><i data-lucide="newspaper"></i><span>Konten dan Layanan</span></span>
        <i data-lucide="chevron-down" class="chevron"></i>
    </button>
    <div class="accordion-collapse {{ $kontenActive ? 'show' : '' }}" id="navGroupKonten{{ $idPrefix }}">
        <a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}">
            <i data-lucide="newspaper"></i><span>Artikel</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
            <i data-lucide="circle-help"></i><span>FAQ</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.consultations.*') ? 'active' : '' }}" href="{{ route('admin.consultations.index') }}">
            <i data-lucide="message-square-text"></i><span>Konsultasi</span>
        </a>
    </div>

    <button class="admin-nav-group-toggle {{ $pemantauanActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupPemantauan{{ $idPrefix }}" aria-expanded="{{ $pemantauanActive ? 'true' : 'false' }}" aria-controls="navGroupPemantauan{{ $idPrefix }}">
        <span class="d-flex align-items-center gap-2"><i data-lucide="chart-no-axes-combined"></i><span>Pemantauan</span></span>
        <i data-lucide="chevron-down" class="chevron"></i>
    </button>
    <div class="accordion-collapse {{ $pemantauanActive ? 'show' : '' }}" id="navGroupPemantauan{{ $idPrefix }}">
        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
            <i data-lucide="shield-check"></i><span>Audit Aktivitas</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.kpi.*') ? 'active' : '' }}" href="{{ route('admin.kpi.index') }}">
            <i data-lucide="chart-no-axes-combined"></i><span>Indikator dan Survei</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.organization-profile.*') ? 'active' : '' }}" href="{{ route('admin.organization-profile.edit') }}">
            <i data-lucide="settings"></i><span>Profil Instansi</span>
        </a>
    </div>

    @if(auth()->user()->isSuperAdmin())
        <button class="admin-nav-group-toggle {{ $sistemActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupSistem{{ $idPrefix }}" aria-expanded="{{ $sistemActive ? 'true' : 'false' }}" aria-controls="navGroupSistem{{ $idPrefix }}">
            <span class="d-flex align-items-center gap-2"><i data-lucide="settings"></i><span>Sistem</span></span>
            <i data-lucide="chevron-down" class="chevron"></i>
        </button>
        <div class="accordion-collapse {{ $sistemActive ? 'show' : '' }}" id="navGroupSistem{{ $idPrefix }}">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i data-lucide="users"></i><span>Pengguna</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}">
                <i data-lucide="database-backup"></i><span>Backup Sistem</span>
            </a>
        </div>
    @endif

</nav>
