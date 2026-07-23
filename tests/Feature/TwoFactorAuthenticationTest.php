<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_and_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin SIPAKEM',
            'email' => 'admin@sipakem.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('profile.two-factor'))
            ->assertOk()
            ->assertSee('Google Authenticator');

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);

        $code = (new Google2FA())->getCurrentOtp($user->two_factor_secret);

        $this->actingAs($user)
            ->post(route('profile.two-factor.store'), [
                'current_password' => 'password',
                'code' => $code,
            ])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->delete(route('profile.two-factor.destroy'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('success');

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_without_two_factor_redirects_to_required_setup(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin SIPAKEM',
            'email' => 'admin@sipakem.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('two-factor.setup'))
            ->assertSessionHas('url.intended', route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_required_setup_page_enables_two_factor_after_first_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin SIPAKEM',
            'email' => 'admin@sipakem.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.setup'));

        $this->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Aktivasi Google Authenticator')
            ->assertSee('Kunci manual')
            ->assertSee('Lanjut masukkan kode')
            ->assertDontSee('Kode verifikasi');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);

        $this->get(route('two-factor.setup.confirm'))
            ->assertOk()
            ->assertSee('Konfirmasi kode keamanan')
            ->assertSee('Kode verifikasi');

        $this->post(route('two-factor.setup.store'), [
            'code' => '000000',
        ])->assertSessionHasErrors('code');

        $this->post(route('two-factor.setup.store'), [
            'code' => (new Google2FA())->getCurrentOtp($user->two_factor_secret),
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_protected_admin_pages_require_two_factor_setup(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin SIPAKEM',
            'email' => 'admin@sipakem.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('two-factor.setup'))
            ->assertSessionHas('url.intended', route('admin.dashboard'));

        $this->actingAs($user)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Aktivasi Google Authenticator');
    }

    public function test_logged_in_user_without_two_factor_cannot_access_document_area(): void
    {
        $user = User::factory()->create([
            'name' => 'Pengguna Internal',
            'email' => 'internal@sipakem.test',
            'password' => 'password',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertRedirect(route('two-factor.setup'))
            ->assertSessionHas('url.intended', route('documents.index'));
    }

    public function test_login_with_enabled_two_factor_requires_valid_otp_code(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'name' => 'Admin SIPAKEM',
            'email' => 'admin@sipakem.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('two-factor.challenge'))
            ->assertSessionHas('login.2fa.user_id', $user->id);

        $this->assertGuest();

        $this->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSee('Kode verifikasi');

        $this->post(route('two-factor.verify'), [
            'code' => '000000',
        ])->assertSessionHasErrors('code');

        $this->assertGuest();

        $this->post(route('two-factor.verify'), [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_two_factor_challenge_redirects_without_pending_login_session(): void
    {
        $this->get(route('two-factor.challenge'))
            ->assertRedirect(route('login'));
    }
}
