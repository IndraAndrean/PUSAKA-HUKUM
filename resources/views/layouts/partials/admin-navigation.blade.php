@php
    $appLogoUrl = $organizationProfile?->logo_url ?: asset('images/sipakem-logo.png');
@endphp

<div class="admin-brand-wrap">
    <a class="admin-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('admin.dashboard') }}">
        <img class="brand-logo" src="{{ $appLogoUrl }}" alt="Logo {{ $organizationProfile?->organization_name ?? 'SIPAKEM' }}">
        <span class="admin-brand-copy">
            <span class="d-block fw-bold text-truncate">{{ $organizationProfile?->portal_name ?? 'SIPAKEM' }}</span>
            <small>Panel Administrasi</small>
        </span>
    </a>
</div>

@php
    $dokumenActive = request()->routeIs('admin.documents.*', 'admin.document-types.*', 'admin.legal-categories.*', 'admin.document-divisions.*');
    $documentInputActive = request()->routeIs('admin.documents.create');
    $documentManageActive = request()->routeIs('admin.documents.index', 'admin.documents.edit');
    $documentMasterActive = request()->routeIs('admin.document-types.*', 'admin.legal-categories.*', 'admin.document-divisions.*');
    $kontenActive = request()->routeIs('admin.articles.*', 'admin.faqs.*', 'admin.consultations.*');
    $pemantauanActive = request()->routeIs('admin.audit-logs.*', 'admin.kpi.*', 'admin.organization-profile.*');
    $sistemActive = request()->routeIs('admin.users.*', 'admin.backups.*');
@endphp

<nav class="admin-nav nav flex-column">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>

    <div class="admin-nav-section {{ $dokumenActive ? 'is-open' : '' }}">
        <button class="admin-nav-group-toggle {{ $dokumenActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupDokumen{{ $idPrefix }}" aria-expanded="{{ $dokumenActive ? 'true' : 'false' }}" aria-controls="navGroupDokumen{{ $idPrefix }}">
            <span class="d-flex align-items-center gap-2"><i data-lucide="file-text"></i><span>Dokumen Hukum</span></span>
            <i data-lucide="chevron-down" class="chevron"></i>
        </button>
        <div class="accordion-collapse {{ $dokumenActive ? 'show' : '' }}" id="navGroupDokumen{{ $idPrefix }}">
            <a class="nav-link {{ $documentManageActive ? 'active' : '' }}" href="{{ route('admin.documents.index') }}">
                <i data-lucide="files"></i><span>Semua Dokumen</span>
            </a>

            <button class="admin-nav-group-toggle {{ $documentInputActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupInputDokumen{{ $idPrefix }}" aria-expanded="{{ $documentInputActive ? 'true' : 'false' }}" aria-controls="navGroupInputDokumen{{ $idPrefix }}">
                <span class="d-flex align-items-center gap-2"><i data-lucide="plus"></i><span>Input Dokumen</span></span>
                <i data-lucide="chevron-down" class="chevron"></i>
            </button>
            <div class="accordion-collapse {{ $documentInputActive ? 'show' : '' }}" id="navGroupInputDokumen{{ $idPrefix }}">
                <a class="nav-link {{ $documentInputActive && request('collection') === 'produk_hukum' ? 'active' : '' }}" href="{{ route('admin.documents.create', ['collection' => 'produk_hukum']) }}">
                    <i data-lucide="file-text"></i><span>Bank Produk Hukum</span>
                </a>
                <a class="nav-link {{ $documentInputActive && request('collection') === 'perpustakaan' ? 'active' : '' }}" href="{{ route('admin.documents.create', ['collection' => 'perpustakaan']) }}">
                    <i data-lucide="book"></i><span>Perpustakaan Digital</span>
                </a>
                <a class="nav-link {{ $documentInputActive && request('collection') === 'edukasi' ? 'active' : '' }}" href="{{ route('admin.documents.create', ['collection' => 'edukasi']) }}">
                    <i data-lucide="graduation-cap"></i><span>Materi Edukasi</span>
                </a>
            </div>

            <button class="admin-nav-group-toggle {{ $documentMasterActive ? '' : 'collapsed' }}" type="button" data-ui-toggle="collapse" data-ui-target="#navGroupMasterDokumen{{ $idPrefix }}" aria-expanded="{{ $documentMasterActive ? 'true' : 'false' }}" aria-controls="navGroupMasterDokumen{{ $idPrefix }}">
                <span class="d-flex align-items-center gap-2"><i data-lucide="database"></i><span>Master Data</span></span>
                <i data-lucide="chevron-down" class="chevron"></i>
            </button>
            <div class="accordion-collapse {{ $documentMasterActive ? 'show' : '' }}" id="navGroupMasterDokumen{{ $idPrefix }}">
                <a class="nav-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}" href="{{ route('admin.document-types.index') }}">
                    <i data-lucide="tags"></i><span>Jenis Dokumen</span>
                </a>
                <a class="nav-link {{ request()->routeIs('admin.legal-categories.*') ? 'active' : '' }}" href="{{ route('admin.legal-categories.index') }}">
                    <i data-lucide="folder-open"></i><span>Kategori Hukum</span>
                </a>
                <a class="nav-link {{ request()->routeIs('admin.document-divisions.*') ? 'active' : '' }}" href="{{ route('admin.document-divisions.index') }}">
                    <i data-lucide="building-2"></i><span>Bidang/Subbidang</span>
                </a>
            </div>
        </div>
    </div>

    <div class="admin-nav-section {{ $kontenActive ? 'is-open' : '' }}">
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
    </div>

    <div class="admin-nav-section {{ $pemantauanActive ? 'is-open' : '' }}">
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
    </div>

    @if(auth()->user()->isSuperAdmin())
        <div class="admin-nav-section {{ $sistemActive ? 'is-open' : '' }}">
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
        </div>
    @endif

</nav>
