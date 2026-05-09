<?php

namespace App\Jobs\Scraper;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Scraper\ScraperBrand;
use App\Models\Scraper\ScraperCategory;
use App\Models\Scraper\ScraperProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncScraperProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;
    public int $tries   = 3;

    public function __construct(public readonly ?int $sourceId = null) {}

    public function handle(): void
    {
        // Pre-build lookup maps: scraper brand/category id → main DB id
        $brandMap    = $this->buildBrandMap();
        $categoryMap = $this->buildCategoryMap();

        $query = ScraperProduct::query()->where('is_synced', false);

        if ($this->sourceId) {
            $query->where('source_id', $this->sourceId);
        }

        $synced = 0;
        $failed = 0;

        $query->chunkById(100, function ($scraperProducts) use ($brandMap, $categoryMap, &$synced, &$failed) {
            foreach ($scraperProducts as $sp) {
                try {
                    DB::transaction(function () use ($sp, $brandMap, $categoryMap) {
                        $brandId    = $brandMap[$sp->scraper_brand_id]       ?? null;
                        $categoryId = $categoryMap[$sp->scraper_category_id] ?? null;

                        // Upsert by SKU if present, otherwise by name + brand
                        if ($sp->sku) {
                            Product::updateOrCreate(
                                ['sku' => $sp->sku],
                                $this->productPayload($sp, $brandId, $categoryId)
                            );
                        } else {
                            Product::firstOrCreate(
                                [
                                    'name'     => $sp->name,
                                    'brand_id' => $brandId,
                                ],
                                $this->productPayload($sp, $brandId, $categoryId)
                            );
                        }

                        $sp->update([
                            'is_synced' => true,
                            'synced_at' => now(),
                        ]);
                    });

                    $synced++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('SyncScraperProductsJob: failed to sync product', [
                        'scraper_product_id' => $sp->id,
                        'name'               => $sp->name,
                        'error'              => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('SyncScraperProductsJob: done', compact('synced', 'failed'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<int, ?int>  scraper_brand_id → main brand.id */
    private function buildBrandMap(): array
    {
        $map = [];

        ScraperBrand::query()->select(['id', 'name'])->cursor()->each(function (ScraperBrand $sb) use (&$map) {
            $brand = Brand::where('name', $sb->name)->first(['id']);
            $map[$sb->id] = $brand?->id;
        });

        return $map;
    }

    /** @return array<int, ?int>  scraper_category_id → main category.id */
    private function buildCategoryMap(): array
    {
        $map = [];

        ScraperCategory::query()->select(['id', 'name'])->cursor()->each(function (ScraperCategory $sc) use (&$map) {
            $category = Category::where('name', $sc->name)->first(['id']);
            $map[$sc->id] = $category?->id;
        });

        return $map;
    }

    private function productPayload(ScraperProduct $sp, ?int $brandId, ?int $categoryId): array
    {
        $payload = [
            'name'        => $sp->name,
            'sku'         => $sp->sku,
            'description' => $sp->description,
            'brand_id'    => $brandId,
            'category_id' => $categoryId,
            'active'      => true,
        ];

        // Map scraped specifications into the JSON column if available
        if ($sp->specifications) {
            $payload['specifications'] = is_array($sp->specifications)
                ? $sp->specifications
                : json_decode($sp->specifications, true);
        }

        return $payload;
    }
}
