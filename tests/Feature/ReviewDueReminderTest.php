<?php

namespace Tests\Feature;

use App\Mail\DocumentsReviewDue;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewDueReminderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_emails_active_admins_when_documents_are_due(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin Aktif',
            'email' => 'admin-aktif@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $inactiveAdmin = User::create([
            'name' => 'Admin Nonaktif',
            'email' => 'admin-nonaktif@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $internalUser = User::create([
            'name' => 'Pengguna Internal',
            'email' => 'internal-user@pusakahukum.test',
            'password' => 'password',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $type = DocumentType::create([
            'name' => 'Jenis Review '.uniqid(),
            'slug' => 'jenis-review-'.uniqid(),
            'code_prefix' => 'REV',
            'review_interval_months' => 6,
        ]);

        Document::create([
            'document_code' => 'REV-2026-001',
            'title' => 'Dokumen Jatuh Tempo Uji',
            'document_type_id' => $type->id,
            'document_status' => 'berlaku',
            'access_level' => 'publik',
            'next_review_at' => now()->subDay(),
        ]);

        $this->artisan('reminders:review-due')->assertSuccessful();

        Mail::assertSent(DocumentsReviewDue::class, fn ($mail) => $mail->hasTo('admin-aktif@pusakahukum.test'));
        Mail::assertNotSent(DocumentsReviewDue::class, fn ($mail) => $mail->hasTo('admin-nonaktif@pusakahukum.test'));
        Mail::assertNotSent(DocumentsReviewDue::class, fn ($mail) => $mail->hasTo('internal-user@pusakahukum.test'));
    }

    public function test_command_sends_nothing_when_no_documents_are_due(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Admin Tanpa Tugas',
            'email' => 'admin-tanpa-tugas@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->artisan('reminders:review-due')->assertSuccessful();

        Mail::assertNothingSent();
    }
}
