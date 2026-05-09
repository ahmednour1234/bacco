<?php

use App\Http\Controllers\Api\ScraperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Scraper API Routes
|--------------------------------------------------------------------------
|
| These routes are consumed by the external Python scraper (qimta_ai).
| No authentication is required — keep this API internal / behind a firewall.
|
*/

Route::prefix('scraper')->name('api.scraper.')->group(function () {

    // ── Read from main DB ──────────────────────────────────────────────────
    Route::get('/brands',     [ScraperController::class, 'brands'])->name('brands');
    Route::get('/categories', [ScraperController::class, 'categories'])->name('categories');
    Route::get('/products',   [ScraperController::class, 'products'])->name('products');
    Route::get('/sources',    [ScraperController::class, 'sources'])->name('sources');
    Route::post('/sources',   [ScraperController::class, 'storeSource'])->name('sources.store');

    // ── Stats (both DBs) ───────────────────────────────────────────────────
    Route::get('/stats', [ScraperController::class, 'stats'])->name('stats');

    // ── Sync: qimta_ai → main DB (queued jobs) ────────────────────────────
    Route::post('/sync',            [ScraperController::class, 'syncAll'])->name('sync.all');
    Route::post('/sync/brands',     [ScraperController::class, 'syncBrands'])->name('sync.brands');
    Route::post('/sync/categories', [ScraperController::class, 'syncCategories'])->name('sync.categories');
    Route::post('/sync/products',   [ScraperController::class, 'syncProducts'])->name('sync.products');
});
