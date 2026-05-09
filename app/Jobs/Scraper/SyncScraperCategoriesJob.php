<?php

namespace App\Jobs\Scraper;

use App\Models\Category;
use App\Models\Scraper\ScraperCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncScraperCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 3;

    public function __construct(public readonly ?int $sourceId = null) {}

    public function handle(): void
    {
        $query = ScraperCategory::query()->where('is_synced', false);

        if ($this->sourceId) {
            $query->where('source_id', $this->sourceId);
        }

        $synced = 0;
        $failed = 0;

        $query->chunkById(200, function ($scraperCategories) use (&$synced, &$failed) {
            foreach ($scraperCategories as $scraperCategory) {
                try {
                    DB::transaction(function () use ($scraperCategory) {
                        $slug = $this->uniqueSlug($scraperCategory->name);

                        Category::firstOrCreate(
                            ['name' => $scraperCategory->name],
                            [
                                'slug'   => $slug,
                                'active' => true,
                            ]
                        );

                        $scraperCategory->update([
                            'is_synced' => true,
                            'synced_at' => now(),
                        ]);
                    });

                    $synced++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('SyncScraperCategoriesJob: failed to sync category', [
                        'scraper_category_id' => $scraperCategory->id,
                        'name'                => $scraperCategory->name,
                        'error'               => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('SyncScraperCategoriesJob: done', compact('synced', 'failed'));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i    = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
