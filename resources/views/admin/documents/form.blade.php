@extends('layouts.admin')

@php
    $isEdit = $document->exists;
    $collection = $selectedCollection ?? $document->type?->collection ?? 'produk_hukum';
    $collectionLabel = $collections[$collection] ?? 'Dokumen';
    $isLibraryForm = $collection === 'perpustakaan';
    $isEducationForm = $collection === 'edukasi';
    $isProductForm = $collection === 'produk_hukum';
    $titleLabel = $isEdit ? 'Edit Dokumen' : 'Tambah '.$collectionLabel;
    $titlePlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: Buku Pedoman Bantuan Hukum Polri',
        'edukasi' => 'Contoh: Materi Penyuluhan Hukum tentang Bantuan Hukum Polri',
        default => 'Contoh: Surat Telegram Kapolda Lampung tentang Tata Cara Pemberian Bantuan Hukum oleh Polri',
    };
    $summaryPlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: Referensi ini membahas konsep, ruang lingkup, dan praktik bantuan hukum di lingkungan kepolisian.',
        'edukasi' => 'Contoh: Materi ini digunakan untuk penyuluhan hukum mengenai hak dan kewajiban personel dalam pelaksanaan tugas.',
        default => 'Contoh: Dokumen ini mengatur pedoman penyidikan tindak pidana di lingkungan Kepolisian Negara Republik Indonesia.',
    };
    $authorPlaceholder = $isEducationForm
        ? 'Contoh: Subbid Bankum / Tim Penyuluhan Hukum'
        : 'Contoh: Bidkum Polda Lampung / Tim Penyusun';
    $publisherPlaceholder = $isLibraryForm
        ? 'Contoh: Polda Lampung / Mabes Polri / Penerbit Jurnal Hukum'
        : 'Contoh: Kepolisian Daerah Lampung';
    $numberPlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: ISBN 978-602-1234-56-7, ISSN 2045-7788, atau kode katalog',
        'edukasi' => 'Contoh: ME-BANKUM-001/2026 atau MODUL-SUHLUKUM-2026',
        default => 'Contoh: ST/96/I/HUK.2.0./2020, KEP/12/III/2026, atau PERKAPOLDA/1/2026',
    };
    $numberHelp = match($collection) {
        'perpustakaan' => 'Opsional. Isi jika referensi memiliki nomor katalog, ISBN/ISSN, atau nomor terbit internal.',
        'edukasi' => 'Opsional. Isi jika materi memiliki kode modul, nomor bahan penyuluhan, atau nomor administrasi internal.',
        default => 'Isi nomor resmi sesuai naskah dokumen, misalnya ST, KEP, SE, atau nomor peraturan.',
    };
    $dateHelp = match($collection) {
        'perpustakaan' => 'Opsional. Isi jika referensi memiliki tanggal terbit atau tanggal publikasi.',
        'edukasi' => 'Opsional. Isi jika materi memiliki tanggal penyusunan, penyampaian, atau publikasi.',
        default => 'Tanggal dokumen ditetapkan, ditandatangani, atau diterbitkan.',
    };
    $effectiveDateHelp = $isProductForm
        ? 'Tanggal mulai berlaku. Jika sama dengan tanggal penetapan, isi tanggal yang sama.'
        : 'Opsional. Isi hanya jika referensi atau materi memiliki tanggal mulai berlaku.';
    $keywordsPlaceholder = match($collection) {
        'perpustakaan' => 'bantuan hukum, referensi hukum, kepolisian',
        'edukasi' => 'penyuluhan hukum, bantuan hukum, personel polri',
        default => 'surat telegram, bantuan hukum, polda lampung',
    };
    $abstractPlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: Menguraikan pokok bahasan, cakupan referensi, dan manfaatnya sebagai bahan bacaan hukum.',
        'edukasi' => 'Contoh: Memuat tujuan pembelajaran, sasaran peserta, pokok materi, dan pesan utama penyuluhan.',
        default => 'Contoh: Memuat ruang lingkup, maksud, tujuan, dan pokok pengaturan dokumen.',
    };
    $legalBasisPlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: Undang-Undang Nomor 2 Tahun 2002, Perkap terkait, atau kosongkan jika tidak ada.',
        'edukasi' => 'Contoh: UU Kepolisian, Perkap/Perpol terkait, atau kosongkan jika materi tidak memuat dasar hukum khusus.',
        default => 'Contoh: Undang-Undang Nomor 2 Tahun 2002 tentang Kepolisian Negara Republik Indonesia.',
    };
    $legalBasisHelp = $isProductForm
        ? 'Cantumkan dasar hukum utama yang menjadi rujukan dokumen.'
        : 'Opsional. Cantumkan jika referensi atau materi mencantumkan dasar hukum utama.';
    $relatedRegulationPlaceholder = match($collection) {
        'perpustakaan' => 'Contoh: Buku pedoman, jurnal, kajian, atau dokumen rujukan lain yang berkaitan.',
        'edukasi' => 'Contoh: Modul pendukung, bahan sosialisasi, surat edaran, atau aturan terkait materi.',
        default => 'Contoh: Perkap Nomor ..., Surat Edaran Kapolri Nomor ..., atau dokumen pendukung lain.',
    };
    $relatedRegulationHelp = $isProductForm
        ? 'Isi regulasi yang berkaitan agar relasi penelusuran dokumen lebih kuat.'
        : 'Opsional. Isi sumber pendukung agar pengguna dapat menelusuri bahan terkait.';
    $fileNameHelp = match($collection) {
        'perpustakaan' => 'Nama file dibuat otomatis. Jika nomor kosong, sistem memakai jenis, tahun, dan judul referensi.',
        'edukasi' => 'Nama file dibuat otomatis. Jika nomor kosong, sistem memakai jenis, tahun, dan judul materi.',
        default => 'Nama file dibuat otomatis mengikuti format: JENIS_NOMOR_TAHUN_JUDUL.pdf.',
    };
