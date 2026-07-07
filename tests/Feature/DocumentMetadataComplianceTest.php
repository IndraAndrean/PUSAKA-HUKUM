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

class DocumentMetadataComplianceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_document_uses_institution_code_filename_and_review_schedule(): void
    {
        Storage::fake('documents');

        $admin = $this->admin();
        $type = $this->type('POL-PERKAP', 3);
        $category = $this->category();

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            ...$this->validMetadata($type, $category),
            'title' => 'Kode Etik Profesi Polri',
            'document_number' => '7',
            'year' => 2026,
            'last_reviewed_at' => '2026-01-15',
            'file' => UploadedFile::fake()->create('asal.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.documents.index'));

        $document = Document::where('title', 'Kode Etik Profesi Polri')->firstOrFail();

        $this->assertMatchesRegularExpression('/^POL-PERKAP-2026-\d{3}$/', $document->document_code);
        $this->assertSame('2026-04-15', $document->next_review_at->format('Y-m-d'));
        $this->assertMatchesRegularExpression(
            '/^documents\/PERKAP_7_2026_KODE_ETIK_PROFESI_POLRI\.pdf$/',
            $document->file_path
        );
        Storage::disk('documents')->assertExists($document->file_path);
        $this->assertSame(100, $document->metadata_completeness);
    }

    public function test_document_requires_three_unique_keywords_and_mandatory_metadata(): void
    {
        Storage::fake('documents');

        $admin = $this->admin();
        $type = $this->type('KAJ', 6);
        $category = $this->category();
        $metadata = $this->validMetadata($type, $category);
        $metadata['keywords'] = 'hukum, hukum';
        $metadata['issuing_institution'] = '';
        $metadata['file'] = UploadedFile::fake()->create('kajian.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), $metadata)
            ->assertSessionHasErrors(['keywords', 'issuing_institution']);
    }

    public function test_duplicate_type_number_and_year_is_rejected(): void
    {
        Storage::fake('documents');

        $admin = $this->admin();
        $type = $this->type('REG-UU', 3);
        $category = $this->category();
        $metadata = $this->validMetadata($type, $category);
        $metadata['file'] = UploadedFile::fake()->create('pertama.pdf', 100, 'application/pdf');

        $this->actingAs($admin)->post(route('admin.documents.store'), $metadata)
            ->assertRedirect(route('admin.documents.index'));

        $metadata['title'] = 'Judul Duplikat Berbeda';
        $metadata['file'] = UploadedFile::fake()->create('kedua.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), $metadata)
            ->assertSessionHasErrors('document_number');

        $this->assertSame(1, Document::where('document_type_id', $type->id)
            ->where('document_number', $metadata['document_number'])
            ->where('year', $metadata['year'])
            ->count());
    }

    public function test_legacy_incomplete_document_is_flagged_on_dashboard(): void
    {
        $admin = $this->admin();
        $type = $this->type('LO', 0);

        Document::create([
            'document_code' => 'LO-2026-999',
            'title' => 'Dokumen Legacy Belum Lengkap',
            'document_type_id' => $type->id,
            'document_status' => 'berlaku',
            'access_level' => 'terbatas',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dokumen Legacy Belum Lengkap')
            ->assertSee('Perlu Dilengkapi');
    }

    public function test_legacy_document_code_can_be_normalized(): void
    {
        $type = $this->type('POL-PERKAP', 3);

        $document = Document::create([
            'document_code' => 'PUSAKA-LEGACY-001',
            'title' => 'Dokumen Legacy Kode',
            'document_type_id' => $type->id,
            'document_number' => '5',
            'year' => 2026,
            'document_status' => 'berlaku',
            'access_level' => 'internal',
        ]);

        $this->artisan('documents:normalize-standards')->assertSuccessful();

        $this->assertMatchesRegularExpression(
            '/^POL-PERKAP-2026-\d{3}$/',
            $document->fresh()->document_code
        );
    }

    private function validMetadata(DocumentType $type, LegalCategory $category): array
    {
        return [
            'title' => 'Undang-Undang Pengujian Metadata',
            'document_type_id' => $type->id,
            'document_number' => '2',
            'year' => 2026,
            'enacted_date' => '2026-01-01',
            'effective_date' => '2026-01-02',
            'issuing_institution' => 'Bidkum Polda Lampung',
            'document_status' => 'berlaku',
            'legal_category_id' => $category->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'hukum, kepolisian, pengujian',
            'summary' => 'Ringkasan dokumen hukum yang memenuhi standar metadata instansi.',
            'abstract' => 'Abstrak pengujian metadata.',
            'legal_basis' => 'Undang-Undang Dasar 1945.',
            'related_regulation' => 'Peraturan terkait pengujian.',
            'document_version' => '1.0',
            'access_level' => 'publik',
        ];
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Metadata',
            'email' => 'admin-metadata-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function type(string $prefix, int $months): DocumentType
    {
        $type = DocumentType::where('code_prefix', $prefix)->first();

        if ($type) {
            $type->update(['review_interval_months' => $months]);

            return $type;
        }

        return DocumentType::create([
            'name' => 'Jenis '.$prefix.' '.uniqid(),
            'slug' => 'jenis-'.strtolower($prefix).'-'.uniqid(),
            'code_prefix' => $prefix,
            'review_interval_months' => $months,
        ]);
    }

    private function category(): LegalCategory
    {
        return LegalCategory::create([
            'name' => 'Kategori Metadata '.uniqid(),
            'slug' => 'kategori-metadata-'.uniqid(),
        ]);
    }
}
