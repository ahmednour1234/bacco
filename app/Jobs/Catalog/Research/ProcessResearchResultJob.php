<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\Contracts\ResearchResultPersister;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use App\Services\Catalog\Research\ResearchProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Takes the validated result stored on a ResearchJob and persists it into the
 * catalog via the (Phase-5) ResearchResultPersister, then advances the family's
 * research status. Persistence itself is idempotent (normalized_variant_key), so
 * a re-run never duplicates variants.
 */
class ProcessResearchResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(private int $researchJobId) {}

    public function handle(
        ResearchResultPersister  $persister,
        ResearchProgressService  $progress,
    ): void {
        $job = ResearchJob::with('results')->find($this->researchJobId);
        if (! $job || $job->status === ResearchJobStatusEnum::Cancelled) {
            return;
        }

        $result = $job->results()->latest('id')->first();
        if (! $result || $result->validation_status !== 'valid' || ! $result->parsed_response) {
            return;
        }

        $response = ResearchResponse::valid($result->parsed_response, (string) $result->raw_response);

        try {
            $counts = $persister->persist($job, $response);
        } catch (\Throwable $e) {
            Log::error('Persisting research result failed.', ['job' => $job->id, 'message' => $e->getMessage()]);
            $job->update([
                'status'        => ResearchJobStatusEnum::Failed,
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
            ]);

            return;
        }

        $result->update([
            'accepted_count'  => $counts['accepted'],
            'rejected_count'  => $counts['rejected'],
            'duplicate_count' => $counts['duplicate'],
        ]);

        $job->update([
            'status'       => ResearchJobStatusEnum::Completed,
            'completed_at' => now(),
        ]);

        // Advance the family's status based on the whole plan's progress.
        $progress->refreshFamilyStatus($job->family);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessResearchResultJob died.', [
            'job'     => $this->researchJobId,
            'message' => $e->getMessage(),
        ]);
    }
}
