@extends('layouts.admin')

@section('title', 'Import Massal Dokumen')
@section('page_title', 'Import Massal Dokumen')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.documents.index') }}"><i class="bi bi-arrow-left"></i> Daftar Dokumen</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-xl-7">
        <div class="content-card p-4 mb-4">
            <h2 class="h5 mb-3">1. Siapkan Data</h2>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a class="btn btn-outline-success" href="{{ route('admin.document-imports.template', 'xlsx') }}">
                    <i class="bi bi-file-earmark-excel"></i> Template Excel
                </a>
                <a class="btn btn-outline-secondary" href="{{ route('admin.document-imports.template', 'csv') }}">
                    <i class="bi bi-filetype-csv"></i> Template CSV
                </a>
            </div>
            <ol class="mb-0 ps-3">
                <li class="mb-2">Isi satu dokumen pada setiap baris tanpa mengubah nama kolom.</li>
                <li class="mb-2">Gunakan format tanggal <code>YYYY-MM-DD</code>, misalnya <code>2026-06-11</code>.</li>
                <li class="mb-2">Masukkan minimal tiga kata kunci yang dipisahkan koma.</li>
                <li>Satukan seluruh PDF ke satu file ZIP. Nama PDF harus sama persis dengan kolom <code>nama_file_pdf</code>.</li>
            </ol>
        </div>

        <form class="content-card p-4" method="post" enctype="multipart/form-data" action="{{ route('admin.document-imports.store') }}">
            @csrf
            <h2 class="h5 mb-3">2. Unggah dan Proses</h2>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="spreadsheet">Spreadsheet dokumen</label>
                <input class="form-control @error('spreadsheet') is-invalid @enderror" id="spreadsheet" name="spreadsheet" type="file" accept=".csv,.xlsx" required>
                <div class="form-text">Format CSV atau XLSX, maksimal 10 MB dan 500 baris.</div>
                @error('spreadsheet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold" for="pdf_archive">Arsip PDF</label>
                <input class="form-control @error('pdf_archive') is-invalid @enderror" id="pdf_archive" name="pdf_archive" type="file" accept=".zip,application/zip" required>
                <div class="form-text">Format ZIP maksimal 1 GB. Setiap PDF maksimal 20 MB.</div>
                @error('pdf_archive')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-cloud-arrow-up"></i> Mulai Import
            </button>
        </form>
    </div>

    <div class="col-xl-5">
        <div class="content-card p-4 mb-4">
            <h2 class="h5 mb-3">Nilai yang Diperbolehkan</h2>
            <div class="mb-3">
                <div class="fw-semibold mb-2">Jenis dokumen</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Nama</th><th>Nilai alternatif</th></tr></thead>
                        <tbody>
                        @foreach($types as $type)
                            <tr>
                                <td>{{ $type->name }}</td>
                                <td><code>{{ $type->code_prefix }}</code></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mb-3">
                <div class="fw-semibold mb-2">Kategori hukum</div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($categories as $category)
                        <span class="badge text-bg-light border">{{ $category->name }}</span>
                    @endforeach
                </div>
            </div>
            <dl class="row small mb-0">
                <dt class="col-5">Status</dt>
                <dd class="col-7"><code>berlaku</code>, <code>dicabut</code>, <code>diubah</code>, <code>tidak_berlaku</code></dd>
                <dt class="col-5">Bidang</dt>
                <dd class="col-7"><code>kum</code>, <code>bankum</code>, <code>sunluhkum</code></dd>
                <dt class="col-5">Akses</dt>
                <dd class="col-7"><code>publik</code>, <code>internal</code>, <code>terbatas</code></dd>
            </dl>
        </div>

        <div class="content-card p-4">
            <h2 class="h5 mb-3">Riwayat Import</h2>
            <div class="list-group list-group-flush">
                @forelse($recentBatches as $batch)
                    <a class="list-group-item list-group-item-action px-0" href="{{ route('admin.document-imports.show', $batch) }}">
                        <div class="d-flex justify-content-between gap-3">
                            <span class="fw-semibold">{{ $batch->spreadsheet_name }}</span>
                            <small class="text-muted text-nowrap">{{ $batch->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <small class="text-muted">{{ $batch->successful_rows }} berhasil, {{ $batch->failed_rows }} gagal</small>
                    </a>
                @empty
                    <div class="text-muted">Belum ada proses import.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
