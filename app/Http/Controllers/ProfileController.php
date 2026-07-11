<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $request->string('email')->lower()->toString() !== strtolower($user->email);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:filter', 'max:150', Rule::unique('users')->ignore($user)],
            'satuan_kerja' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'current_password' => [Rule::requiredIf($emailChanged), 'nullable', 'current_password'],
        ]);

        unset($data['current_password']);
        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
