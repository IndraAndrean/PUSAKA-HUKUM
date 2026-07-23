<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class OrganizationProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_name',
        'portal_full_name',
        'tagline',
        'organization_name',
        'eyebrow',
        'hero_description',
        'about',
        'institution_duties',
        'general_goal',
        'services',
        'benefits_organization',
        'benefits_personnel',
        'benefits_public',
        'organization_values',
        'address',
        'phone',
        'email',
        'website',
        'office_hours',
        'logo_path',
    ];

    protected $casts = [
        'services' => 'array',
        'benefits_organization' => 'array',
        'benefits_personnel' => 'array',
        'benefits_public' => 'array',
        'organization_values' => 'array',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('organization_profiles')) {
            return new static(static::defaultAttributes());
        }

        return static::query()->first()
            ?? static::query()->create(static::defaultAttributes());
    }

    public static function defaultAttributes(): array
    {
        return [
            'portal_name' => 'SIPAKEM',
            'portal_full_name' => 'Sistem Informasi Arsip Konsultasi Edukasi dan Manajemen Hukum',
            'tagline' => 'Satu Akses untuk Semua Pengetahuan Hukum',
            'organization_name' => 'Bidang Hukum dan HAM Polda Lampung',
            'eyebrow' => 'Digitalisasi Pengetahuan Hukum Terintegrasi',
            'hero_description' => 'Pusat data, perpustakaan digital, media pembelajaran, dan layanan informasi hukum yang mudah, cepat, serta terintegrasi bagi personel Polri dan masyarakat.',
            'about' => 'SIPAKEM merupakan aplikasi berbasis web yang mengintegrasikan produk hukum, referensi, kajian, artikel edukasi, FAQ, dan konsultasi informasi hukum dalam satu platform digital yang dikelola oleh Bidang Hukum dan HAM Polda Lampung.',
            'institution_duties' => 'Bidang Hukum dan HAM Polda Lampung merupakan unsur pendukung di bawah Kapolda Lampung yang menyelenggarakan pembinaan hukum, bantuan dan pertimbangan hukum, penyuluhan hukum, serta pengkajian dan pengembangan hukum di lingkungan Polda Lampung.',
            'general_goal' => 'Mewujudkan transformasi digital pengelolaan pengetahuan hukum guna meningkatkan efektivitas, efisiensi, dan kualitas pelayanan informasi hukum di Bidang Hukum dan HAM Polda Lampung.',
            'services' => [
                'Bank Produk Hukum',
                'Perpustakaan Digital Hukum',
                'Knowledge Center dan Edukasi Hukum',
                'Pencarian Dokumen Terintegrasi',
                'Konsultasi Informasi Hukum',
                'Dashboard Monitoring',
            ],
            'benefits_organization' => [
                'Meningkatkan efektivitas pelayanan hukum.',
                'Mendukung pengambilan keputusan berbasis regulasi.',
                'Meningkatkan kualitas pembinaan hukum.',
            ],
            'benefits_personnel' => [
                'Memperoleh akses cepat terhadap produk hukum.',
                'Meningkatkan pemahaman dan kompetensi hukum.',
                'Mendukung budaya berbagi pengetahuan hukum.',
            ],
            'benefits_public' => [
                'Mendapatkan informasi hukum yang mudah, cepat, dan terpercaya.',
                'Memperluas akses terhadap edukasi hukum.',
                'Meningkatkan kesadaran hukum masyarakat.',
            ],
            'organization_values' => [
                'Berorientasi Pelayanan',
                'Akuntabel',
                'Kompeten',
                'Harmonis',
                'Loyal',
                'Adaptif',
                'Kolaboratif',
                'Prediktif',
                'Responsibilitas',
                'Transparansi Berkeadilan',
            ],
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    public function hasContactInformation(): bool
    {
        return filled($this->address)
            || filled($this->phone)
            || filled($this->email)
            || filled($this->website)
            || filled($this->office_hours);
    }
}
