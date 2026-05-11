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

        // URL is the single source of truth for locale.
        // /ar/... or /ar  → Arabic (and remember it in session)
        // Any other path  → English (reset session to en)
        if (str_starts_with($path, '/ar/') || $path === '/ar') {
            app()->setLocale('ar');
            session(['locale' => 'ar']);
        } else {
            app()->setLocale('en');
            session(['locale' => 'en']);
        }

        return $next($request);
    }
}
