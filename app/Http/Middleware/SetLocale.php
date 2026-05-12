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
                 || str_starts_with($path, '/supplier');

        if ($isPortal) {
            $locale = session('locale', 'ar');
            app()->setLocale(in_array($locale, ['en', 'ar']) ? $locale : 'ar');
        } elseif (str_starts_with($path, '/ar/') || $path === '/ar') {
            // Public Arabic URLs
            app()->setLocale('ar');
            session(['locale' => 'ar']);
        } else {
            // Public English URLs
            app()->setLocale('en');
            session(['locale' => 'en']);
        }

        return $next($request);
    }
}
