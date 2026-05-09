<?php

namespace App\Jobs\Scraper;

use App\Models\Brand;
use App\Models\Scraper\ScraperBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncScraperBrandsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 3;

    public function __construct(public readonly ?int $sourceId = null) {}

    public function handle(): void
    {
        $query = ScraperBrand::query()->where('is_synced', false);

        if ($this->sourceId) {
            $query->where('source_id', $this->sourceId);
        }

        $synced = 0;
        $failed = 0;

        $query->chunkById(200, function ($scraperBrands) use (&$synced, &$failed) {
            foreach ($scraperBrands as $scraperBrand) {
                try {
                    DB::transaction(function () use ($scraperBrand) {
                        $brand = Brand::firstOrCreate(
                            ['name' => $scraperBrand->name],
                            ['active' => true]
                        );

                        // Mark as synced in the scraper DB
                        $scraperBrand->update([
                            'is_synced' => true,
                            'synced_at' => now(),
                        ]);
                    });

                    $synced++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('SyncScraperBrandsJob: failed to sync brand', [
                        'scraper_brand_id' => $scraperBrand->id,
                        'name'             => $scraperBrand->name,
                        'error'            => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('SyncScraperBrandsJob: done', compact('synced', 'failed'));
    }
}
