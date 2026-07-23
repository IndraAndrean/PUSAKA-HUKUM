@extends('layouts.admin')

@section('title', 'Profil Instansi')
@section('page_title', 'Identitas dan Profil Instansi')

@section('page_actions')
    <a class="btn btn-outline-secondary" href="{{ route('organization-profile.show') }}" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i> Lihat Halaman Publik
    </a>
@endsection

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ route('admin.organization-profile.update') }}">
    @csrf
    @method('put')

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-3">Identitas Portal</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="portal_name">Nama Portal</label>
                <input class="form-control" id="portal_name" name="portal_name" value="{{ old('portal_name', $profile->portal_name) }}" placeholder="Contoh: SIPAKEM" required>
                <div class="form-text">Nama singkat yang tampil pada navbar, footer, dan judul halaman.</div>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="portal_full_name">Kepanjangan Nama Portal</label>
                <input class="form-control" id="portal_full_name" name="portal_full_name" value="{{ old('portal_full_name', $profile->portal_full_name) }}" placeholder="Contoh: Pusat Akses Pengetahuan dan Kajian Hukum" required>
                <div class="form-text">Gunakan kepanjangan resmi sesuai konsep proyek perubahan.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="organization_name">Nama Instansi Pengelola</label>
                <input class="form-control" id="organization_name" name="organization_name" value="{{ old('organization_name', $profile->organization_name) }}" placeholder="Contoh: Bidang Hukum dan HAM Polda Lampung" required>
                <div class="form-text">Nama instansi ini menjadi identitas resmi pengelola portal.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tagline">Tagline</label>
                <input class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $profile->tagline) }}" placeholder="Contoh: Satu akses untuk semua pengetahuan hukum" required>
                <div class="form-text">Tagline singkat yang menjelaskan manfaat utama portal.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="eyebrow">Teks Pengantar Beranda</label>
                <input class="form-control" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $profile->eyebrow) }}" placeholder="Contoh: Pusat Akses Pengetahuan dan Kajian Hukum Bidkum Polda Lampung">
                <div class="form-text">Teks kecil di atas judul beranda untuk memperjelas konteks portal.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="hero_description">Deskripsi Singkat Beranda</label>
                <textarea class="form-control" id="hero_description" name="hero_description" rows="3" placeholder="Contoh: Portal digital untuk mengakses produk hukum, referensi perpustakaan, artikel edukasi, FAQ, dan layanan konsultasi hukum." required>{{ old('hero_description', $profile->hero_description) }}</textarea>
                <div class="form-text">Deskripsi ini tampil di hero beranda, jadi buat ringkas dan mudah dipahami pengguna.</div>
            </div>
        </div>
    </div>

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-3">Profil dan Tujuan</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="about">Tentang SIPAKEM</label>
                <textarea class="form-control" id="about" name="about" rows="5" placeholder="Jelaskan latar belakang, tujuan, dan cakupan layanan SIPAKEM sebagai portal pengetahuan hukum Bidkum Polda Lampung." required>{{ old('about', $profile->about) }}</textarea>
                <div class="form-text">Gunakan narasi resmi, namun tetap mudah dibaca oleh personel dan masyarakat.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="institution_duties">Tugas dan Fungsi Instansi</label>
                <textarea class="form-control" id="institution_duties" name="institution_duties" rows="5" placeholder="Contoh: Menyelenggarakan fungsi hukum, bantuan hukum, penyuluhan hukum, dan pengelolaan referensi hukum di lingkungan Polda Lampung." required>{{ old('institution_duties', $profile->institution_duties) }}</textarea>
                <div class="form-text">Isi sesuai tugas dan fungsi Bidkum agar halaman profil selaras dengan dokumen instansi.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="general_goal">Tujuan Umum</label>
                <textarea class="form-control" id="general_goal" name="general_goal" rows="4" placeholder="Contoh: Meningkatkan akses, literasi, dan pemanfaatan informasi hukum secara digital, cepat, aman, dan terdokumentasi." required>{{ old('general_goal', $profile->general_goal) }}</textarea>
                <div class="form-text">Tujuan umum menjadi dasar penjelasan manfaat portal kepada pengguna.</div>
            </div>
        </div>
    </div>

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-1">Layanan, Manfaat, dan Nilai</h2>
        <p class="text-muted small mb-3">Tuliskan satu item pada setiap baris.</p>
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label" for="services_text">Layanan Terintegrasi</label>
                <textarea class="form-control" id="services_text" name="services_text" rows="8" placeholder="Produk Hukum&#10;Perpustakaan Digital Hukum&#10;Knowledge Center&#10;FAQ Hukum&#10;Konsultasi Informasi Hukum" required>{{ old('services_text', implode("\n", $profile->services ?? [])) }}</textarea>
                <div class="form-text">Satu layanan per baris. Urutan ini dapat digunakan pada halaman profil dan footer.</div>
            </div>
            <div class="col-lg-6">
                <label class="form-label" for="organization_values_text">Nilai Organisasi</label>
                <textarea class="form-control" id="organization_values_text" name="organization_values_text" rows="8" placeholder="Profesional&#10;Akuntabel&#10;Transparan&#10;Responsif&#10;Berbasis Data" required>{{ old('organization_values_text', implode("\n", $profile->organization_values ?? [])) }}</textarea>
                <div class="form-text">Satu nilai per baris, gunakan nilai yang mendukung pelayanan digital instansi.</div>
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="benefits_organization_text">Manfaat bagi Organisasi</label>
                <textarea class="form-control" id="benefits_organization_text" name="benefits_organization_text" rows="7" required>{{ old('benefits_organization_text', implode("\n", $profile->benefits_organization ?? [])) }}</textarea>
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="benefits_personnel_text">Manfaat bagi Personel</label>
                <textarea class="form-control" id="benefits_personnel_text" name="benefits_personnel_text" rows="7" required>{{ old('benefits_personnel_text', implode("\n", $profile->benefits_personnel ?? [])) }}</textarea>
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="benefits_public_text">Manfaat bagi Masyarakat</label>
                <textarea class="form-control" id="benefits_public_text" name="benefits_public_text" rows="7" required>{{ old('benefits_public_text', implode("\n", $profile->benefits_public ?? [])) }}</textarea>
            </div>
        </div>
    </div>

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-1">Logo dan Kontak Resmi</h2>
        <p class="text-muted small mb-3">Kosongkan informasi yang belum terverifikasi. Portal tidak akan menampilkan bagian kontak yang kosong.</p>
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label" for="logo">Logo Instansi</label>
                @if($profile->logo_url)
                    <div class="border rounded-2 p-3 mb-2 bg-light text-center">
                        <img src="{{ $profile->logo_url }}" alt="Logo saat ini" style="max-width: 160px; max-height: 110px">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" id="remove_logo" type="checkbox" name="remove_logo" value="1">
                        <label class="form-check-label" for="remove_logo">Hapus logo saat ini</label>
                    </div>
                @endif
                <input class="form-control" id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg,.webp">
                <div class="form-text">PNG, JPG, atau WebP; maksimal 2 MB.</div>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="address">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Contoh: Jl. Terusan Ryacudu No.1, Way Halim, Bandar Lampung">{{ old('address', $profile->address) }}</textarea>
                        <div class="form-text">Isi alamat kantor yang boleh ditampilkan ke publik.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Telepon</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" placeholder="Contoh: (0721) 703000">
                        <div class="form-text">Gunakan nomor layanan resmi atau kosongkan jika belum diverifikasi.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email Resmi</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $profile->email) }}" placeholder="Contoh: bidkum.poldalampung@polri.go.id">
                        <div class="form-text">Email ini tampil pada footer dan halaman profil publik.</div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="website">Website Resmi</label>
                        <input class="form-control" id="website" name="website" type="url" value="{{ old('website', $profile->website) }}" placeholder="https://...">
                        <div class="form-text">Masukkan URL lengkap, misalnya https://bidkum.lampung.polri.go.id.</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="office_hours">Jam Layanan</label>
                        <input class="form-control" id="office_hours" name="office_hours" value="{{ old('office_hours', $profile->office_hours) }}" placeholder="Contoh: Senin-Jumat, 08.00-15.00 WIB">
                        <div class="form-text">Tampilkan jam layanan yang berlaku untuk konsultasi atau informasi hukum.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Profil</button>
        <a class="btn btn-outline-secondary" href="{{ route('organization-profile.show') }}" target="_blank">Lihat Publik</a>
    </div>
</form>
@endsection