@endphp

@section('title', $titleLabel)
@section('page_title', $titleLabel)

@section('content')
<form class="content-card p-3" method="post" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.documents.update', $document) : route('admin.documents.store') }}"
    @if($isEdit)
        data-confirm="Simpan perubahan metadata dokumen ini?"
        data-confirm-title="Simpan Perubahan Dokumen"
        data-confirm-label="Ya, Simpan"
        data-confirm-file="File PDF baru akan mengganti file lama. Pastikan dokumen yang dipilih sudah benar sebelum disimpan."
        data-confirm-file-title="Ganti File PDF Dokumen"
    @endif
>
    @csrf
    @if($isEdit)
        @method('put')
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="title">Judul Dokumen</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $document->title) }}" placeholder="{{ $titlePlaceholder }}" required>
            <div class="form-text">Gunakan judul resmi sesuai naskah agar mudah ditemukan melalui pencarian.</div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="document_type_id">Jenis Dokumen</label>
            <select class="form-select" id="document_type_id" name="document_type_id" required>
                <option value="">Pilih jenis</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" data-collection="{{ $type->collection }}" @selected(old('document_type_id', $document->document_type_id) == $type->id)>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Pilihan jenis dibatasi sesuai kelompok {{ $collectionLabel }} agar input lebih mudah dan tidak tercampur.</div>
        </div>
        @if($types->isEmpty())
            <div class="col-12">
                <div class="alert alert-warning mb-0">Belum ada jenis dokumen untuk kelompok {{ $collectionLabel }}. Tambahkan dulu melalui menu Jenis Dokumen.</div>
            </div>
        @endif
        @if($isLibraryForm || $isEducationForm)
        <div class="col-md-6">
            <label class="form-label" for="author">{{ $isEducationForm ? 'Penyusun Materi' : 'Penulis/Penyusun' }}</label>
            <input class="form-control" id="author" name="author" value="{{ old('author', $document->author) }}" placeholder="{{ $authorPlaceholder }}" required>
            <div class="form-text">{{ $isEducationForm ? 'Wajib untuk materi edukasi agar sumber penyusun jelas.' : 'Wajib untuk koleksi perpustakaan digital seperti buku, jurnal, modul, atau kajian.' }}</div>
        </div>
        @endif
        @if($isLibraryForm)
        <div class="col-md-6">
            <label class="form-label" for="publisher">Penerbit</label>
            <input class="form-control" id="publisher" name="publisher" value="{{ old('publisher', $document->publisher) }}" placeholder="{{ $publisherPlaceholder }}" required>
            <div class="form-text">Isi nama lembaga atau penerbit yang menerbitkan bahan referensi.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="isbn_issn">ISBN/ISSN</label>
            <input class="form-control" id="isbn_issn" name="isbn_issn" value="{{ old('isbn_issn', $document->isbn_issn) }}" placeholder="Contoh: 978-602-1234-56-7 / 2045-7788">
            <div class="form-text">Boleh dikosongkan jika dokumen tidak memiliki nomor ISBN atau ISSN.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="edition_volume">Edisi/Volume</label>
            <input class="form-control" id="edition_volume" name="edition_volume" value="{{ old('edition_volume', $document->edition_volume) }}" placeholder="Contoh: Edisi 2 / Vol. 5 No. 1">
            <div class="form-text">Gunakan untuk buku, jurnal, majalah hukum, atau seri kajian berkala.</div>
        </div>
        @endif
        @if($isEducationForm)
            <input type="hidden" name="publisher" value="{{ old('publisher', $document->publisher) }}">
            <input type="hidden" name="isbn_issn" value="{{ old('isbn_issn', $document->isbn_issn) }}">
            <input type="hidden" name="edition_volume" value="{{ old('edition_volume', $document->edition_volume) }}">
        @endif
        <div class="col-md-4">
            <label class="form-label" for="document_number">Nomor Dokumen</label>
            <input class="form-control" id="document_number" name="document_number" value="{{ old('document_number', $document->document_number) }}" placeholder="{{ $numberPlaceholder }}" @required($isProductForm)>
            <div class="form-text">{{ $numberHelp }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="year">Tahun</label>
            <input class="form-control" id="year" type="number" name="year" min="1900" max="2100" value="{{ old('year', $document->year ?: now()->year) }}" required>
            <div class="form-text">Tahun terbit, penetapan, atau publikasi dokumen.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="issuing_institution">Instansi/Organisasi Penerbit</label>
            <input class="form-control" id="issuing_institution" name="issuing_institution" value="{{ old('issuing_institution', $document->issuing_institution) }}" placeholder="Contoh: Kepolisian Negara Republik Indonesia" required>
            <div class="form-text">Tuliskan instansi yang mengesahkan, menerbitkan, atau menjadi sumber dokumen.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="enacted_date">Tanggal Penetapan</label>
            <input class="form-control" id="enacted_date" type="date" name="enacted_date" value="{{ old('enacted_date', optional($document->enacted_date)->format('Y-m-d')) }}" @required($isProductForm)>
            <div class="form-text">{{ $dateHelp }}</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="effective_date">Tanggal Berlaku</label>
            <input class="form-control" id="effective_date" type="date" name="effective_date" value="{{ old('effective_date', optional($document->effective_date)->format('Y-m-d')) }}" @required($isProductForm)>
            <div class="form-text">{{ $effectiveDateHelp }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="document_status">Status Dokumen</label>
            <select class="form-select" id="document_status" name="document_status" required>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('document_status', $document->document_status ?: 'berlaku') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">Gunakan status Berlaku jika dokumen masih menjadi rujukan aktif.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="legal_category_id">Kategori Hukum</label>
            <select class="form-select" id="legal_category_id" name="legal_category_id" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('legal_category_id', $document->legal_category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Kategori membantu pengguna menelusuri dokumen berdasarkan bidang hukum.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="access_level">Level Akses</label>
            <select class="form-select" id="access_level" name="access_level" required>
                @foreach($accessLevels as $value => $label)
                    <option value="{{ $value }}" @selected(old('access_level', $document->access_level ?: 'publik') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">Publik dapat diakses umum, internal untuk akun personel, terbatas hanya admin/super admin.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="bidang_subbidang">Bidang/Subbidang</label>
            <select class="form-select" id="bidang_subbidang" name="bidang_subbidang" required>
                <option value="">Pilih bidang</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->code }}" @selected(old('bidang_subbidang', $document->bidang_subbidang) === $division->code)>{{ $division->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Pilih unit pengampu metadata sesuai struktur Bidkum. Pilihan dapat dikelola dari menu Bidang/Subbidang.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="document_version">Versi Dokumen</label>
            <input class="form-control" id="document_version" name="document_version" value="{{ old('document_version', $document->document_version ?: '1.0') }}" placeholder="Contoh: 1.0 / Revisi 2026" required>
            <div class="form-text">Gunakan versi untuk membedakan dokumen awal, revisi, atau pembaruan.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="last_reviewed_at">Terakhir Direview</label>
            <input class="form-control" id="last_reviewed_at" type="date" name="last_reviewed_at" value="{{ old('last_reviewed_at', optional($document->last_reviewed_at)->format('Y-m-d')) }}">
            <div class="form-text">Kosongkan untuk memakai tanggal hari ini.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="keywords">Kata Kunci</label>
            <input class="form-control" id="keywords" name="keywords" value="{{ old('keywords', $document->keywords) }}" placeholder="{{ $keywordsPlaceholder }}" required>
            <div class="form-text">Minimal 3 kata kunci unik, dipisahkan koma.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="summary">Ringkasan Dokumen</label>
            <textarea class="form-control" id="summary" name="summary" rows="3" minlength="20" placeholder="{{ $summaryPlaceholder }}" required>{{ old('summary', $document->summary) }}</textarea>
            <div class="form-text">Tulis inti dokumen dalam 1-3 kalimat agar pengguna cepat memahami isi dokumen.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="abstract">Abstrak</label>
            <textarea class="form-control" id="abstract" name="abstract" rows="4" placeholder="{{ $abstractPlaceholder }}">{{ old('abstract', $document->abstract) }}</textarea>
            <div class="form-text">Opsional, gunakan untuk uraian yang lebih panjang dibanding ringkasan.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="legal_basis">Dasar Hukum</label>
            <textarea class="form-control" id="legal_basis" name="legal_basis" rows="4" placeholder="{{ $legalBasisPlaceholder }}">{{ old('legal_basis', $document->legal_basis) }}</textarea>
            <div class="form-text">{{ $legalBasisHelp }}</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="related_regulation">Regulasi Terkait</label>
            <textarea class="form-control" id="related_regulation" name="related_regulation" rows="3" placeholder="{{ $relatedRegulationPlaceholder }}">{{ old('related_regulation', $document->related_regulation) }}</textarea>
            <div class="form-text">{{ $relatedRegulationHelp }}</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="file">File PDF</label>
            <input class="form-control" id="file" type="file" name="file" accept="application/pdf" @required(! $isEdit)>
            <div class="form-text">Hanya PDF, maksimum 20 MB. File disimpan secara private.</div>
            <div class="form-text">{{ $fileNameHelp }}</div>
            @if($isEdit && $document->file_path)
                <div class="form-text">File lama tetap dipakai jika tidak memilih file baru.</div>
            @endif
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection
