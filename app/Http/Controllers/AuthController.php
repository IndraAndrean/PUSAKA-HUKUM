<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:filter'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $user = User::where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->hasTwoFactorEnabled()) {
                $request->session()->regenerate();
                $request->session()->put('login.2fa.user_id', $user->id);
                $request->session()->put('login.2fa.remember', $remember);

                return redirect()->route('two-factor.challenge');
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            $request->session()->put(
                'url.intended',
                $user->isAdmin() ? route('admin.dashboard') : route('documents.index')
            );

            return redirect()
                ->route('two-factor.setup')
                ->with('info', 'Aktifkan verifikasi dua langkah terlebih dahulu untuk melanjutkan.');
        }

        return back()
            ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
