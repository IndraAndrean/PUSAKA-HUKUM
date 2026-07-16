<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Faq;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SearchFunctionalityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_document_search_finds_type_category_and_status_metadata(): void
    {
        $type = $this->documentType('Peraturan Kapolri Pencarian', 'produk_hukum');
        $category = $this->category('Bantuan Hukum Pencarian');
        $document = $this->document($type, $category, [
            'title' => 'Dokumen Operasional Teruji',
            'document_status' => 'tidak_berlaku',
        ]);

        $this->get(route('documents.index', ['q' => 'Peraturan Kapolri']))
            ->assertOk()
            ->assertSee($document->title);

        $this->get(route('documents.index', ['q' => 'Bantuan Hukum Pencarian']))
            ->assertOk()
            ->assertSee($document->title);

        $this->get(route('documents.index', ['q' => 'Tidak Berlaku']))
            ->assertOk()
            ->assertSee($document->title);
    }

    public function test_library_and_education_search_find_related_document_metadata(): void
    {
        $libraryType = $this->documentType('Jurnal Hukum Pencarian', 'perpustakaan');
        $libraryCategory = $this->category('Etik Profesi Pencarian');
        $libraryDocument = $this->document($libraryType, $libraryCategory, [
            'title' => 'Koleksi Rujukan Netral',
            'author' => 'Tim Literatur',
        ]);

        $educationType = $this->documentType('Materi Penyuluhan Pencarian', 'edukasi');
        $educationCategory = $this->category('Administrasi Edukasi Pencarian');
        $educationDocument = $this->document($educationType, $educationCategory, [
            'title' => 'Bahan Sosialisasi Netral',
        ]);

        $this->get(route('library.index', ['q' => 'Jurnal Hukum Pencarian']))
            ->assertOk()
            ->assertSee($libraryDocument->title);

        $this->get(route('library.index', ['q' => 'Etik Profesi Pencarian']))
            ->assertOk()
            ->assertSee($libraryDocument->title);

        $this->get(route('education-materials.index', ['q' => 'Materi Penyuluhan Pencarian']))
            ->assertOk()
            ->assertSee($educationDocument->title);

        $this->get(route('education-materials.index', ['q' => 'Administrasi Edukasi Pencarian']))
            ->assertOk()
            ->assertSee($educationDocument->title);
    }

    public function test_admin_document_search_uses_same_document_metadata(): void
    {
        $admin = $this->user('admin');
        $type = $this->documentType('Surat Edaran Admin Pencarian', 'produk_hukum');
        $category = $this->category('Kategori Admin Pencarian');
        $document = $this->document($type, $category, [
            'title' => 'Dokumen Admin Netral',
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.index', ['q' => 'Surat Edaran Admin Pencarian']))
            ->assertOk()
            ->assertSee($document->title);

        $this->actingAs($admin)
            ->get(route('admin.documents.index', ['q' => 'Kategori Admin Pencarian']))
            ->assertOk()
            ->assertSee($document->title);
    }

    public function test_other_search_pages_match_their_advertised_fields(): void
    {
        $admin = $this->user('admin');

        $article = Article::create([
            'title' => 'Artikel Netral Pencarian',
            'slug' => 'artikel-netral-pencarian-'.uniqid(),
            'category' => 'Edukasi Pencarian',
            'excerpt' => 'Ringkasan artikel pengujian.',
            'content' => 'Isi artikel pengujian.',
            'status' => 'published',
            'created_by' => $admin->id,
            'published_at' => now(),
        ]);

        $faq = Faq::create([
            'question' => 'Bagaimana mencari informasi?',
            'answer' => 'Gunakan kata kunci kepolisian pencarian pada kolom FAQ.',
            'category' => 'Kategori FAQ Pencarian',
            'status' => 'published',
        ]);

        $consultation = Consultation::create([
            'name' => 'Pemohon Pencarian',
            'email' => 'pemohon-search@example.test',
            'question' => 'Pertanyaan konsultasi pencarian.',
        ]);

        $user = $this->user('internal', [
            'name' => 'Personel Pencarian',
            'satuan_kerja' => 'Satker Pencarian Khusus',
        ]);

        $this->get(route('articles.index', ['q' => 'Edukasi Pencarian']))
            ->assertOk()
            ->assertSee($article->title);

        $this->get(route('faqs.index', ['q' => 'kepolisian pencarian']))
            ->assertOk()
            ->assertSee($faq->question);

        $this->actingAs($admin)
            ->get(route('admin.articles.index', ['q' => 'Edukasi Pencarian']))
            ->assertOk()
            ->assertSee($article->title);

        $this->actingAs($admin)
            ->get(route('admin.faqs.index', ['q' => 'Kategori FAQ Pencarian']))
            ->assertOk()
            ->assertSee($faq->question);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['q' => 'pemohon-search@example.test']))
            ->assertOk()
            ->assertSee($consultation->name);

        $this->actingAs($this->user('super_admin'))
            ->get(route('admin.users.index', ['q' => 'Satker Pencarian Khusus']))
            ->assertOk()
            ->assertSee($user->name);
    }

    private function documentType(string $name, string $collection): DocumentType
    {
        $suffix = uniqid();

        return DocumentType::create([
            'name' => "{$name} {$suffix}",
            'slug' => str($name)->slug().'-'.$suffix,
            'code_prefix' => 'SRCH-'.strtoupper(substr($suffix, -5)),
            'review_interval_months' => 12,
            'collection' => $collection,
        ]);
    }

    private function category(string $name): LegalCategory
    {
        $suffix = uniqid();

        return LegalCategory::create([
            'name' => "{$name} {$suffix}",
            'slug' => str($name)->slug().'-'.$suffix,
        ]);
    }

    private function document(DocumentType $type, LegalCategory $category, array $overrides = []): Document
    {
        $suffix = strtoupper(substr(uniqid(), -6));

        return Document::create([
            'document_code' => "SRCH-{$suffix}",
            'title' => "Dokumen Pencarian {$suffix}",
            'author' => 'Penulis Pencarian',
            'document_type_id' => $type->id,
            'document_number' => "NO-{$suffix}",
            'year' => 2026,
            'enacted_date' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
            'issuing_institution' => 'Bidkum Polda Lampung',
            'publisher' => 'Penerbit Pencarian',
            'document_status' => 'berlaku',
            'legal_category_id' => $category->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'hukum, pencarian, pengujian',
            'summary' => 'Ringkasan dokumen untuk pengujian pencarian seluruh fitur.',
            'document_version' => '1.0',
            'access_level' => 'publik',
            ...$overrides,
        ]);
    }

    private function user(string $role, array $overrides = []): User
    {
        $suffix = uniqid();

        return User::create([
            'name' => ucfirst($role).' Pencarian',
            'email' => "{$role}-search-{$suffix}@pusakahukum.test",
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
            ...$overrides,
        ]);
    }
}
