<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\DocumentDivision;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_manage_operational_content_but_not_users(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.faqs.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.consultations.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_bulk_import_document_feature_is_not_available(): void
    {
        $admin = User::create([
            'name' => 'Admin Tanpa Import',
            'email' => 'admin-tanpa-import@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.index'))
            ->assertOk()
            ->assertDontSee('Import Massal')
            ->assertDontSee('Import</a>', false);

        $this->actingAs($admin)->get('/admin/import-dokumen')->assertNotFound();
        $this->actingAs($admin)->post('/admin/import-dokumen')->assertNotFound();
        $this->actingAs($admin)->get('/admin/import-dokumen/template/csv')->assertNotFound();
        $this->actingAs($admin)->get('/admin/import-dokumen/template/xlsx')->assertNotFound();
        $this->actingAs($admin)->get('/admin/document-imports')->assertNotFound();
    }

    public function test_document_create_flow_is_split_by_collection(): void
    {
        $admin = User::create([
            'name' => 'Admin Koleksi Dokumen',
            'email' => 'admin-koleksi-dokumen@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $lawType = DocumentType::create([
            'name' => 'Produk Hukum Pilihan',
            'slug' => 'produk-hukum-pilihan',
            'code_prefix' => 'REG-PILIH',
            'review_interval_months' => 3,
            'collection' => 'produk_hukum',
        ]);
        $libraryType = DocumentType::create([
            'name' => 'Perpustakaan Pilihan',
            'slug' => 'perpustakaan-pilihan',
            'code_prefix' => 'LIB-PILIH',
            'review_interval_months' => 6,
            'collection' => 'perpustakaan',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.create'))
            ->assertOk()
            ->assertSee('Bank Produk Hukum')
            ->assertSee('Perpustakaan Digital')
            ->assertSee('Materi Edukasi');

        $this->actingAs($admin)
            ->get(route('admin.documents.create', ['collection' => 'produk_hukum']))
            ->assertOk()
            ->assertSee($lawType->name)
            ->assertDontSee($libraryType->name);
    }

    public function test_admin_can_upload_education_material_without_regulation_number_or_dates(): void
    {
        Storage::fake('documents');

        $admin = User::create([
            'name' => 'Admin Materi Edukasi',
            'email' => 'admin-materi-edukasi@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $type = DocumentType::create([
            'name' => 'Materi Edukasi Pengujian',
            'slug' => 'materi-edukasi-pengujian',
            'code_prefix' => 'SUNLUH-UJI',
            'review_interval_months' => 6,
            'collection' => 'edukasi',
        ]);
        $category = LegalCategory::create([
            'name' => 'Kategori Edukasi Pengujian',
            'slug' => 'kategori-edukasi-pengujian',
        ]);

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            'title' => 'Materi Penyuluhan Bantuan Hukum',
            'author' => 'Tim Sunluhkum',
            'document_type_id' => $type->id,
            'document_number' => '',
            'year' => 2026,
            'enacted_date' => '',
            'effective_date' => '',
            'issuing_institution' => 'Bidkum Polda Lampung',
            'publisher' => '',
            'isbn_issn' => '',
            'edition_volume' => '',
            'document_status' => 'berlaku',
            'legal_category_id' => $category->id,
            'bidang_subbidang' => 'sunluhkum',
            'keywords' => 'penyuluhan, bantuan hukum, edukasi',
            'summary' => 'Materi edukasi untuk penyuluhan bantuan hukum kepada personel dan masyarakat.',
            'document_version' => '1.0',
            'access_level' => 'publik',
            'file' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.documents.index'));

        $document = Document::where('title', 'Materi Penyuluhan Bantuan Hukum')->firstOrFail();

        $this->assertNull($document->document_number);
        $this->assertNull($document->enacted_date);
        $this->assertSame(100, $document->metadata_completeness);
    }

    public function test_admin_can_create_article_and_faq(): void
    {
        $admin = User::create([
            'name' => 'Admin Konten',
            'email' => 'admin-konten@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Pengujian Portal',
            'category' => 'Edukasi',
            'excerpt' => 'Ringkasan artikel pengujian.',
            'content' => 'Isi artikel pengujian portal SIPAKEM.',
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::where('title', 'Artikel Pengujian Portal')->firstOrFail();
        $this->get(route('articles.show', $article->slug))->assertOk();

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Apakah FAQ pengujian tersimpan?',
            'answer' => 'Ya, FAQ pengujian berhasil tersimpan.',
            'category' => 'Pengujian',
            'status' => 'published',
        ])->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', [
            'question' => 'Apakah FAQ pengujian tersimpan?',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_answer_consultation(): void
    {
        $admin = User::create([
            'name' => 'Admin Konsultasi',
            'email' => 'admin-konsultasi@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'name' => 'Pengguna Uji',
            'email' => 'pengguna-uji@example.test',
            'question' => 'Di mana saya dapat menemukan dokumen hukum?',
        ]);

        $this->actingAs($admin)->put(route('admin.consultations.update', $consultation), [
            'answer' => 'Silakan gunakan menu Dokumen dan kolom pencarian.',
            'status' => 'dijawab',
        ])->assertRedirect(route('admin.consultations.show', $consultation));

        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'status' => 'dijawab',
            'answered_by' => $admin->id,
        ]);
    }

    public function test_super_admin_can_create_user(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'super-test@pusakahukum.test',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Personel Internal Test',
            'email' => 'personel-test@pusakahukum.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'internal',
            'satuan_kerja' => 'Polres Pengujian',
            'jabatan' => 'Operator',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'personel-test@pusakahukum.test',
            'role' => 'internal',
            'satuan_kerja' => 'Polres Pengujian',
        ]);
    }

    public function test_admin_can_manage_document_types_legal_categories_and_document_divisions(): void
    {
        $admin = User::create([
            'name' => 'Admin Master Data',
            'email' => 'admin-master@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.document-types.store'), [
            'name' => 'Peraturan Pengujian',
            'code_prefix' => 'REG-TEST',
            'review_interval_months' => 3,
            'description' => 'Jenis dokumen untuk pengujian.',
        ])->assertRedirect(route('admin.document-types.index'));

        $this->actingAs($admin)->post(route('admin.legal-categories.store'), [
            'name' => 'Kategori Pengujian',
            'description' => 'Kategori hukum untuk pengujian.',
        ])->assertRedirect(route('admin.legal-categories.index'));

        $this->actingAs($admin)->post(route('admin.document-divisions.store'), [
            'name' => 'Subbidang Pengujian',
            'code' => 'subbid-test',
            'description' => 'Bidang/subbidang untuk pengujian.',
        ])->assertRedirect(route('admin.document-divisions.index'));

        $type = DocumentType::where('name', 'Peraturan Pengujian')->firstOrFail();
        $category = LegalCategory::where('name', 'Kategori Pengujian')->firstOrFail();
        $division = DocumentDivision::where('name', 'Subbidang Pengujian')->firstOrFail();

        $this->assertSame('peraturan-pengujian', $type->slug);
        $this->assertSame('kategori-pengujian', $category->slug);
        $this->assertSame('subbidang-pengujian', $division->slug);

        $this->actingAs($admin)->delete(route('admin.document-types.destroy', $type))
            ->assertSessionHas('success');
        $this->actingAs($admin)->delete(route('admin.legal-categories.destroy', $category))
            ->assertSessionHas('success');
        $this->actingAs($admin)->delete(route('admin.document-divisions.destroy', $division))
            ->assertSessionHas('success');
    }

    public function test_used_master_data_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin Proteksi Master',
            'email' => 'admin-proteksi-master@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $type = DocumentType::create([
            'name' => 'Jenis Terpakai',
            'slug' => 'jenis-terpakai',
            'code_prefix' => 'USED-TYPE',
            'review_interval_months' => 3,
        ]);
        $category = LegalCategory::create([
            'name' => 'Kategori Terpakai',
            'slug' => 'kategori-terpakai',
        ]);
        $division = DocumentDivision::create([
            'name' => 'Bidang Terpakai',
            'slug' => 'bidang-terpakai',
            'code' => 'bidang-terpakai',
        ]);

        Document::create([
            'document_code' => 'TEST-MASTER-001',
            'title' => 'Dokumen Proteksi Master Data',
            'document_type_id' => $type->id,
            'legal_category_id' => $category->id,
            'bidang_subbidang' => $division->code,
            'document_status' => 'berlaku',
            'access_level' => 'publik',
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->delete(route('admin.document-types.destroy', $type))
            ->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.legal-categories.destroy', $category))
            ->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.document-divisions.destroy', $division))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('document_types', ['id' => $type->id]);
        $this->assertDatabaseHas('legal_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('document_divisions', ['id' => $division->id]);
    }

    public function test_admin_dashboard_contains_activity_statistics(): void
    {
        $admin = User::create([
            'name' => 'Admin Statistik',
            'email' => 'admin-statistik@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas Enam Bulan Terakhir')
            ->assertSee('Dokumen Paling Banyak Diunduh');
    }
}
