@extends('layouts.admin')

@section('title', 'KPI dan Survei')
@section('page_title', 'Indikator Keberhasilan')

@section('page_actions')
<a class="btn btn-outline-success" href="{{ route('admin.kpi.export') }}"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV</a>
@endsection

@section('content')
<div class="alert alert-info border-0">
    <i class="bi bi-info-circle me-1"></i>
    Target awal mengikuti dokumen proyek perubahan dan dapat disesuaikan admin. Angka bertanda <strong>verifikasi admin</strong> tidak dihitung otomatis oleh sistem.
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="message-square-text"></i></span>
            <div><div class="stat-card-value">{{ number_format($actuals['survey_responses']) }}</div><div class="stat-card-label">Respons Survei</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon bg-emerald-50 text-emerald-700"><i data-lucide="circle-check-big"></i></span>
            <div><div class="stat-card-value">{{ number_format($actuals['satisfaction'], 1) }}%</div><div class="stat-card-label">Kepuasan</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="search"></i></span>
            <div><div class="stat-card-value">{{ $actuals['search_time'] ? number_format($actuals['search_time'] / 60, 1).' menit' : '-' }}</div><div class="stat-card-label">Waktu Pencarian</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="stat-card-icon"><i data-lucide="refresh-cw"></i></span>
            <div><div class="stat-card-value">{{ number_format($actuals['monthly_updates']) }}</div><div class="stat-card-label">Pembaruan 30 Hari</div></div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation"><button class="nav-link active" data-ui-toggle="tab" data-ui-target="#capaian" type="button">Capaian KPI</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-ui-toggle="tab" data-ui-target="#survei" type="button">Respons Survei</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-ui-toggle="tab" data-ui-target="#pengaturan" type="button">Target dan Verifikasi</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="capaian">
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="content-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Tren Enam Bulan</h2>
                        <span class="small text-muted">Akses dan kepuasan</span>
                    </div>
                    <div style="position: relative; width: 100%; height: 300px"><canvas id="kpiTrendChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="content-card p-3 h-100">
                    <h2 class="h5">Kesiapan Operasional</h2>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between"><span>Admin ditunjuk</span><strong>{{ $actuals['appointed_admins'] }} orang</strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span>SOP pengelolaan</span><strong class="{{ $actuals['sop_available'] ? 'text-success' : 'text-warning' }}">{{ $actuals['sop_available'] ? 'Tersedia' : 'Belum' }}</strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span>Panduan pengguna</span><strong class="{{ $actuals['user_guide_available'] ? 'text-success' : 'text-warning' }}">{{ $actuals['user_guide_available'] ? 'Tersedia' : 'Belum' }}</strong></div>
                        <div class="list-group-item px-0 d-flex justify-content-between"><span>Pembaruan bulanan</span><strong class="{{ $actuals['monthly_updates'] > 0 ? 'text-success' : 'text-warning' }}">{{ $actuals['monthly_updates'] > 0 ? 'Aktif' : 'Belum tercatat' }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card p-3">
            <h2 class="h5 mb-3">Matriks Capaian</h2>
            @php
                $groupIcons = [
                    'Dokumen' => 'files',
                    'Pengguna & Akses' => 'users',
                    'Kualitas Layanan' => 'shield-check',
                    'Cakupan Wilayah' => 'map-pin',
                ];
            @endphp
            @foreach(collect($indicators)->groupBy('group') as $groupName => $groupIndicators)
                <div class="admin-section-title"><i data-lucide="{{ $groupIcons[$groupName] ?? 'list-checks' }}"></i> {{ $groupName }}</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Indikator</th><th>Realisasi</th><th>Target</th><th style="min-width: 180px">Capaian</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($groupIndicators as $indicator)
                            @php
                                $noData = array_key_exists('has_data', $indicator) && ! $indicator['has_data'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $indicator['label'] }}</div>
                                    @if($indicator['manual'])<span class="badge text-bg-light border">Verifikasi admin</span>@endif
                                    @if($indicator['label'] === 'Cakupan Satker')
                                        <div class="small text-muted mt-1">{{ $detectedUnits['satker']->count() }} satuan kerja unik terdeteksi dari data pengguna aktif</div>
                                    @elseif($indicator['label'] === 'Cakupan Polres')
                                        <div class="small text-muted mt-1">{{ $detectedUnits['polres']->count() }} Polres/Polresta unik terdeteksi dari data pengguna aktif</div>
                                    @endif
                                </td>
                                <td>{{ $noData ? '-' : number_format($indicator['actual'], is_float($indicator['actual']) ? 1 : 0) }} {{ $noData ? '' : $indicator['unit'] }}</td>
                                <td>{{ number_format($indicator['target'], is_float($indicator['target']) ? 1 : 0) }} {{ $indicator['unit'] }}</td>
                                <td>
                                    <div class="progress" role="progressbar" aria-label="Capaian {{ $indicator['label'] }}" aria-valuenow="{{ $indicator['progress'] }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px">
                                        <div class="progress-bar {{ $indicator['achieved'] ? 'bg-success' : 'bg-warning' }}" style="width: {{ $indicator['progress'] }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ number_format($indicator['progress'], 1) }}%</span>
                                </td>
                                <td>
                                    @if($noData)
                                        <span class="badge text-bg-secondary">Belum ada data</span>
                                    @elseif($indicator['achieved'])
                                        <span class="badge text-bg-success">Tercapai</span>
                                    @else
                                        <span class="badge text-bg-warning">Belum tercapai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>

    <div class="tab-pane fade" id="survei">
        <div class="content-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Respons Survei Terbaru</h2>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('surveys.create') }}" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Form Survei</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Waktu</th><th>Responden</th><th>Kepuasan</th><th>Dokumen</th><th>Waktu Cari</th><th>Masukan</th></tr></thead>
                    <tbody>
                    @forelse($surveys as $survey)
                        <tr>
                            <td class="text-nowrap">{{ $survey->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $survey->user?->name ?? str($survey->respondent_type)->replace('_', ' ')->title() }}</td>
                            <td><span class="badge {{ $survey->satisfaction_percentage >= $target->satisfaction_target_percent ? 'text-bg-success' : 'text-bg-warning' }}">{{ $survey->satisfaction_percentage }}%</span></td>
                            <td>{{ $survey->found_document ? 'Ditemukan' : 'Tidak ditemukan' }}</td>
                            <td>{{ $survey->search_duration_seconds ? number_format($survey->search_duration_seconds / 60, 1).' menit' : '-' }}</td>
                            <td style="min-width: 240px">{{ $survey->feedback ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Belum ada respons survei.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $surveys->links() }}
        </div>
    </div>

    <div class="tab-pane fade" id="pengaturan">
        <form method="post" action="{{ route('admin.kpi.update') }}">
            @csrf
            @method('put')
            <div class="content-card p-3 mb-4">
                <h2 class="h5">Target Kuantitatif</h2>
                <div class="row g-3">
                    @php
                        $targets = [
                            'documents_target' => 'Total dokumen',
                            'legislation_target' => 'Peraturan perundang-undangan',
                            'internal_documents_target' => 'Produk hukum internal',
                            'legal_studies_target' => 'Kajian dan legal opinion',
                            'education_materials_target' => 'Materi penyuluhan',
                            'registered_users_target' => 'Pengguna terdaftar',
                            'accesses_target' => 'Jumlah akses',
                        ];
                    @endphp
                    @foreach($targets as $field => $label)
                        <div class="col-md-6 col-xl-4">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <input class="form-control" type="number" min="1" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $target->{$field}) }}" required>
                        </div>
                    @endforeach
                    <div class="col-md-6 col-xl-4"><label class="form-label" for="satisfaction_target_percent">Kepuasan (%)</label><input class="form-control" type="number" min="1" max="100" step="0.1" id="satisfaction_target_percent" name="satisfaction_target_percent" value="{{ old('satisfaction_target_percent', $target->satisfaction_target_percent) }}" required></div>
                    <div class="col-md-6 col-xl-4"><label class="form-label" for="utilization_target_percent">Pemanfaatan pengguna (%)</label><input class="form-control" type="number" min="1" max="100" step="0.1" id="utilization_target_percent" name="utilization_target_percent" value="{{ old('utilization_target_percent', $target->utilization_target_percent) }}" required></div>
                    <div class="col-md-6 col-xl-4"><label class="form-label" for="search_time_target_seconds">Waktu pencarian (detik)</label><input class="form-control" type="number" min="1" max="7200" id="search_time_target_seconds" name="search_time_target_seconds" value="{{ old('search_time_target_seconds', $target->search_time_target_seconds) }}" required></div>
                    <div class="col-md-6 col-xl-4"><label class="form-label" for="satker_coverage_target_percent">Target cakupan Satker (%)</label><input class="form-control" type="number" min="1" max="100" step="0.1" id="satker_coverage_target_percent" name="satker_coverage_target_percent" value="{{ old('satker_coverage_target_percent', $target->satker_coverage_target_percent) }}" required></div>
                    <div class="col-md-6 col-xl-4"><label class="form-label" for="polres_coverage_target_percent">Target cakupan Polres (%)</label><input class="form-control" type="number" min="1" max="100" step="0.1" id="polres_coverage_target_percent" name="polres_coverage_target_percent" value="{{ old('polres_coverage_target_percent', $target->polres_coverage_target_percent) }}" required></div>
                </div>
            </div>

            <div class="content-card p-3">
                <h2 class="h5">Verifikasi Operasional</h2>
                <p class="text-muted small">Isi berdasarkan bukti implementasi terbaru, bukan perkiraan.</p>

                <div class="alert alert-info border-0 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2 fw-semibold"><i data-lucide="info"></i> Bukti pendukung otomatis</div>
                    <p class="small mb-2">
                        Belum ada daftar resmi seluruh Satker &amp; Polres jajaran Polda Lampung di sistem ini, jadi persentase cakupan di bawah
                        <strong>masih diisi manual</strong>. Daftar berikut dihitung otomatis dari kolom &ldquo;Satuan Kerja&rdquo; pengguna aktif
                        sebagai pembanding &mdash; gunakan untuk mengecek kewajaran angka yang Anda masukkan.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small fw-semibold mb-1">Satker terdeteksi ({{ $detectedUnits['satker']->count() }})</div>
                            @if($detectedUnits['satker']->isNotEmpty())
                                <div class="small text-muted">{{ $detectedUnits['satker']->implode(', ') }}</div>
                            @else
                                <div class="small text-muted">Belum ada pengguna aktif dengan satuan kerja terisi.</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="small fw-semibold mb-1">Polres/Polresta terdeteksi ({{ $detectedUnits['polres']->count() }})</div>
                            @if($detectedUnits['polres']->isNotEmpty())
                                <div class="small text-muted">{{ $detectedUnits['polres']->implode(', ') }}</div>
                            @else
                                <div class="small text-muted">Belum ada pengguna aktif dengan satuan kerja Polres/Polresta terisi.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="satker_coverage_percent">Realisasi cakupan Satker (%)</label><input class="form-control" type="number" min="0" max="100" step="0.1" id="satker_coverage_percent" name="satker_coverage_percent" value="{{ old('satker_coverage_percent', $target->satker_coverage_percent) }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="polres_coverage_percent">Realisasi cakupan Polres (%)</label><input class="form-control" type="number" min="0" max="100" step="0.1" id="polres_coverage_percent" name="polres_coverage_percent" value="{{ old('polres_coverage_percent', $target->polres_coverage_percent) }}" required></div>
                    <div class="col-md-4">
                        <label class="form-label">Admin yang ditunjuk</label>
                        <div class="form-control bg-light d-flex align-items-center gap-2" style="min-height: 2.5rem;">
                            <i data-lucide="shield-check"></i>
                            <span class="fw-semibold">{{ $actuals['appointed_admins'] }} orang</span>
                        </div>
                        <div class="form-text">Dihitung otomatis dari pengguna berperan admin/super admin yang aktif — tidak perlu diisi manual.</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-md-4">
                            <input type="hidden" name="sop_available" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="sop_available" name="sop_available" value="1" @checked(old('sop_available', $target->sop_available))>
                            <label class="form-check-label" for="sop_available">SOP pengelolaan tersedia</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-md-4">
                            <input type="hidden" name="user_guide_available" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="user_guide_available" name="user_guide_available" value="1" @checked(old('user_guide_available', $target->user_guide_available))>
                            <label class="form-check-label" for="user_guide_available">Panduan pengguna tersedia</label>
                        </div>
                    </div>
                    <div class="col-12"><label class="form-label" for="verification_notes">Catatan dan dasar verifikasi</label><textarea class="form-control" id="verification_notes" name="verification_notes" rows="4" maxlength="3000">{{ old('verification_notes', $target->verification_notes) }}</textarea></div>
                </div>
                <div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i> Simpan Indikator</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const trendData = @json($trends);
    const trendCanvas = document.getElementById('kpiTrendChart');

    new Chart(trendCanvas, {
        type: 'line',
        data: {
            labels: trendData.map(item => item.label),
            datasets: [
                {
                    label: 'Akses',
                    data: trendData.map(item => item.accesses),
                    borderColor: '#16796f',
                    backgroundColor: '#16796f',
                    tension: 0.25,
                    yAxisID: 'y'
                },
                {
                    label: 'Kepuasan (%)',
                    data: trendData.map(item => item.satisfaction),
                    borderColor: '#c79a31',
                    backgroundColor: '#c79a31',
                    tension: 0.25,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                y1: { beginAtZero: true, max: 100, position: 'right', grid: { drawOnChartArea: false } }
            },
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endpush
