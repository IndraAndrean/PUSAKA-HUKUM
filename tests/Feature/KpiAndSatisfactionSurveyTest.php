<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KpiTarget;
use App\Models\SatisfactionSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KpiAndSatisfactionSurveyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_submit_survey_once_per_month(): void
    {
        $this->get(route('surveys.create'))
            ->assertOk()
            ->assertSee('Survei Kepuasan PUSAKA HUKUM');

        $this->post(route('surveys.store'), $this->validSurvey())
            ->assertRedirect(route('surveys.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('satisfaction_surveys', [
            'respondent_type' => 'masyarakat',
            'search_duration_seconds' => 150,
            'found_document' => true,
        ]);

        $this->post(route('surveys.store'), $this->validSurvey())
            ->assertSessionHas('error');

        $this->assertSame(1, SatisfactionSurvey::count());
    }

    public function test_survey_validates_ratings_and_does_not_store_raw_ip(): void
    {
        $payload = $this->validSurvey();
        $payload['overall_rating'] = 6;

        $this->post(route('surveys.store'), $payload)
            ->assertSessionHasErrors('overall_rating');

        $this->post(route('surveys.store'), $this->validSurvey(), ['REMOTE_ADDR' => '192.0.2.10'])
            ->assertSessionHas('success');

        $survey = SatisfactionSurvey::firstOrFail();
        $this->assertNotSame('192.0.2.10', $survey->ip_hash);
        $this->assertSame(64, strlen($survey->ip_hash));
    }

    public function test_admin_can_view_update_and_export_kpi_data(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->get(route('admin.kpi.index'))
            ->assertOk()
            ->assertSee('Matriks Capaian')
            ->assertSee('Target dan Verifikasi');

        $payload = $this->validTargets();
        $payload['documents_target'] = 350;
        $payload['sop_available'] = '1';

        $this->actingAs($admin)
            ->put(route('admin.kpi.update'), $payload)
            ->assertSessionHas('success');

        $target = KpiTarget::current()->fresh();
        $this->assertSame(350, $target->documents_target);
        $this->assertTrue($target->sop_available);
        $this->assertSame($admin->id, $target->updated_by);
        $this->assertTrue(AuditLog::where('module', 'Indikator Keberhasilan')
            ->where('action', 'updated')
            ->where('subject_id', $target->id)
            ->exists());

        $this->actingAs($admin)
            ->get(route('admin.kpi.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload();
    }

    public function test_internal_user_cannot_access_kpi_administration(): void
    {
        $internal = $this->user('internal');

        $this->actingAs($internal)
            ->get(route('admin.kpi.index'))
            ->assertForbidden();
    }

    private function validSurvey(): array
    {
        return [
            'respondent_type' => 'masyarakat',
            'accessibility_rating' => 4,
            'speed_rating' => 4,
            'content_rating' => 5,
            'ease_rating' => 4,
            'overall_rating' => 5,
            'found_document' => '1',
            'search_duration_minutes' => '2.5',
            'most_useful_feature' => 'pencarian',
            'feedback' => 'Portal mudah digunakan.',
        ];
    }

    private function validTargets(): array
    {
        return [
            'documents_target' => 300,
            'legislation_target' => 200,
            'internal_documents_target' => 100,
            'legal_studies_target' => 30,
            'education_materials_target' => 10,
            'registered_users_target' => 100,
            'accesses_target' => 1000,
            'satisfaction_target_percent' => 80,
            'utilization_target_percent' => 75,
            'search_time_target_seconds' => 180,
            'satker_coverage_target_percent' => 100,
            'polres_coverage_target_percent' => 100,
            'satker_coverage_percent' => 25,
            'polres_coverage_percent' => 20,
            'appointed_admin_count' => 2,
            'sop_available' => '0',
            'user_guide_available' => '1',
            'verification_notes' => 'Diverifikasi berdasarkan laporan implementasi.',
        ];
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' KPI',
            'email' => $role.'-kpi-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
