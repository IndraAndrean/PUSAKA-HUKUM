<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentDownloadLog;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecureDocumentsAndProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_document_preview_obeys_access_levels(): void
    {
        Storage::fake('documents');

        $type = $this->createDocumentType();
        $public = $this->createDocument($type, 'PUBLIC-001', 'publik');
        $internal = $this->createDocument($type, 'INTERNAL-001', 'internal');
        $restricted = $this->createDocument($type, 'RESTRICTED-001', 'terbatas');

        foreach ([$public, $internal, $restricted] as $document) {
            Storage::disk('documents')->put($document->file_path, '%PDF-1.4 test');
        }

        $internalUser = $this->createUser('internal', 'internal-preview@pusakahukum.test');
        $admin = $this->createUser('admin', 'admin-preview@pusakahukum.test');

        $this->get(route('documents.preview', $public))->assertOk();
        $this->get(route('documents.preview', $internal))->assertForbidden();
        $this->get(route('documents.preview', $restricted))->assertForbidden();

        $this->actingAs($internalUser)->get(route('documents.preview', $public))->assertOk();
        $this->actingAs($internalUser)->get(route('documents.preview', $internal))->assertOk();
        $this->actingAs($internalUser)->get(route('documents.preview', $restricted))->assertForbidden();

        $this->actingAs($admin)->get(route('documents.preview', $restricted))->assertOk();
    }

    public function test_preview_does_not_log_download_but_download_does(): void
    {
        Storage::fake('documents');

        $user = $this->createUser('internal', 'history@pusakahukum.test');
        $document = $this->createDocument($this->createDocumentType(), 'HISTORY-001', 'internal');
        Storage::disk('documents')->put($document->file_path, '%PDF-1.4 history');

        $this->actingAs($user)->get(route('documents.preview', $document))->assertOk();
        $this->assertSame(0, DocumentDownloadLog::where('document_id', $document->id)->count());

        $this->actingAs($user)->get(route('documents.download', $document))->assertOk();

        $this->assertDatabaseHas('document_download_logs', [
            'user_id' => $user->id,
            'document_id' => $document->id,
        ]);
        $this->assertSame(1, $document->fresh()->downloads_count);
    }

    public function test_user_only_sees_their_own_download_history(): void
    {
        $firstUser = $this->createUser('internal', 'first-history@pusakahukum.test');
        $secondUser = $this->createUser('internal', 'second-history@pusakahukum.test');
        $type = $this->createDocumentType();
        $firstDocument = $this->createDocument($type, 'FIRST-HISTORY', 'internal', 'Dokumen Milik Riwayat Pertama');
        $secondDocument = $this->createDocument($type, 'SECOND-HISTORY', 'internal', 'Dokumen Milik Riwayat Kedua');

        DocumentDownloadLog::create([
            'user_id' => $firstUser->id,
            'document_id' => $firstDocument->id,
            'downloaded_at' => now(),
        ]);
        DocumentDownloadLog::create([
            'user_id' => $secondUser->id,
            'document_id' => $secondDocument->id,
            'downloaded_at' => now(),
        ]);

        $this->actingAs($firstUser)
            ->get(route('account.activity'))
            ->assertOk()
            ->assertSee('Dokumen Milik Riwayat Pertama')
            ->assertDontSee('Dokumen Milik Riwayat Kedua');
    }

    public function test_profile_email_change_requires_current_password_and_unique_email(): void
    {
        $user = $this->createUser('internal', 'profile-owner@pusakahukum.test');
        $other = $this->createUser('internal', 'profile-other@pusakahukum.test');

        $base = [
            'name' => 'Pemilik Profil',
            'satuan_kerja' => 'Bidkum',
            'jabatan' => 'Operator',
        ];

        $this->actingAs($user)->put(route('profile.update'), [
            ...$base,
            'email' => 'email-baru@pusakahukum.test',
            'current_password' => 'salah-password',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('profile.update'), [
            ...$base,
            'email' => $other->email,
            'current_password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)->put(route('profile.update'), [
            ...$base,
            'email' => 'email-baru@pusakahukum.test',
            'current_password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'email-baru@pusakahukum.test',
        ]);
    }

    public function test_password_change_requires_the_correct_current_password(): void
    {
        $user = $this->createUser('internal', 'password-owner@pusakahukum.test');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'salah-password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_admin_uploads_pdf_only_to_private_storage(): void
    {
        Storage::fake('documents');
        Storage::fake('public');

        $admin = $this->createUser('admin', 'private-upload@pusakahukum.test');
        $type = $this->createDocumentType();

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            'title' => 'Dokumen Private',
            'document_type_id' => $type->id,
            'document_number' => '12',
            'year' => 2026,
            'enacted_date' => '2026-01-01',
            'effective_date' => '2026-01-02',
            'issuing_institution' => 'Bidkum Polda Lampung',
            'document_status' => 'berlaku',
            'legal_category_id' => $this->createLegalCategory()->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'dokumen, private, pengujian',
            'summary' => 'Ringkasan dokumen private untuk keperluan pengujian.',
            'document_version' => '1.0',
            'access_level' => 'publik',
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.documents.index'));

        $document = Document::where('title', 'Dokumen Private')->firstOrFail();
        Storage::disk('documents')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->assertStringStartsWith('documents/', $document->file_path);
    }

    public function test_admin_upload_rejects_non_pdf_and_oversized_pdf(): void
    {
        Storage::fake('documents');

        $admin = $this->createUser('admin', 'invalid-upload@pusakahukum.test');
        $type = $this->createDocumentType();
        $base = [
            'title' => 'Dokumen Tidak Valid',
            'document_type_id' => $type->id,
            'document_number' => '99',
            'year' => 2026,
            'enacted_date' => '2026-01-01',
            'effective_date' => '2026-01-02',
            'issuing_institution' => 'Bidkum Polda Lampung',
            'document_status' => 'berlaku',
            'legal_category_id' => $this->createLegalCategory()->id,
            'bidang_subbidang' => 'kum',
            'keywords' => 'dokumen, tidak valid, pengujian',
            'summary' => 'Ringkasan dokumen tidak valid untuk pengujian upload.',
            'document_version' => '1.0',
            'access_level' => 'publik',
        ];

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            ...$base,
            'file' => UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain'),
        ])->assertSessionHasErrors('file');

        $this->actingAs($admin)->post(route('admin.documents.store'), [
            ...$base,
            'file' => UploadedFile::fake()->create('besar.pdf', 20481, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    public function test_legacy_public_documents_can_be_migrated_without_metadata_changes(): void
    {
        Storage::fake('documents');
        Storage::fake('public');

        $document = $this->createDocument($this->createDocumentType(), 'LEGACY-001', 'publik');
        Storage::disk('public')->put($document->file_path, '%PDF-1.4 legacy');

        $this->artisan('documents:migrate-to-private')->assertSuccessful();

        Storage::disk('documents')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->assertSame($document->file_path, $document->fresh()->file_path);
    }

    public function test_login_attempts_are_throttled(): void
    {
        $request = ['email' => 'nobody@pusakahukum.test', 'password' => 'password-salah'];
        $server = ['REMOTE_ADDR' => '10.60.0.10'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables($server)
                ->post(route('login.store'), $request)
                ->assertSessionHasErrors('email');
        }

        $this->withServerVariables($server)
            ->post(route('login.store'), $request)
            ->assertTooManyRequests();
    }

    public function test_public_consultation_submissions_are_throttled(): void
    {
        $server = ['REMOTE_ADDR' => '10.60.0.11'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables($server)
                ->post(route('consultation.store'), [
                    'name' => 'Pemohon Uji '.$attempt,
                    'email' => 'pemohon'.$attempt.'@pusakahukum.test',
                    'question' => 'Pertanyaan konsultasi untuk pengujian pembatasan request.',
                ])
                ->assertSessionHasNoErrors();
        }

        $this->withServerVariables($server)
            ->post(route('consultation.store'), [
                'name' => 'Pemohon Uji Batas',
                'email' => 'pemohon-batas@pusakahukum.test',
                'question' => 'Pertanyaan konsultasi untuk memastikan request keenam dibatasi.',
            ])
            ->assertTooManyRequests();
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)).' Test',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createDocumentType(): DocumentType
    {
        return DocumentType::create([
            'name' => 'Jenis '.uniqid(),
            'slug' => 'jenis-'.uniqid(),
            'code_prefix' => 'TEST-'.strtoupper(substr(uniqid(), -6)),
            'review_interval_months' => 3,
        ]);
    }

    private function createLegalCategory(): \App\Models\LegalCategory
    {
        return \App\Models\LegalCategory::create([
            'name' => 'Kategori '.uniqid(),
            'slug' => 'kategori-'.uniqid(),
        ]);
    }

    private function createDocument(
        DocumentType $type,
        string $code,
        string $accessLevel,
        ?string $title = null
    ): Document {
        return Document::create([
            'document_code' => $code,
            'title' => $title ?? 'Dokumen '.$code,
            'document_type_id' => $type->id,
            'document_status' => 'berlaku',
            'access_level' => $accessLevel,
            'file_path' => 'documents/'.strtolower($code).'.pdf',
        ]);
    }
}
