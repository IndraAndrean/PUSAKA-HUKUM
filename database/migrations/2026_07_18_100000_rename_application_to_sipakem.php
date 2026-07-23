<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organization_profiles')) {
            return;
        }

        DB::table('organization_profiles')
            ->where('portal_name', 'PUSAKA HUKUM')
            ->update([
                'portal_name' => 'SIPAKEM',
                'portal_full_name' => 'Sistem Informasi Arsip Konsultasi Edukasi dan Manajemen Hukum',
                'organization_name' => 'Bidang Hukum dan HAM Polda Lampung',
                'about' => 'SIPAKEM merupakan aplikasi berbasis web yang mengintegrasikan produk hukum, referensi, kajian, artikel edukasi, FAQ, dan konsultasi informasi hukum dalam satu platform digital yang dikelola oleh Bidang Hukum dan HAM Polda Lampung.',
                'institution_duties' => 'Bidang Hukum dan HAM Polda Lampung merupakan unsur pendukung di bawah Kapolda Lampung yang menyelenggarakan pembinaan hukum, bantuan dan pertimbangan hukum, penyuluhan hukum, serta pengkajian dan pengembangan hukum di lingkungan Polda Lampung.',
                'general_goal' => 'Mewujudkan transformasi digital pengelolaan pengetahuan hukum guna meningkatkan efektivitas, efisiensi, dan kualitas pelayanan informasi hukum di Bidang Hukum dan HAM Polda Lampung.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('organization_profiles')) {
            return;
        }

        DB::table('organization_profiles')
            ->where('portal_name', 'SIPAKEM')
            ->update([
                'portal_name' => 'PUSAKA HUKUM',
                'portal_full_name' => 'Pusat Akses Pengetahuan dan Kajian Hukum',
                'organization_name' => 'Bidang Hukum Polda Lampung',
                'about' => 'PUSAKA HUKUM merupakan aplikasi berbasis web yang mengintegrasikan produk hukum, referensi, kajian, artikel edukasi, FAQ, dan konsultasi informasi hukum dalam satu platform digital yang dikelola oleh Bidang Hukum Polda Lampung.',
                'institution_duties' => 'Bidang Hukum Polda Lampung merupakan unsur pendukung di bawah Kapolda Lampung yang menyelenggarakan pembinaan hukum, bantuan dan pertimbangan hukum, penyuluhan hukum, serta pengkajian dan pengembangan hukum di lingkungan Polda Lampung.',
                'general_goal' => 'Mewujudkan transformasi digital pengelolaan pengetahuan hukum guna meningkatkan efektivitas, efisiensi, dan kualitas pelayanan informasi hukum di Bidang Hukum Polda Lampung.',
                'updated_at' => now(),
            ]);
    }
};
