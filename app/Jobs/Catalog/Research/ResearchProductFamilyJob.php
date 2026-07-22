<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\DeepSeek\DeepSeekCatalogResearchService;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs one research stage for a family via the (swappable) AI provider, stores
 * the result on the ResearchJob, then hands the parsed response to
 * ProcessResearchResultJob for persistence. Cancelled jobs are skipped so
 * pause/cancel take effect immediately.
 */
class ResearchProductFamilyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1; // provider owns its own retry/backoff

    public function __construct(private int $researchJobId) {}

    public function handle(DeepSeekCatalogResearchService $service): void
    {
        $job = ResearchJob::find($this->researchJobId);
        if (! $job) {
            return;
        }

        // Respect pause/cancel: a job flipped to cancelled must not run.
        if (in_array($job->status, [
            ResearchJobStatusEnum::Cancelled,
            ResearchJobStatusEnum::Completed,
        ], true)) {
            return;
        }

        $family = $job->family;
        if (! $family) {
            return;
        }

        $family->update(['research_status' => ResearchStatusEnum::Researching]);

        $request = ResearchRequest::make(
            type: $job->job_type,
            familyName: $family->name,
            normalizedFamilyName: $family->normalized_name,
            context: (array) ($job->input_payload ?? []),
            manufacturerName: $job->manufacturer?->name,
            marketScope: $family->research_scope?->value,
        );

        try {
            $response = $service->runForJob($job, $request);
        } catch (\Throwable $e) {
            Log::warning('Research stage failed.', ['job' => $job->id, 'message' => $e->getMessage()]);
            $job->update([
                'status'        => ResearchJobStatusEnum::Failed,
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
            ]);
            $family->update(['research_status' => ResearchStatusEnum::Failed]);

            return;
        }

        if ($response->valid) {
            ProcessResearchResultJob::dispatch($job->id)
                ->onQueue(config('catalog_research.queue', 'default'));
        } else {
            $family->update(['research_status' => ResearchStatusEnum::NeedsReview]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ResearchProductFamilyJob died.', [
            'job'     => $this->researchJobId,
            'message' => $e->getMessage(),
        ]);
    }
}
