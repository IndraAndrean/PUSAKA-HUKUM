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
                <input class="form-control" id="portal_name" name="portal_name" value="{{ old('portal_name', $profile->portal_name) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="portal_full_name">Kepanjangan Nama Portal</label>
                <input class="form-control" id="portal_full_name" name="portal_full_name" value="{{ old('portal_full_name', $profile->portal_full_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="organization_name">Nama Instansi Pengelola</label>
                <input class="form-control" id="organization_name" name="organization_name" value="{{ old('organization_name', $profile->organization_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="tagline">Tagline</label>
                <input class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $profile->tagline) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="eyebrow">Teks Pengantar Beranda</label>
                <input class="form-control" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $profile->eyebrow) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="hero_description">Deskripsi Singkat Beranda</label>
                <textarea class="form-control" id="hero_description" name="hero_description" rows="3" required>{{ old('hero_description', $profile->hero_description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-3">Profil dan Tujuan</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="about">Tentang PUSAKA HUKUM</label>
                <textarea class="form-control" id="about" name="about" rows="5" required>{{ old('about', $profile->about) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="institution_duties">Tugas dan Fungsi Instansi</label>
                <textarea class="form-control" id="institution_duties" name="institution_duties" rows="5" required>{{ old('institution_duties', $profile->institution_duties) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="general_goal">Tujuan Umum</label>
                <textarea class="form-control" id="general_goal" name="general_goal" rows="4" required>{{ old('general_goal', $profile->general_goal) }}</textarea>
            </div>
        </div>
    </div>

    <div class="content-card p-4 mb-4">
        <h2 class="h5 mb-1">Layanan, Manfaat, dan Nilai</h2>
        <p class="text-muted small mb-3">Tuliskan satu item pada setiap baris.</p>
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label" for="services_text">Layanan Terintegrasi</label>
                <textarea class="form-control" id="services_text" name="services_text" rows="8" required>{{ old('services_text', implode("\n", $profile->services ?? [])) }}</textarea>
            </div>
            <div class="col-lg-6">
                <label class="form-label" for="organization_values_text">Nilai Organisasi</label>
                <textarea class="form-control" id="organization_values_text" name="organization_values_text" rows="8" required>{{ old('organization_values_text', implode("\n", $profile->organization_values ?? [])) }}</textarea>
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
                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $profile->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Telepon</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email Resmi</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $profile->email) }}">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="website">Website Resmi</label>
                        <input class="form-control" id="website" name="website" type="url" value="{{ old('website', $profile->website) }}" placeholder="https://...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="office_hours">Jam Layanan</label>
                        <input class="form-control" id="office_hours" name="office_hours" value="{{ old('office_hours', $profile->office_hours) }}">
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
