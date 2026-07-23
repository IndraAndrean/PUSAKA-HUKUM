<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function setup(Request $request, TwoFactorService $twoFactor): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return $this->redirectAfterLogin($user);
        }

        if (blank($user->two_factor_secret)) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
            ])->save();

            $user->refresh();
        }

        return view('auth.two-factor-setup', [
            'user' => $user,
            'qrCodeSvg' => $twoFactor->qrCodeSvg($user, $user->two_factor_secret),
            'manualKey' => $user->two_factor_secret,
        ]);
    }

    public function setupConfirm(Request $request, TwoFactorService $twoFactor): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return $this->redirectAfterLogin($user);
        }

        if (blank($user->two_factor_secret)) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
            ])->save();

            $user->refresh();
        }

        return view('auth.two-factor-setup-code', [
            'user' => $user,
        ]);
    }

    public function setupStore(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return $this->redirectAfterLogin($user);
        }

        if (blank($user->two_factor_secret)) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
            ])->save();

            $user->refresh();
        }

        if (! $twoFactor->verify($user->two_factor_secret, $data['code'])) {
            return back()->withErrors([
                'code' => 'Kode verifikasi tidak valid. Periksa kembali kode dari Google Authenticator.',
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->regenerate();

        return $this->redirectAfterLogin($user)
            ->with('success', 'Verifikasi dua langkah berhasil diaktifkan.');
    }

    public function edit(Request $request, TwoFactorService $twoFactor): View
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled() && blank($user->two_factor_secret)) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
            ])->save();

            $user->refresh();
        }

        return view('account.two-factor', [
            'user' => $user,
            'qrCodeSvg' => $user->hasTwoFactorEnabled() ? null : $twoFactor->qrCodeSvg($user, $user->two_factor_secret),
            'manualKey' => $user->hasTwoFactorEnabled() ? null : $user->two_factor_secret,
        ]);
    }

    public function store(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'string'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'code.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $user = $request->user();

        if (blank($user->two_factor_secret)) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
            ])->save();

            $user->refresh();
        }

        if (! $twoFactor->verify($user->two_factor_secret, $data['code'])) {
            return back()->withErrors([
                'code' => 'Kode verifikasi tidak valid. Periksa kembali kode dari Google Authenticator.',
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return redirect()
            ->intended(route('profile.two-factor'))
            ->with('success', 'Verifikasi dua langkah berhasil diaktifkan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()
            ->route('profile.two-factor')
            ->with('success', 'Verifikasi dua langkah berhasil dinonaktifkan.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            $request->session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', ['user' => $user]);
    }

    public function verify(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            $request->session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sesi verifikasi dua langkah sudah berakhir. Silakan masuk kembali.']);
        }

        if (! $twoFactor->verify($user->two_factor_secret, $data['code'])) {
            return back()->withErrors([
                'code' => 'Kode verifikasi tidak valid. Periksa kembali kode dari Google Authenticator.',
            ]);
        }

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Verifikasi dua langkah dibatalkan.']);
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('login.2fa.user_id');

        if (! $userId) {
            return null;
        }

        $user = User::find($userId);

        if (! $user || ! $user->is_active || ! $user->hasTwoFactorEnabled()) {
            return null;
        }

        return $user;
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        return $user->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('documents.index'));
    }
}
