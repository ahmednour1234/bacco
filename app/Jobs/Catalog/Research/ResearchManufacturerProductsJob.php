<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Models\Catalog\Research\ResearchJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Stage 2 — for one manufacturer within a family, create a discover-series
 * ResearchJob and route it through the family orchestrator. Dispatched in
 * batches (config catalog_research.batch.manufacturers_per_batch) by the
 * result-processing stage.
 */
class ResearchManufacturerProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(
        private int $familyId,
        private int $manufacturerId,
    ) {}

    public function handle(): void
    {
        $job = ResearchJob::create([
            'product_family_id' => $this->familyId,
            'manufacturer_id'   => $this->manufacturerId,
            'job_type'          => ResearchJobTypeEnum::DiscoverProductSeries,
            'provider'          => config('catalog_research.provider', 'deepseek'),
            'status'            => ResearchJobStatusEnum::Queued,
            'max_attempts'      => (int) config('services.deepseek.max_retries', 3),
        ]);

        ResearchProductFamilyJob::dispatch($job->id)
            ->onQueue(config('catalog_research.queue', 'catalog-research'));
    }
}
