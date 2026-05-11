<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Priority 1: URL prefix /ar/... sets Arabic regardless of session
        if (str_starts_with($request->getPathInfo(), '/ar/') || $request->getPathInfo() === '/ar') {
            app()->setLocale('ar');
            session(['locale' => 'ar']);
            return $next($request);
        }

        // Priority 2: session-stored preference (used by non-ar URL paths)
        $locale = session('locale', config('app.locale', 'en'));
        if (in_array($locale, ['en', 'ar'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
