<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use App\Jobs\Catalog\Research\ResearchProductFamilyJob;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ResearchJob;
use App\Repositories\Catalog\Research\ResearchJobRepository;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Owns the lifecycle of research for a Product Family: build a plan, dispatch it
 * through the queue in stages/batches, and expose pause / resume / cancel /
 * retry. A cache lock prevents two research runs for the same family at once.
 *
 * No AI calls happen here — this only schedules work onto the queue.
 */
class ResearchPlanService
{
    public function __construct(private ResearchJobRepository $jobRepo) {}

    private function queue(): string
    {
        return config('catalog_research.queue', 'catalog-research');
    }

    private function lockKey(int $familyId): string
    {
        return "catalog-research:family-lock:{$familyId}";
    }

    /**
     * Start research for a family. Creates the first-stage job (discover
     * manufacturers) and dispatches it; later stages are chained as each stage's
     * results arrive. Guards against duplicate concurrent runs.
     */
    public function start(ProductFamily $family): ResearchJob
    {
        if (! $family->research_status->canStart()) {
            throw new RuntimeException("Research cannot start from status: {$family->research_status->value}");
        }

        // Prevent a second concurrent run for the same family.
        $lock = Cache::lock($this->lockKey($family->id), 30);
        if (! $lock->get()) {
            throw new RuntimeException('Research is already being scheduled for this family.');
        }

        try {
            if ($this->jobRepo->activeCountForFamily($family->id) > 0) {
                throw new RuntimeException('Research is already running for this family.');
            }

            $family->update(['research_status' => ResearchStatusEnum::Queued]);

            $job = $this->jobRepo->create([
                'product_family_id' => $family->id,
                'job_type'          => ResearchJobTypeEnum::DiscoverManufacturers,
                'provider'          => config('catalog_research.provider', 'deepseek'),
                'research_query'    => $family->name,
                'status'            => ResearchJobStatusEnum::Pending,
                'priority'          => $family->research_priority,
                'max_attempts'      => (int) config('services.deepseek.max_retries', 3),
                'created_by'        => $family->created_by,
            ]);

            $this->jobRepo->markQueued($job);
            ResearchProductFamilyJob::dispatch($job->id)->onQueue($this->queue());

            return $job;
        } finally {
            $lock->release();
        }
    }

    /** Pause: stop scheduling new jobs; keep completed work. */
    public function pause(ProductFamily $family): void
    {
        $this->jobRepo->cancelPendingForFamily($family->id, ResearchJobStatusEnum::Cancelled);
        $family->update(['research_status' => ResearchStatusEnum::Paused]);
    }

    /** Resume a paused family by re-dispatching the discovery stage. */
    public function resume(ProductFamily $family): ResearchJob
    {
        if ($family->research_status !== ResearchStatusEnum::Paused) {
            throw new RuntimeException('Only a paused family can be resumed.');
        }

        return $this->start($family->fresh());
    }

    /** Cancel: stop everything and mark the family failed/needs-review. */
    public function cancel(ProductFamily $family): void
    {
        $this->jobRepo->cancelPendingForFamily($family->id, ResearchJobStatusEnum::Cancelled);
        $family->update(['research_status' => ResearchStatusEnum::NeedsReview]);
    }

    /** Retry a single failed job. */
    public function retryJob(ResearchJob $job): void
    {
        if ($job->status !== ResearchJobStatusEnum::Failed) {
            throw new RuntimeException('Only a failed job can be retried.');
        }

        $this->jobRepo->resetForRetry($job);
        $this->jobRepo->markQueued($job);

        // Re-dispatch the appropriate stage job.
        $this->dispatchStage($job);
    }

    /**
     * Dispatch the queue job that handles a given ResearchJob's stage. Kept in
     * one place so start()/resume()/retry() and chaining all agree.
     */
    public function dispatchStage(ResearchJob $job): void
    {
        // For now every stage flows through the family orchestrator, which reads
        // the job_type and calls the right provider stage. Later stages
        // (series/variants) are enqueued by ProcessResearchResultJob.
        ResearchProductFamilyJob::dispatch($job->id)->onQueue($this->queue());
    }

    public function progress(ProductFamily $family): int
    {
        return $this->jobRepo->progressForFamily($family->id);
    }
}
