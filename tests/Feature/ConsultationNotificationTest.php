<?php

namespace Tests\Feature;

use App\Mail\ConsultationAnswered;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConsultationNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_answering_a_consultation_emails_the_citizen(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin Notifikasi',
            'email' => 'admin-notifikasi@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'name' => 'Warga Uji',
            'email' => 'warga-uji@example.test',
            'question' => 'Bagaimana cara mengurus surat keterangan?',
        ]);

        $this->actingAs($admin)->put(route('admin.consultations.update', $consultation), [
            'answer' => 'Silakan datang ke loket pelayanan dengan membawa KTP.',
            'status' => 'dijawab',
        ]);

        Mail::assertSent(ConsultationAnswered::class, fn ($mail) => $mail->hasTo('warga-uji@example.test')
            && $mail->consultation->is($consultation));
    }

    public function test_answering_a_consultation_without_email_sends_nothing(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin Notifikasi Tanpa Email',
            'email' => 'admin-notifikasi-2@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'name' => 'Warga Tanpa Email',
            'question' => 'Apakah dokumen ini bisa diakses publik?',
        ]);

        $this->actingAs($admin)->put(route('admin.consultations.update', $consultation), [
            'answer' => 'Dokumen ini bersifat publik.',
            'status' => 'dijawab',
        ]);

        Mail::assertNothingSent();
    }

    public function test_re_saving_an_already_answered_consultation_does_not_resend_email(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin Notifikasi Ulang',
            'email' => 'admin-notifikasi-3@pusakahukum.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $consultation = Consultation::create([
            'name' => 'Warga Uji Ulang',
            'email' => 'warga-uji-ulang@example.test',
            'question' => 'Bagaimana status pengajuan saya?',
            'answer' => 'Sudah diproses.',
            'status' => 'dijawab',
            'answered_by' => $admin->id,
            'answered_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.consultations.update', $consultation), [
            'answer' => 'Sudah diproses dan selesai.',
            'status' => 'selesai',
        ]);

        Mail::assertNothingSent();
    }
}
