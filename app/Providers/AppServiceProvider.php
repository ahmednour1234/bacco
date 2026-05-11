<?php

namespace App\Providers;

use App\Services\CatalogStats;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.qimta');
        Paginator::defaultSimpleView('vendor.pagination.qimta');

        // Share real catalog stats with every view.
        // Cached 6 hours; falls back to last-known values if DB is down.
        View::share('catalogStats', CatalogStats::get());
    }
}

