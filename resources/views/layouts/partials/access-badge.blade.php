@php
    $accessMap = [
        'publik' => ['label' => 'Publik', 'icon' => 'globe-2'],
        'internal' => ['label' => 'Internal Polri', 'icon' => 'lock-open'],
        'terbatas' => ['label' => 'Terbatas', 'icon' => 'lock'],
    ];
    $accessInfo = $accessMap[$document->access_level] ?? ['label' => ucfirst($document->access_level), 'icon' => 'lock-open'];
@endphp
<span class="badge badge-access d-inline-flex align-items-center gap-1">
    <i data-lucide="{{ $accessInfo['icon'] }}"></i> {{ $accessInfo['label'] }}
</span>
