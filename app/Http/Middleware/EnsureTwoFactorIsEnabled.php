<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasTwoFactorEnabled() || $this->isAllowedRoute($request)) {
            return $next($request);
        }

        if ($request->isMethod('get') || $request->isMethod('head')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()
            ->route('two-factor.setup')
            ->with('info', 'Aktifkan verifikasi dua langkah terlebih dahulu untuk melanjutkan.');
    }

    private function isAllowedRoute(Request $request): bool
    {
        return $request->routeIs(
            'two-factor.setup',
            'two-factor.setup.confirm',
            'two-factor.setup.store',
            'profile.two-factor',
            'profile.two-factor.store',
            'profile.two-factor.destroy',
            'logout'
        );
    }
}
