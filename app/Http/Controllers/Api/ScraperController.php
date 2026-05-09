<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Scraper\SyncScraperBrandsJob;
use App\Jobs\Scraper\SyncScraperCategoriesJob;
use App\Jobs\Scraper\SyncScraperProductsJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ScraperSource;
use App\Models\Scraper\ScraperBrand;
use App\Models\Scraper\ScraperCategory;
use App\Models\Scraper\ScraperProduct;
use App\Models\Scraper\ScraperSource as AiScraperSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/brands
    // -------------------------------------------------------------------------

    public function brands(): JsonResponse
    {
        $brands = Brand::where('active', true)
            ->orderBy('name')
            ->get(['uuid', 'name', 'description']);

        return response()->json(['data' => $brands]);
    }

    // -------------------------------------------------------------------------
    // GET /api/categories
    // -------------------------------------------------------------------------

    public function categories(): JsonResponse
    {
        $categories = Category::where('active', true)
            ->orderBy('name')
            ->get(['uuid', 'name', 'slug', 'parent_id', 'description']);

        return response()->json(['data' => $categories]);
    }

    // -------------------------------------------------------------------------
    // GET /api/products
    // -------------------------------------------------------------------------

    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['brand:id,uuid,name', 'category:id,uuid,name,slug'])
            ->where('active', true)
            ->orderBy('name');

        if ($request->filled('brand_uuid')) {
            $query->whereHas('brand', fn($q) => $q->where('uuid', $request->brand_uuid));
        }

        if ($request->filled('category_uuid')) {
            $query->whereHas('category', fn($q) => $q->where('uuid', $request->category_uuid));
        }

        $products = $query->get([
            'uuid', 'name', 'sku', 'description',
            'brand_id', 'category_id', 'unit_id',
        ]);

        return response()->json(['data' => $products]);
    }

    // -------------------------------------------------------------------------
    // GET /api/sources
    // -------------------------------------------------------------------------

    public function sources(): JsonResponse
    {
        $sources = ScraperSource::where('active', true)
            ->orderBy('name')
            ->get(['uuid', 'name', 'url', 'type']);

        return response()->json(['data' => $sources]);
    }

    // -------------------------------------------------------------------------
    // POST /api/sources
    // -------------------------------------------------------------------------

    public function storeSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url'  => ['required', 'url', 'max:2048'],
            'type' => ['nullable', 'string', 'max:100'],
        ]);

        $source = ScraperSource::create($validated);

        return response()->json(['data' => $source->only(['uuid', 'name', 'url', 'type'])], 201);
    }

    // =========================================================================
    // SCRAPER DB  –  read directly from qimta_ai
    // =========================================================================

    // -------------------------------------------------------------------------
    // GET /api/scraper/scraper-brands
    // -------------------------------------------------------------------------

    public function scraperBrands(Request $request): JsonResponse
    {
        $query = ScraperBrand::with('source:id,name,base_url')
            ->orderBy('name');

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        if ($request->boolean('unsynced')) {
            $query->where('is_synced', false);
        }

        $brands = $query->get([
            'id', 'source_id', 'external_id', 'name',
            'is_synced', 'synced_at', 'last_scraped_at',
        ]);

        return response()->json([
            'total' => $brands->count(),
            'data'  => $brands,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/scraper/scraper-categories
    // -------------------------------------------------------------------------

    public function scraperCategories(Request $request): JsonResponse
    {
        $query = ScraperCategory::with('source:id,name,base_url')
            ->orderBy('name');

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        if ($request->boolean('unsynced')) {
            $query->where('is_synced', false);
        }

        $categories = $query->get([
            'id', 'source_id', 'external_id', 'name', 'url',
            'is_synced', 'synced_at', 'last_scraped_at',
        ]);

        return response()->json([
            'total' => $categories->count(),
            'data'  => $categories,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/scraper/scraper-products
    // -------------------------------------------------------------------------

    public function scraperProducts(Request $request): JsonResponse
    {
        $query = ScraperProduct::with([
            'source:id,name,base_url',
            'scraperBrand:id,name',
            'scraperCategory:id,name',
        ])->orderBy('name');

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        if ($request->filled('scraper_brand_id')) {
            $query->where('scraper_brand_id', $request->integer('scraper_brand_id'));
        }

        if ($request->filled('scraper_category_id')) {
            $query->where('scraper_category_id', $request->integer('scraper_category_id'));
        }

        if ($request->boolean('unsynced')) {
            $query->where('is_synced', false);
        }

        $perPage  = min($request->integer('per_page', 50), 200);
        $products = $query->paginate($perPage, [
            'id', 'source_id', 'scraper_brand_id', 'scraper_category_id',
            'external_id', 'source_url', 'sku', 'name', 'description',
            'price', 'is_synced', 'synced_at', 'last_scraped_at',
        ]);

        return response()->json($products);
    }

    // =========================================================================
    // SYNC  –  pull from qimta_ai → push into main DB via queue
    // =========================================================================

    // -------------------------------------------------------------------------
    // POST /api/scraper/sync          – dispatch all three jobs
    // POST /api/scraper/sync/brands
    // POST /api/scraper/sync/categories
    // POST /api/scraper/sync/products
    // -------------------------------------------------------------------------

    public function syncAll(Request $request): JsonResponse
    {
        $sourceId = $request->integer('source_id') ?: null;

        SyncScraperBrandsJob::dispatch($sourceId);
        SyncScraperCategoriesJob::dispatch($sourceId);
        SyncScraperProductsJob::dispatch($sourceId);

        return response()->json(['message' => 'Sync jobs dispatched for brands, categories and products.']);
    }

    public function syncBrands(Request $request): JsonResponse
    {
        $sourceId = $request->integer('source_id') ?: null;
        SyncScraperBrandsJob::dispatch($sourceId);

        return response()->json(['message' => 'Sync job dispatched for brands.']);
    }

    public function syncCategories(Request $request): JsonResponse
    {
        $sourceId = $request->integer('source_id') ?: null;
        SyncScraperCategoriesJob::dispatch($sourceId);

        return response()->json(['message' => 'Sync job dispatched for categories.']);
    }

    public function syncProducts(Request $request): JsonResponse
    {
        $sourceId = $request->integer('source_id') ?: null;
        SyncScraperProductsJob::dispatch($sourceId);

        return response()->json(['message' => 'Sync job dispatched for products.']);
    }

    // -------------------------------------------------------------------------
    // GET /api/scraper/stats   – quick counts from both DBs
    // -------------------------------------------------------------------------

    public function stats(): JsonResponse
    {
        return response()->json([
            'scraper_db' => [
                'sources'    => AiScraperSource::count(),
                'brands'     => ScraperBrand::count(),
                'categories' => ScraperCategory::count(),
                'products'   => ScraperProduct::count(),
                'unsynced'   => [
                    'brands'     => ScraperBrand::where('is_synced', false)->count(),
                    'categories' => ScraperCategory::where('is_synced', false)->count(),
                    'products'   => ScraperProduct::where('is_synced', false)->count(),
                ],
            ],
            'main_db' => [
                'brands'     => Brand::count(),
                'categories' => Category::count(),
                'products'   => Product::count(),
            ],
        ]);
    }
}
