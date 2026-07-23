<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_open_forgot_password_page(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Kata Sandi')
            ->assertSee('Kirim Tautan Reset');
    }

    public function test_guest_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@sipakem.test',
            'password' => 'password',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'valid-reset@sipakem.test',
            'password' => 'password-lama',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_password_reset_rejects_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid-reset@sipakem.test',
            'password' => 'password',
            'role' => 'internal',
            'is_active' => true,
        ]);

        $this->post(route('password.update'), [
            'token' => 'token-salah',
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertSessionHasErrors('email');
    }
}
