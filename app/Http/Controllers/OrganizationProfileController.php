<?php

namespace App\Http\Controllers;

use App\Models\OrganizationProfile;
use Illuminate\View\View;

class OrganizationProfileController extends Controller
{
    public function __invoke(): View
    {
        return view('public.organization-profile', [
            'profile' => OrganizationProfile::current(),
        ]);
    }
}
