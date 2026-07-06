<?php

namespace App\Providers;

use App\Services\CatalogStats;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Load global helper functions (e.g. catalog_value_t) without relying
        // on a composer "files" autoload entry, so no `composer dump-autoload`
        // is needed on deploy.
        require_once __DIR__ . '/../Support/helpers.php';
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.qimta');
        Paginator::defaultSimpleView('vendor.pagination.qimta');

        // Share real catalog stats with every view.
        // Cached 6 hours; falls back to last-known values if DB is down.
        View::share('catalogStats', CatalogStats::get());

        // Inject DB-backed SEO metadata into the public layout, keyed by the
        // current route name. The blade reads $seo (a SeoMeta model or null) and
        // falls back to its own defaults when no record exists. Resolved lazily
        // per-request and guarded so a missing table / DB outage never 500s a page.
        View::composer('layouts.app', function ($view) {
            $view->with('seo', \App\Services\SeoResolver::forCurrentRoute());
        });

        // Scrub invalid UTF-8 from every Livewire response payload before it is
        // JSON-encoded. Without this, a single malformed byte anywhere in a
        // component snapshot or its rendered effects (e.g. coming from the DB)
        // makes json_encode() fail with:
        //   "Malformed UTF-8 characters, possibly incorrectly encoded".
        Livewire::listen('response', function ($payload) {
            // Return a "finisher": Livewire calls it with the payload and
            // replaces the payload with whatever we return.
            return function ($payload) {
                return $this->scrubUtf8($payload);
            };
        });
    }

    /**
     * Recursively convert every string in a structure to valid UTF-8,
     * dropping any invalid byte sequences.
     */
    protected function scrubUtf8(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->scrubUtf8($v);
            }

            return $value;
        }

        if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

            return $clean !== false ? $clean : '';
        }

        return $value;
    }
}

