<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('portal_name', 100);
            $table->string('portal_full_name', 255);
            $table->string('tagline', 255);
            $table->string('organization_name', 255);
            $table->string('eyebrow', 255)->nullable();
            $table->text('hero_description');
            $table->longText('about');
            $table->longText('institution_duties');
            $table->longText('general_goal');
            $table->json('services');
            $table->json('benefits_organization');
            $table->json('benefits_personnel');
            $table->json('benefits_public');
            $table->json('organization_values');
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('office_hours', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        DB::table('organization_profiles')->insert([
            'id' => 1,
            'portal_name' => 'SIPAKEM',
            'portal_full_name' => 'Sistem Informasi Arsip Konsultasi Edukasi dan Manajemen Hukum',
            'tagline' => 'Satu Akses untuk Semua Pengetahuan Hukum',
            'organization_name' => 'Bidang Hukum dan HAM Polda Lampung',
            'eyebrow' => 'Digitalisasi Pengetahuan Hukum Terintegrasi',
            'hero_description' => 'Pusat data, perpustakaan digital, media pembelajaran, dan layanan informasi hukum yang mudah, cepat, serta terintegrasi bagi personel Polri dan masyarakat.',
            'about' => 'SIPAKEM merupakan aplikasi berbasis web yang mengintegrasikan produk hukum, referensi, kajian, artikel edukasi, FAQ, dan konsultasi informasi hukum dalam satu platform digital yang dikelola oleh Bidang Hukum dan HAM Polda Lampung.',
            'institution_duties' => 'Bidang Hukum dan HAM Polda Lampung merupakan unsur pendukung di bawah Kapolda Lampung yang menyelenggarakan pembinaan hukum, bantuan dan pertimbangan hukum, penyuluhan hukum, serta pengkajian dan pengembangan hukum di lingkungan Polda Lampung.',
            'general_goal' => 'Mewujudkan transformasi digital pengelolaan pengetahuan hukum guna meningkatkan efektivitas, efisiensi, dan kualitas pelayanan informasi hukum di Bidang Hukum dan HAM Polda Lampung.',
            'services' => json_encode([
                'Bank Produk Hukum',
                'Perpustakaan Digital Hukum',
                'Knowledge Center dan Edukasi Hukum',
                'Pencarian Dokumen Terintegrasi',
                'Konsultasi Informasi Hukum',
                'Dashboard Monitoring',
            ]),
            'benefits_organization' => json_encode([
                'Meningkatkan efektivitas pelayanan hukum.',
                'Mendukung pengambilan keputusan berbasis regulasi.',
                'Meningkatkan kualitas pembinaan hukum.',
            ]),
            'benefits_personnel' => json_encode([
                'Memperoleh akses cepat terhadap produk hukum.',
                'Meningkatkan pemahaman dan kompetensi hukum.',
                'Mendukung budaya berbagi pengetahuan hukum.',
            ]),
            'benefits_public' => json_encode([
                'Mendapatkan informasi hukum yang mudah, cepat, dan terpercaya.',
                'Memperluas akses terhadap edukasi hukum.',
                'Meningkatkan kesadaran hukum masyarakat.',
            ]),
            'organization_values' => json_encode([
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
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_profiles');
    }
};
