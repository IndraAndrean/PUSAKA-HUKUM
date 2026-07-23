<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sipakem.test'],
            [
                'name' => 'Super Admin SIPAKEM',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@sipakem.test'],
            [
                'name' => 'Admin Pengelola',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'internal@sipakem.test'],
            [
                'name' => 'User Internal',
                'password' => Hash::make('password'),
                'role' => 'internal',
                'is_active' => true,
            ]
        );

        $types = [
            ['name' => 'Undang-Undang', 'code_prefix' => 'REG-UU', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Peraturan Pemerintah', 'code_prefix' => 'REG-PP', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Peraturan Presiden', 'code_prefix' => 'REG-PERPRES', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Peraturan Kapolri', 'code_prefix' => 'POL-PERKAP', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Peraturan Kapolda', 'code_prefix' => 'PERKAPOLDA', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Keputusan Kabidkum', 'code_prefix' => 'KEP-KABIDKUM', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Surat Edaran', 'code_prefix' => 'SE', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Petunjuk Pelaksanaan', 'code_prefix' => 'JUKLAK', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Petunjuk Teknis', 'code_prefix' => 'JUKNIS', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Kajian Hukum', 'code_prefix' => 'KAJ', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
            ['name' => 'Materi Penyuluhan', 'code_prefix' => 'SUNLUH', 'review_interval_months' => 6, 'collection' => 'edukasi'],
            ['name' => 'Buku Hukum', 'code_prefix' => 'LIB-BUKU', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
            ['name' => 'Jurnal Hukum', 'code_prefix' => 'LIB-JURNAL', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
            ['name' => 'Putusan Praperadilan', 'code_prefix' => 'PRAP', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
            ['name' => 'Best Practice Hukum', 'code_prefix' => 'LIB-BP', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
        ];
        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                $type
            );
        }

        $categories = ['Pidana', 'Perdata', 'Administrasi', 'Kepegawaian', 'Lalu Lintas', 'Bantuan Hukum', 'Etik Profesi', 'Penyuluhan Hukum'];
        foreach ($categories as $category) {
            LegalCategory::updateOrCreate(
                ['slug' => Str::slug($category)],
                ['name' => $category]
            );
        }

        $type = DocumentType::where('slug', 'peraturan-kapolri')->first();
        $category = LegalCategory::where('slug', 'administrasi')->first();

        Document::updateOrCreate(
            ['document_code' => 'SIPAKEM-0001'],
            [
                'title' => 'Contoh Produk Hukum Internal',
                'document_type_id' => $type?->id,
                'document_number' => '1',
                'year' => 2026,
                'issuing_institution' => 'Bidkum Polda Lampung',
                'document_status' => 'berlaku',
                'legal_category_id' => $category?->id,
                'keywords' => 'contoh, hukum, internal',
                'summary' => 'Contoh data awal untuk demonstrasi daftar dokumen SIPAKEM.',
                'access_level' => 'internal',
                'uploaded_by' => $superAdmin->id,
            ]
        );

        Article::updateOrCreate(
            ['slug' => 'mengenal-sipakem'],
            [
                'title' => 'Mengenal SIPAKEM',
                'category' => 'Edukasi',
                'excerpt' => 'SIPAKEM menjadi pusat akses pengetahuan dan kajian hukum Bidang Hukum dan HAM Polda Lampung.',
                'content' => 'SIPAKEM dirancang untuk mengintegrasikan dokumen, referensi, artikel, FAQ, dan layanan informasi hukum dalam satu portal digital.',
                'status' => 'published',
                'created_by' => $superAdmin->id,
                'published_at' => now(),
            ]
        );

        Faq::updateOrCreate(
            ['question' => 'Apa itu SIPAKEM?'],
            [
                'answer' => 'SIPAKEM adalah portal digital untuk mengelola dan mencari dokumen serta pengetahuan hukum Bidang Hukum dan HAM Polda Lampung.',
                'category' => 'Umum',
                'status' => 'published',
            ]
        );
    }
}
