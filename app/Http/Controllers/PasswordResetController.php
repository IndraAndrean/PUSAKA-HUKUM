<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan format email yang valid.',
        ]);

        Password::sendResetLink($data);

        return back()->with('success', 'Jika email terdaftar, tautan reset kata sandi telah dikirim.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan format email yang valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru belum sama.',
        ]);

        $status = Password::reset(
            $data,
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->messageForStatus($status)]);
    }

    private function messageForStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.',
            Password::INVALID_USER => 'Email tidak ditemukan.',
            default => 'Permintaan reset kata sandi belum dapat diproses.',
        };
    }
}
