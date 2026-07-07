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
            ['email' => 'superadmin@pusakahukum.test'],
            [
                'name' => 'Super Admin PUSAKA',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@pusakahukum.test'],
            [
                'name' => 'Admin Pengelola',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'internal@pusakahukum.test'],
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
            ['name' => 'Surat Edaran', 'code_prefix' => 'SE', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Petunjuk Pelaksanaan', 'code_prefix' => 'JUKLAK', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Petunjuk Teknis', 'code_prefix' => 'JUKNIS', 'review_interval_months' => 3, 'collection' => 'produk_hukum'],
            ['name' => 'Kajian Hukum', 'code_prefix' => 'KAJ', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
            ['name' => 'Legal Opinion', 'code_prefix' => 'LO', 'review_interval_months' => 0, 'collection' => 'perpustakaan'],
            ['name' => 'Materi Penyuluhan', 'code_prefix' => 'SUNLUH', 'review_interval_months' => 6, 'collection' => 'edukasi'],
            ['name' => 'Buku Hukum', 'code_prefix' => 'LIB-BUKU', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
            ['name' => 'Jurnal Hukum', 'code_prefix' => 'LIB-JURNAL', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
            ['name' => 'Naskah Akademik', 'code_prefix' => 'LIB-NA', 'review_interval_months' => 12, 'collection' => 'perpustakaan'],
            ['name' => 'Yurisprudensi', 'code_prefix' => 'LIB-YURIS', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
            ['name' => 'Putusan Praperadilan', 'code_prefix' => 'PRAP', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
            ['name' => 'Telaahan Hukum', 'code_prefix' => 'TEL', 'review_interval_months' => 6, 'collection' => 'perpustakaan'],
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
            ['document_code' => 'PUSAKA-0001'],
            [
                'title' => 'Contoh Produk Hukum Internal',
                'document_type_id' => $type?->id,
                'document_number' => '1',
                'year' => 2026,
                'issuing_institution' => 'Bidkum Polda Lampung',
                'document_status' => 'berlaku',
                'legal_category_id' => $category?->id,
                'keywords' => 'contoh, hukum, internal',
                'summary' => 'Contoh data awal untuk demonstrasi daftar dokumen PUSAKA HUKUM.',
                'access_level' => 'internal',
                'uploaded_by' => $superAdmin->id,
            ]
        );

        Article::updateOrCreate(
            ['slug' => 'mengenal-pusaka-hukum'],
            [
                'title' => 'Mengenal PUSAKA HUKUM',
                'category' => 'Edukasi',
                'excerpt' => 'PUSAKA HUKUM menjadi pusat akses pengetahuan dan kajian hukum Bidkum Polda Lampung.',
                'content' => 'PUSAKA HUKUM dirancang untuk mengintegrasikan dokumen, referensi, artikel, FAQ, dan layanan informasi hukum dalam satu portal digital.',
                'status' => 'published',
                'created_by' => $superAdmin->id,
                'published_at' => now(),
            ]
        );

        Faq::updateOrCreate(
            ['question' => 'Apa itu PUSAKA HUKUM?'],
            [
                'answer' => 'PUSAKA HUKUM adalah portal digital untuk mengelola dan mencari dokumen serta pengetahuan hukum Bidkum Polda Lampung.',
                'category' => 'Umum',
                'status' => 'published',
            ]
        );
    }
}
