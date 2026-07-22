<?php

use App\Http\Controllers\Api\Catalog\Research\ProductFamilyApiController;
use App\Http\Controllers\Api\Catalog\Research\ProductVariantApiController;
use App\Http\Controllers\Api\Catalog\Research\ResearchImportApiController;
use App\Http\Controllers\Api\Catalog\Research\ResearchJobApiController;
use App\Http\Controllers\Api\Catalog\Research\ReviewQueueApiController;
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

    // ── Read from scraper (qimta_ai) DB ─────────────────────────────────────
    Route::get('/scraper-brands',     [ScraperController::class, 'scraperBrands'])->name('scraper.brands');
    Route::get('/scraper-categories', [ScraperController::class, 'scraperCategories'])->name('scraper.categories');
    Route::get('/scraper-products',   [ScraperController::class, 'scraperProducts'])->name('scraper.products');

    // ── Stats (both DBs) ───────────────────────────────────────────────────
    Route::get('/stats', [ScraperController::class, 'stats'])->name('stats');

    // ── Sync: qimta_ai → main DB (queued jobs) ────────────────────────────
    Route::post('/sync',            [ScraperController::class, 'syncAll'])->name('sync.all');
    Route::post('/sync/brands',     [ScraperController::class, 'syncBrands'])->name('sync.brands');
    Route::post('/sync/categories', [ScraperController::class, 'syncCategories'])->name('sync.categories');
    Route::post('/sync/products',   [ScraperController::class, 'syncProducts'])->name('sync.products');
});

/*
|--------------------------------------------------------------------------
| Product Catalog Research API (authenticated employees/admins)
|--------------------------------------------------------------------------
| Same session guard as the admin panel; each action additionally checks a
| catalog.* Gate ability. No pricing is ever exposed.
*/
Route::prefix('catalog')->name('api.catalog.')->middleware(['web', 'auth', 'employee'])->group(function () {
    Route::get('imports',                    [ResearchImportApiController::class, 'index'])->name('imports.index');
    Route::post('imports',                   [ResearchImportApiController::class, 'store'])->name('imports.store');
    Route::get('imports/{uuid}',             [ResearchImportApiController::class, 'show'])->name('imports.show');
    Route::post('imports/{uuid}/map-columns',[ResearchImportApiController::class, 'mapColumns'])->name('imports.map');
    Route::post('imports/{uuid}/process',    [ResearchImportApiController::class, 'process'])->name('imports.process');

    Route::get('product-families',                    [ProductFamilyApiController::class, 'index'])->name('families.index');
    Route::get('product-families/{uuid}',             [ProductFamilyApiController::class, 'show'])->name('families.show');
    Route::post('product-families/{uuid}/research',        [ProductFamilyApiController::class, 'research'])->name('families.research');
    Route::post('product-families/{uuid}/pause-research',  [ProductFamilyApiController::class, 'pause'])->name('families.pause');
    Route::post('product-families/{uuid}/resume-research', [ProductFamilyApiController::class, 'resume'])->name('families.resume');
    Route::post('product-families/{uuid}/cancel-research', [ProductFamilyApiController::class, 'cancel'])->name('families.cancel');

    Route::get('research-jobs',            [ResearchJobApiController::class, 'index'])->name('jobs.index');
    Route::get('research-jobs/{uuid}',     [ResearchJobApiController::class, 'show'])->name('jobs.show');
    Route::post('research-jobs/{uuid}/retry',[ResearchJobApiController::class, 'retry'])->name('jobs.retry');

    Route::get('products',                    [ProductVariantApiController::class, 'index'])->name('products.index');
    Route::get('products/{uuid}',             [ProductVariantApiController::class, 'show'])->name('products.show');
    Route::get('product-variants',            [ProductVariantApiController::class, 'index'])->name('variants.index');
    Route::patch('product-variants/{uuid}/verify',[ProductVariantApiController::class, 'verify'])->name('variants.verify');
    Route::patch('product-variants/{uuid}/reject',[ProductVariantApiController::class, 'reject'])->name('variants.reject');

    Route::get('review-queue',              [ReviewQueueApiController::class, 'index'])->name('review.index');
    Route::post('review-queue/{id}/resolve',[ReviewQueueApiController::class, 'resolve'])->name('review.resolve');
});
