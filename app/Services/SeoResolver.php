<?php

namespace App\Services;

use App\Models\SeoMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Resolves the SeoMeta record for the current request's route.
 *
 * Arabic pages share the same logical page as their English counterpart but
 * carry an "ar." route-name prefix (e.g. "ar.about" ↔ "about"). We strip that
 * prefix so a single SeoMeta row (keyed by the canonical EN route name) drives
 * both locales — the model's locale-aware accessors pick the right language.
 *
 * Everything is wrapped defensively: if the table is missing (fresh checkout
 * before migrate) or the DB is unreachable, this returns null and the layout
 * falls back to its hardcoded defaults instead of throwing.
 */
class SeoResolver
{
    public static function forCurrentRoute(): ?SeoMeta
    {
        $routeName = Route::currentRouteName();

        if (! $routeName) {
            return null;
        }

        return static::forRoute($routeName);
    }

    public static function forRoute(string $routeName): ?SeoMeta
    {
        // Normalise "ar.about" → "about" so one row serves both locales.
        $canonical = str_starts_with($routeName, 'ar.')
            ? substr($routeName, 3)
            : $routeName;

        try {
            return Cache::remember(
                "seo_meta:{$canonical}",
                now()->addHours(6),
                fn () => SeoMeta::where('route_name', $canonical)
                    ->where('active', true)
                    ->first()
            );
        } catch (Throwable $e) {
            // Missing table / DB outage — never break page rendering for SEO.
            return null;
        }
    }

    /**
     * Forget the cached record for a route (call after admin edits).
     */
    public static function forget(string $routeName): void
    {
        $canonical = str_starts_with($routeName, 'ar.')
            ? substr($routeName, 3)
            : $routeName;

        Cache::forget("seo_meta:{$canonical}");
    }
}
