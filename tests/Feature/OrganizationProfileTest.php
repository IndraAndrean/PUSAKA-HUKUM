<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_profile_uses_institutional_content_from_central_profile(): void
    {
        $profile = OrganizationProfile::current();

        $this->get(route('organization-profile.show'))
            ->assertOk()
            ->assertSee($profile->organization_name)
            ->assertSee($profile->portal_full_name)
            ->assertSee('Layanan Terintegrasi')
            ->assertSee('BerAKHLAK dan Presisi');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($profile->tagline)
            ->assertSee('Profil dan Layanan');
    }

    public function test_admin_can_update_profile_and_change_is_audited(): void
    {
        $admin = $this->user('admin');
        $profile = OrganizationProfile::current();
        $data = $this->validData($profile);
        $data['tagline'] = 'Tagline Resmi Pengujian';
        $data['email'] = 'bidkum@example.test';

        $this->actingAs($admin)
            ->put(route('admin.organization-profile.update'), $data)
            ->assertSessionHas('success');

        $this->assertSame('Tagline Resmi Pengujian', $profile->fresh()->tagline);
        $this->assertSame('bidkum@example.test', $profile->fresh()->email);
        $this->assertTrue(AuditLog::where('module', 'Profil Instansi')
            ->where('action', 'updated')
            ->where('subject_id', $profile->id)
            ->exists());
    }

    public function test_logo_is_stored_publicly_and_old_logo_is_removed_when_replaced(): void
    {
        Storage::fake('public');
        $admin = $this->user('admin');
        $profile = OrganizationProfile::current();
        $data = $this->validData($profile);
        $data['logo'] = UploadedFile::fake()->image('logo-awal.png', 300, 300);

        $this->actingAs($admin)
            ->put(route('admin.organization-profile.update'), $data)
            ->assertSessionHas('success');

        $oldPath = $profile->fresh()->logo_path;
        Storage::disk('public')->assertExists($oldPath);

        $data = $this->validData($profile->fresh());
        $data['logo'] = UploadedFile::fake()->image('logo-baru.webp', 300, 300);

        $this->actingAs($admin)
            ->put(route('admin.organization-profile.update'), $data)
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($profile->fresh()->logo_path);
    }

    public function test_invalid_logo_and_website_are_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->user('admin');
        $profile = OrganizationProfile::current();
        $data = $this->validData($profile);
        $data['website'] = 'bukan-url';
        $data['logo'] = UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml');

        $this->actingAs($admin)
            ->put(route('admin.organization-profile.update'), $data)
            ->assertSessionHasErrors(['website', 'logo']);
    }

    public function test_internal_user_cannot_manage_institution_profile(): void
    {
        $internal = $this->user('internal');

        $this->actingAs($internal)
            ->get(route('admin.organization-profile.edit'))
            ->assertForbidden();
    }

    private function validData(OrganizationProfile $profile): array
    {
        return [
            'portal_name' => $profile->portal_name,
            'portal_full_name' => $profile->portal_full_name,
            'tagline' => $profile->tagline,
            'organization_name' => $profile->organization_name,
            'eyebrow' => $profile->eyebrow,
            'hero_description' => $profile->hero_description,
            'about' => $profile->about,
            'institution_duties' => $profile->institution_duties,
            'general_goal' => $profile->general_goal,
            'services_text' => implode("\n", $profile->services),
            'benefits_organization_text' => implode("\n", $profile->benefits_organization),
            'benefits_personnel_text' => implode("\n", $profile->benefits_personnel),
            'benefits_public_text' => implode("\n", $profile->benefits_public),
            'organization_values_text' => implode("\n", $profile->organization_values),
            'address' => $profile->address,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'website' => $profile->website,
            'office_hours' => $profile->office_hours,
        ];
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Profil Instansi',
            'email' => $role.'-profil-instansi-'.uniqid().'@pusakahukum.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
