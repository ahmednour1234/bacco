<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\DeepSeek\DeepSeekCatalogResearchService;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enumerates one page of one manufacturer's published catalog.
 *
 * This is how the catalog scales to seven figures without inventing anything:
 * a maker like Schneider publishes tens of thousands of real SKUs, so growth
 * comes from reading their catalog page by page — not from multiplying sizes
 * against connections.
 *
 * Paginated and self-chaining: each run asks for one page and, if that page
 * returned products, queues the next. An empty page ends the sweep for that
 * manufacturer/category.
 */
class SweepManufacturerCatalogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1; // the provider owns retry/backoff

    public function __construct(
        private readonly int $manufacturerId,
        private readonly string $category,
        private readonly int $page = 1,
        private readonly int $perPage = 10,
        private readonly int $maxPages = 20,
    ) {}

    public function handle(DeepSeekCatalogResearchService $service): void
    {
        $manufacturer = Manufacturer::find($this->manufacturerId);

        if (! $manufacturer) {
            return;
        }

        if ($this->page > $this->maxPages) {
            Log::info('Catalog sweep reached its page cap.', [
                'manufacturer' => $manufacturer->name,
                'category'     => $this->category,
                'max_pages'    => $this->maxPages,
            ]);

            return;
        }

        // Models already stored for this maker, so the prompt can skip them and
        // each page returns genuinely new products instead of repeats.
        $knownModels = $this->knownModels($manufacturer->id);

        $job = ResearchJob::create([
            'uuid'            => (string) \Illuminate\Support\Str::uuid(),
            'manufacturer_id' => $manufacturer->id,
            'job_type'        => ResearchJobTypeEnum::ManufacturerCatalogSweep,
            'provider'        => config('catalog_research.provider', 'deepseek'),
            'model_name'      => config('catalog_research.deepseek.model'),
            'research_query'  => "{$manufacturer->name} — {$this->category} (page {$this->page})",
            'input_payload'   => [
                'manufacturer' => $manufacturer->name,
                'website'      => $manufacturer->official_website,
                'category'     => $this->category,
                'page'         => $this->page,
                'per_page'     => $this->perPage,
                'known_models' => $knownModels,
            ],
            'status'          => ResearchJobStatusEnum::Processing,
            'priority'        => 5,
            'attempts'        => 0,
            'max_attempts'    => 1,
            'started_at'      => now(),
        ]);

        $request = ResearchRequest::make(
            type: ResearchJobTypeEnum::ManufacturerCatalogSweep,
            familyName: $this->category,
            normalizedFamilyName: mb_strtolower($this->category),
            context: (array) $job->input_payload,
            manufacturerName: $manufacturer->name,
        );

        try {
            $response = $service->runForJob($job, $request);
        } catch (\Throwable $e) {
            Log::warning('Catalog sweep page failed.', [
                'manufacturer' => $manufacturer->name,
                'page'         => $this->page,
                'message'      => $e->getMessage(),
            ]);

            $job->update([
                'status'        => ResearchJobStatusEnum::Failed,
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
            ]);

            return;
        }

        if (! $response->valid) {
            // A malformed page ends this sweep rather than looping on garbage.
            return;
        }

        // Persist through the normal pipeline, which enforces the source and
        // verification rules — expansion gets no shortcut around them.
        ProcessResearchResultJob::dispatchSync($job->id);

        $returned = count($response->data['series'] ?? []);

        Log::info('Catalog sweep page done.', [
            'manufacturer' => $manufacturer->name,
            'category'     => $this->category,
            'page'         => $this->page,
            'series'       => $returned,
        ]);

        // An empty page means the manufacturer has no more products here.
        if ($returned === 0) {
            return;
        }

        self::dispatch(
            $this->manufacturerId,
            $this->category,
            $this->page + 1,
            $this->perPage,
            $this->maxPages,
        )->onQueue(config('catalog_research.queue', 'default'));
    }

    /**
     * Model numbers already recorded for this manufacturer. Capped because the
     * list is injected into the prompt and must not blow up the token budget.
     *
     * @return list<string>
     */
    private function knownModels(int $manufacturerId): array
    {
        return DB::connection('catalog')
            ->table('product_models')
            ->where('manufacturer_id', $manufacturerId)
            ->whereNotNull('model_number')
            ->orderByDesc('id')
            ->limit(60)
            ->pluck('model_number')
            ->filter()
            ->values()
            ->all();
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SweepManufacturerCatalogJob died.', [
            'manufacturer_id' => $this->manufacturerId,
            'category'        => $this->category,
            'page'            => $this->page,
            'message'         => $e->getMessage(),
        ]);
    }
}
