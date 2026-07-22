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
 * Stage 3/4 — discover variants for a specific series/model context. Thin
 * wrapper that creates the typed ResearchJob and hands it to the orchestrator.
 */
class ResearchProductVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    /** @param array<string,mixed> $context */
    public function __construct(
        private int $familyId,
        private int $manufacturerId,
        private array $context,
    ) {}

    public function handle(): void
    {
        $job = ResearchJob::create([
            'product_family_id' => $this->familyId,
            'manufacturer_id'   => $this->manufacturerId,
            'job_type'          => ResearchJobTypeEnum::DiscoverVariants,
            'provider'          => config('catalog_research.provider', 'deepseek'),
            'input_payload'     => $this->context,
            'status'            => ResearchJobStatusEnum::Queued,
            'max_attempts'      => (int) config('services.deepseek.max_retries', 3),
        ]);

        ResearchProductFamilyJob::dispatch($job->id)
            ->onQueue(config('catalog_research.queue', 'catalog-research'));
    }
}
