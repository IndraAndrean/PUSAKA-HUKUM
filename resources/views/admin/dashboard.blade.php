@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Monitoring')

@section('content')
@php
    $trendChip = function (int $delta) {
        if ($delta > 0) return ['class' => 'up', 'icon' => 'trending-up', 'text' => '+'.$delta.' bulan ini'];
        if ($delta < 0) return ['class' => 'down', 'icon' => 'trending-down', 'text' => $delta.' bulan ini'];
        return ['class' => 'flat', 'icon' => null, 'text' => 'Tetap bulan ini'];
    };
    $documentsTrend = $trendChip($documentsDelta);
    $viewsTrend = $trendChip($viewsDelta);
    $downloadsTrend = $trendChip($downloadsDelta);
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="files"></i></span>
            <div>
                <div class="stat-card-value">{{ $totalDocuments }}</div>
                <div class="stat-card-label">Total Dokumen</div>
                <span class="stat-trend {{ $documentsTrend['class'] }}">@if($documentsTrend['icon'])<i data-lucide="{{ $documentsTrend['icon'] }}"></i>@endif {{ $documentsTrend['text'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="users"></i></span>
            <div>
                <div class="stat-card-value">{{ $totalUsers }}</div>
                <div class="stat-card-label">Total Pengguna</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="eye"></i></span>
            <div>
                <div class="stat-card-value">{{ $totalViews }}</div>
                <div class="stat-card-label">Total Dilihat</div>
                <span class="stat-trend {{ $viewsTrend['class'] }}">@if($viewsTrend['icon'])<i data-lucide="{{ $viewsTrend['icon'] }}"></i>@endif {{ $viewsTrend['text'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="download"></i></span>
            <div>
                <div class="stat-card-value">{{ $totalDownloads }}</div>
                <div class="stat-card-label">Total Unduhan</div>
                <span class="stat-trend {{ $downloadsTrend['class'] }}">@if($downloadsTrend['icon'])<i data-lucide="{{ $downloadsTrend['icon'] }}"></i>@endif {{ $downloadsTrend['text'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="shield-check"></i></span>
            <div class="flex-grow-1">
                <div class="stat-card-value">{{ $averageMetadataCompleteness }}%</div>
                <div class="stat-card-label">Rata-rata Metadata</div>
                <div class="progress mt-2" style="height: 6px" role="progressbar" aria-valuenow="{{ $averageMetadataCompleteness }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar {{ $averageMetadataCompleteness >= 95 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $averageMetadataCompleteness }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon bg-emerald-50 text-emerald-700"><i data-lucide="circle-check-big"></i></span>
            <div>
                <div class="stat-card-value">{{ $completeMetadataDocuments }}</div>
                <div class="stat-card-label">Metadata &ge; 95%</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon bg-amber-50 text-amber-700"><i data-lucide="triangle-alert"></i></span>
            <div>
                <div class="stat-card-value">{{ $incompleteMetadataDocuments }}</div>
                <div class="stat-card-label">Perlu Dilengkapi</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon bg-red-50 text-red-700"><i data-lucide="history"></i></span>
            <div>
                <div class="stat-card-value">{{ $documentsDueReview }}</div>
                <div class="stat-card-label">Jatuh Tempo Review</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="content-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Aktivitas Enam Bulan Terakhir</h2>
                <span class="small text-muted">Upload, lihat, dan unduh</span>
            </div>
            <div style="position: relative; width: 100%; height: 300px;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="content-card p-3 h-100">
            <h2 class="h5">Ringkasan Sistem</h2>
            <div style="position: relative; width: 100%; height: 160px;"><canvas id="accessLevelChart"></canvas></div>
            <div class="chart-legend">
                <div class="chart-legend-item"><span class="d-flex align-items-center gap-2"><span class="chart-legend-dot" style="background:#14776d"></span> Publik</span><strong>{{ $publicDocuments }}</strong></div>
                <div class="chart-legend-item"><span class="d-flex align-items-center gap-2"><span class="chart-legend-dot" style="background:#d3a72f"></span> Internal</span><strong>{{ $internalDocuments }}</strong></div>
                <div class="chart-legend-item"><span class="d-flex align-items-center gap-2"><span class="chart-legend-dot" style="background:#a33b42"></span> Terbatas</span><strong>{{ $restrictedDocuments }}</strong></div>
            </div>
            <hr class="my-3 border-pusaka-line">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between px-0"><span>Artikel</span><strong>{{ $totalArticles }}</strong></div>
                <div class="list-group-item d-flex justify-content-between px-0"><span>Konsultasi masuk</span><strong>{{ $openConsultations }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="content-card p-3 h-100">
            <h2 class="h5">Dokumen Paling Banyak Dilihat</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Judul</th><th>Jenis</th><th class="text-end">Dilihat</th></tr></thead>
                    <tbody>
                    @forelse($popularDocuments as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ $document->type?->name }}</td>
                            <td class="text-end">{{ $document->views_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="content-card p-3 h-100">
            <h2 class="h5">Dokumen Paling Banyak Diunduh</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Judul</th><th>Jenis</th><th class="text-end">Unduhan</th></tr></thead>
                    <tbody>
                    @forelse($mostDownloadedDocuments as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ $document->type?->name }}</td>
                            <td class="text-end">{{ $document->downloads_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="content-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Aktivitas Akses Terbaru</h2>
        <span class="small text-muted">{{ $totalConsultations }} total konsultasi</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Dokumen</th><th>Pengguna</th><th>IP Address</th><th>Waktu</th></tr></thead>
            <tbody>
            @forelse($latestAccesses as $access)
                <tr>
                    <td>{{ $access->document?->title ?? 'Dokumen telah dihapus' }}</td>
                    <td>{{ $access->user?->name ?? 'Pengunjung publik' }}</td>
                    <td>{{ $access->ip_address ?: '-' }}</td>
                    <td>{{ $access->accessed_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">Belum ada aktivitas akses.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="content-card p-3 mt-4">
    <h2 class="h5">Dokumen Memerlukan Perhatian</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Dokumen</th><th>Kelengkapan</th><th>Review</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($documentsNeedingAttention as $document)
                <tr>
                    <td>{{ $document->title }}</td>
                    <td><span class="badge {{ $document->metadata_completeness >= 95 ? 'text-bg-success' : 'text-bg-warning' }}">{{ $document->metadata_completeness }}%</span></td>
                    <td>{{ $document->needs_review ? 'Jatuh tempo' : ($document->next_review_at?->format('d/m/Y') ?? '-') }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.documents.edit', $document) }}"><i class="bi bi-pencil"></i> Lengkapi</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">Semua metadata lengkap dan tidak ada review yang jatuh tempo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const activityData = @json($monthlyStatistics);
    const chartElement = document.getElementById('activityChart');

    new Chart(chartElement, {
        type: 'line',
        data: {
            labels: activityData.map(item => item.label),
            datasets: [
                {
                    label: 'Upload',
                    data: activityData.map(item => item.uploads),
                    borderColor: '#c79a31',
                    backgroundColor: '#c79a31',
                    tension: 0.25
                },
                {
                    label: 'Dilihat',
                    data: activityData.map(item => item.views),
                    borderColor: '#16796f',
                    backgroundColor: '#16796f',
                    tension: 0.25
                },
                {
                    label: 'Diunduh',
                    data: activityData.map(item => item.downloads),
                    borderColor: '#123047',
                    backgroundColor: '#123047',
                    tension: 0.25
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { position: 'bottom' } }
        }
    });

    new Chart(document.getElementById('accessLevelChart'), {
        type: 'doughnut',
        data: {
            labels: ['Publik', 'Internal', 'Terbatas'],
            datasets: [{
                data: [{{ $publicDocuments }}, {{ $internalDocuments }}, {{ $restrictedDocuments }}],
                backgroundColor: ['#14776d', '#d3a72f', '#a33b42'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
