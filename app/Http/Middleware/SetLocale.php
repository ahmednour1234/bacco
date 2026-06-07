<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // Portal paths (/admin, /enduser, /supplier) don't use URL-based locale.
        // Use session locale so users can switch language without a URL change.
        $isPortal = str_starts_with($path, '/admin')
                 || str_starts_with($path, '/enduser')
                 || str_starts_with($path, '/supplier')
                 || $path === '/try';

        if ($isPortal) {
            $locale = session('locale', 'ar');
            app()->setLocale(in_array($locale, ['en', 'ar']) ? $locale : 'ar');
        } elseif (str_starts_with($path, '/ar/') || $path === '/ar') {
            // Public Arabic URLs — store in session
            app()->setLocale('ar');
            session(['locale' => 'ar']);
        } else {
            // Public English URLs — apply English for this request only,
            // do NOT overwrite the session so portal pages keep their locale.
            app()->setLocale('en');
        }

        return $next($request);
    }
}
