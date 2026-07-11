<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizationProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.organization-profile.edit', [
            'profile' => OrganizationProfile::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = OrganizationProfile::current();
        $data = $request->validate([
            'portal_name' => ['required', 'string', 'max:100'],
            'portal_full_name' => ['required', 'string', 'max:255'],
            'tagline' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['required', 'string', 'max:1000'],
            'about' => ['required', 'string', 'min:50'],
            'institution_duties' => ['required', 'string', 'min:50'],
            'general_goal' => ['required', 'string', 'min:30'],
            'services_text' => ['required', 'string'],
            'benefits_organization_text' => ['required', 'string'],
            'benefits_personnel_text' => ['required', 'string'],
            'benefits_public_text' => ['required', 'string'],
            'organization_values_text' => ['required', 'string'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:filter', 'max:150'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'office_hours' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, JPEG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'website.url' => 'Alamat website harus berupa URL http atau https yang valid.',
        ]);

        foreach ([
            'services',
            'benefits_organization',
            'benefits_personnel',
            'benefits_public',
            'organization_values',
        ] as $field) {
            $data[$field] = $this->lines($data[$field.'_text']);
            unset($data[$field.'_text']);
        }

        if ($request->boolean('remove_logo') && $profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            $newPath = $request->file('logo')->store('branding', 'public');

            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            $data['logo_path'] = $newPath;
        }

        unset($data['logo'], $data['remove_logo']);
        $profile->update($data);

        return back()->with('success', 'Identitas dan profil instansi berhasil diperbarui.');
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->unique(fn ($line) => mb_strtolower($line))
            ->values()
            ->all();
    }
}
