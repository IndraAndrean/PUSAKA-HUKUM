<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalLawLibraryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_library_page_only_lists_visible_library_collections(): void
    {
        $libraryType = $this->type('Buku Hukum Uji', 'LIB-UJI', 'perpustakaan');
        $lawType = $this->type('Produk Hukum Uji', 'REG-UJI', 'produk_hukum');
        $publicLibrary = $this->document($libraryType, 'LIB-PUBLIC', 'Buku Hukum Publik', 'publik');
        $internalLibrary = $this->document($libraryType, 'LIB-INTERNAL', 'Jurnal Hukum Internal', 'internal');
        $law = $this->document($lawType, 'REG-PUBLIC', 'Peraturan Bukan Koleksi', 'publik');

        $this->get(route('library.index'))
            ->assertOk()
            ->assertSee($publicLibrary->title)
            ->assertDontSee($internalLibrary->title)
            ->assertDontSee($law->title);

        $internal = $this->user('internal');

        $this->actingAs($internal)
            ->get(route('library.index'))
            ->assertOk()
            ->assertSee($publicLibrary->title)
            ->assertSee($internalLibrary->title)
            ->assertDontSee($law->title);
    }

    public function test_library_search_uses_bibliographic_metadata(): void
    {
        $type = $this->type('Jurnal Hukum Uji', 'JURNAL-UJI', 'perpustakaan');
        $document = $this->document($type, 'JURNAL-001', 'Kajian Reformasi Hukum', 'publik', [
            'author' => 'Dr. Siti Lampung',
            'isbn_issn' => 'ISSN-2045-7788',
        ]);

        $this->get(route('library.index', ['q' => 'Siti Lampung']))
            ->assertOk()
            ->assertSee($document->title);

        $this->get(route('library.index', ['q' => '2045-7788']))
            ->assertOk()
            ->assertSee($document->title);
    }

    public function test_admin_can_upload_library_reference_without_regulation_dates_or_number(): void
    {
        Storage::fake('documents');

        $admin = $this->user('admin');
        $type = $this->type('Naskah Akademik Uji', 'NA-UJI', 'perpustakaan');
        $category = $this->category();

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            'title' => 'Naskah Akademik Transformasi Hukum',
            'author' => 'Tim Kajian Bidkum',
            'document_type_id' => $type->id,
            'document_number' => '',
            'year' => 2026,
            'enacted_date' => '',
            'effective_date' => '',
            'issuing_institution' => 'Bidkum Polda Lampung',
            'publisher' => 'Bidkum Polda Lampung',
            'isbn_issn' => '',
            'edition_volume' => 'Edisi 1',
            'document_status' => 'berlaku',
            'legal_category_id' => $category->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'transformasi, hukum, kepolisian',
            'summary' => 'Naskah akademik untuk pengembangan transformasi pelayanan hukum.',
            'abstract' => 'Kajian akademik mengenai transformasi pelayanan hukum.',
            'document_version' => '1.0',
            'access_level' => 'internal',
            'file' => UploadedFile::fake()->create('naskah.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.documents.index'));

        $document = Document::where('title', 'Naskah Akademik Transformasi Hukum')->firstOrFail();

        $this->assertNull($document->document_number);
        $this->assertNull($document->enacted_date);
        $this->assertSame('Tim Kajian Bidkum', $document->author);
        $this->assertSame(100, $document->metadata_completeness);
        Storage::disk('documents')->assertExists($document->file_path);
    }

    public function test_library_detail_displays_bibliographic_metadata_and_bank_excludes_it(): void
    {
        $type = $this->type('Buku Referensi Uji', 'BUKU-UJI', 'perpustakaan');
        $document = $this->document($type, 'BUKU-001', 'Buku Referensi Kepolisian', 'publik', [
            'author' => 'Penyusun Bidkum',
            'publisher' => 'Penerbit Hukum Lampung',
            'isbn_issn' => '978-1-23456-789-0',
            'edition_volume' => 'Edisi Kedua',
        ]);

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Penulis/Penyusun')
            ->assertSee('Penyusun Bidkum')
            ->assertSee('978-1-23456-789-0')
            ->assertSee('Edisi Kedua');

        $this->get(route('documents.index'))
            ->assertOk()
            ->assertDontSee($document->title);
    }

    private function document(
        DocumentType $type,
        string $code,
        string $title,
        string $access,
        array $overrides = [],
    ): Document {
        return Document::create([
            'document_code' => $code,
            'title' => $title,
            'author' => 'Penulis Pengujian',
            'document_type_id' => $type->id,
            'year' => 2026,
            'issuing_institution' => 'Bidkum Polda Lampung',
            'publisher' => 'Penerbit Pengujian',
            'document_status' => 'berlaku',
            'legal_category_id' => $this->category()->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'hukum, referensi, pengujian',
            'summary' => 'Ringkasan koleksi perpustakaan digital hukum untuk pengujian.',
            'document_version' => '1.0',
            'access_level' => $access,
            ...$overrides,
        ]);
    }

    private function type(string $name, string $prefix, string $collection): DocumentType
    {
        return DocumentType::create([
            'name' => $name.' '.uniqid(),
            'slug' => str($name)->slug().'-'.uniqid(),
            'code_prefix' => $prefix.'-'.strtoupper(substr(uniqid(), -5)),
            'review_interval_months' => 12,
            'collection' => $collection,
        ]);
    }

    private function category(): LegalCategory
    {
        return LegalCategory::create([
            'name' => 'Kategori Perpustakaan '.uniqid(),
            'slug' => 'kategori-perpustakaan-'.uniqid(),
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Perpustakaan',
            'email' => $role.'-library-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
