@extends('layouts.admin')

@php($isEdit = $document->exists)

@section('title', $isEdit ? 'Edit Dokumen' : 'Tambah Dokumen')
@section('page_title', $isEdit ? 'Edit Dokumen' : 'Tambah Dokumen')

@section('content')
<form class="content-card p-3" method="post" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.documents.update', $document) : route('admin.documents.store') }}">
    @csrf
    @if($isEdit)
        @method('put')
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label" for="title">Judul Dokumen</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $document->title) }}" placeholder="Contoh: Peraturan Kapolri Nomor 6 Tahun 2023 tentang Penyidikan Tindak Pidana" required>
            <div class="form-text">Gunakan judul resmi sesuai naskah agar mudah ditemukan melalui pencarian.</div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="document_type_id">Jenis Dokumen</label>
            <select class="form-select" id="document_type_id" name="document_type_id" required>
                <option value="">Pilih jenis</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" data-collection="{{ $type->collection }}" @selected(old('document_type_id', $document->document_type_id) == $type->id)>
                        {{ $type->name }} - {{ $collections[$type->collection] ?? $type->collection }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Pilih jenis yang paling sesuai, misalnya produk hukum, surat edaran, juklak/juknis, kajian, atau koleksi perpustakaan.</div>
        </div>
        <div class="col-12 library-field">
            <div class="alert alert-info mb-0">
                <i class="bi bi-book me-1"></i> Metadata referensi perpustakaan menggunakan penulis, penerbit, ISBN/ISSN, dan edisi/volume. Nomor serta tanggal regulasi boleh dikosongkan.
            </div>
        </div>
        <div class="col-md-6 library-field">
            <label class="form-label" for="author">Penulis/Penyusun</label>
            <input class="form-control" id="author" name="author" value="{{ old('author', $document->author) }}" placeholder="Contoh: Bidkum Polda Lampung / Tim Penyusun">
            <div class="form-text">Wajib untuk koleksi perpustakaan digital seperti buku, jurnal, modul, atau kajian.</div>
        </div>
        <div class="col-md-6 library-field">
            <label class="form-label" for="publisher">Penerbit</label>
            <input class="form-control" id="publisher" name="publisher" value="{{ old('publisher', $document->publisher) }}" placeholder="Contoh: Polda Lampung / Mabes Polri / Jurnal Hukum">
            <div class="form-text">Isi nama lembaga atau penerbit yang menerbitkan bahan referensi.</div>
        </div>
        <div class="col-md-6 library-field">
            <label class="form-label" for="isbn_issn">ISBN/ISSN</label>
            <input class="form-control" id="isbn_issn" name="isbn_issn" value="{{ old('isbn_issn', $document->isbn_issn) }}" placeholder="Contoh: 978-602-1234-56-7 / 2045-7788">
            <div class="form-text">Boleh dikosongkan jika dokumen tidak memiliki nomor ISBN atau ISSN.</div>
        </div>
        <div class="col-md-6 library-field">
            <label class="form-label" for="edition_volume">Edisi/Volume</label>
            <input class="form-control" id="edition_volume" name="edition_volume" value="{{ old('edition_volume', $document->edition_volume) }}" placeholder="Contoh: Edisi 2 / Vol. 5 No. 1">
            <div class="form-text">Gunakan untuk buku, jurnal, majalah hukum, atau seri kajian berkala.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="document_number">Nomor Dokumen</label>
            <input class="form-control" id="document_number" name="document_number" value="{{ old('document_number', $document->document_number) }}" placeholder="Contoh: 6, SE/2/V/2024, B/123/VI/HUK.1.1./2026" required>
            <div class="form-text">Isi nomor resmi dokumen. Untuk koleksi perpustakaan, boleh dikosongkan.</div>
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
            <input class="form-control" id="enacted_date" type="date" name="enacted_date" value="{{ old('enacted_date', optional($document->enacted_date)->format('Y-m-d')) }}" required>
            <div class="form-text">Tanggal dokumen ditetapkan, ditandatangani, atau diterbitkan.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="effective_date">Tanggal Berlaku</label>
            <input class="form-control" id="effective_date" type="date" name="effective_date" value="{{ old('effective_date', optional($document->effective_date)->format('Y-m-d')) }}" required>
            <div class="form-text">Tanggal mulai berlaku. Jika sama dengan tanggal penetapan, isi tanggal yang sama.</div>
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
                @foreach($subfields as $value => $label)
                    <option value="{{ $value }}" @selected(old('bidang_subbidang', $document->bidang_subbidang) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">Pilih unit pengampu metadata sesuai struktur Bidkum.</div>
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
            <input class="form-control" id="keywords" name="keywords" value="{{ old('keywords', $document->keywords) }}" placeholder="pidana, kepolisian, penyidikan" required>
            <div class="form-text">Minimal 3 kata kunci unik, dipisahkan koma.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="summary">Ringkasan Dokumen</label>
            <textarea class="form-control" id="summary" name="summary" rows="3" minlength="20" placeholder="Contoh: Dokumen ini mengatur pedoman penyidikan tindak pidana di lingkungan Kepolisian Negara Republik Indonesia." required>{{ old('summary', $document->summary) }}</textarea>
            <div class="form-text">Tulis inti dokumen dalam 1-3 kalimat agar pengguna cepat memahami isi dokumen.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="abstract">Abstrak</label>
            <textarea class="form-control" id="abstract" name="abstract" rows="4" placeholder="Contoh: Memuat ruang lingkup, maksud, tujuan, dan pokok pengaturan dokumen.">{{ old('abstract', $document->abstract) }}</textarea>
            <div class="form-text">Opsional, gunakan untuk uraian yang lebih panjang dibanding ringkasan.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="legal_basis">Dasar Hukum</label>
            <textarea class="form-control" id="legal_basis" name="legal_basis" rows="4" placeholder="Contoh: Undang-Undang Nomor 2 Tahun 2002 tentang Kepolisian Negara Republik Indonesia.">{{ old('legal_basis', $document->legal_basis) }}</textarea>
            <div class="form-text">Cantumkan dasar hukum utama yang menjadi rujukan dokumen.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="related_regulation">Regulasi Terkait</label>
            <textarea class="form-control" id="related_regulation" name="related_regulation" rows="3" placeholder="Contoh: Perkap Nomor ..., Surat Edaran Kapolri Nomor ..., atau dokumen pendukung lain.">{{ old('related_regulation', $document->related_regulation) }}</textarea>
            <div class="form-text">Isi regulasi yang berkaitan agar relasi penelusuran dokumen lebih kuat.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="file">File PDF</label>
            <input class="form-control" id="file" type="file" name="file" accept="application/pdf" @required(! $isEdit)>
            <div class="form-text">Hanya PDF, maksimum 20 MB. File disimpan secara private.</div>
            <div class="form-text">Nama file dibuat otomatis mengikuti format: JENIS_NOMOR_TAHUN_JUDUL.pdf.</div>
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

@push('scripts')
<script>
    const typeSelect = document.getElementById('document_type_id');
    const libraryFields = document.querySelectorAll('.library-field');
    const authorInput = document.getElementById('author');
    const publisherInput = document.getElementById('publisher');
    const documentNumberInput = document.getElementById('document_number');
    const enactedDateInput = document.getElementById('enacted_date');
    const effectiveDateInput = document.getElementById('effective_date');

    function updateCollectionFields() {
        const option = typeSelect.options[typeSelect.selectedIndex];
        const isLibrary = option?.dataset.collection === 'perpustakaan';

        libraryFields.forEach(field => field.classList.toggle('d-none', !isLibrary));
        authorInput.required = isLibrary;
        publisherInput.required = isLibrary;
        documentNumberInput.required = !isLibrary;
        enactedDateInput.required = !isLibrary;
        effectiveDateInput.required = !isLibrary;
        window.Pusaka?.updateRequiredIndicators?.();
    }

    typeSelect.addEventListener('change', updateCollectionFields);
    updateCollectionFields();
</script>
@endpush
