<?php

namespace App\Services\Catalog\Research\DeepSeek;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Models\Catalog\Research\ResearchJob;
use App\Models\Catalog\Research\ResearchJobResult;
use App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;

/**
 * Application-facing entry point for AI catalog research. Phase-4 queue jobs
 * call this; it delegates the actual model call to whichever AiResearchProvider
 * is bound (DeepSeek in production, Fake in tests), and persists the raw +
 * parsed result against the ResearchJob for auditing.
 *
 * This service NEVER treats the AI answer as a final source — it only records
 * what the provider returned. Verification against official sources happens in
 * Phase 5.
 */
class DeepSeekCatalogResearchService
{
    public function __construct(private AiResearchProvider $provider) {}

    public function providerName(): string
    {
        return $this->provider->name();
    }

    /** Run a request without a persisted ResearchJob (e.g. ad-hoc/testing). */
    public function run(ResearchRequest $request): ResearchResponse
    {
        return $this->provider->research($request);
    }

    /**
     * Run a request tied to a ResearchJob: marks it processing, calls the
     * provider, and stores a ResearchJobResult with validation status + counts.
     */
    public function runForJob(ResearchJob $job, ResearchRequest $request): ResearchResponse
    {
        $job->update([
            'status'      => ResearchJobStatusEnum::Processing,
            'started_at'  => now(),
            'attempts'    => $job->attempts + 1,
            'provider'    => $this->provider->name(),
        ]);

        $response = $this->provider->research($request);

        ResearchJobResult::create([
            'research_job_id'   => $job->id,
            'raw_response'      => $response->rawResponse,
            'parsed_response'   => $response->valid ? $response->data : null,
            'validation_status' => $response->valid ? 'valid' : 'invalid',
            'validation_errors' => $response->valid ? null : $response->validationErrors,
            'discovered_count'  => $this->countVariants($response),
            'accepted_count'    => 0, // set by the persistence stage in Phase 5
            'rejected_count'    => 0,
            'duplicate_count'   => 0,
        ]);

        $job->update([
            'status'       => $response->valid
                ? ResearchJobStatusEnum::AwaitingValidation
                : ResearchJobStatusEnum::Failed,
            'completed_at' => $response->valid ? now() : null,
            'failed_at'    => $response->valid ? null : now(),
            'error_message'=> $response->valid ? null : implode('; ', array_slice($response->validationErrors, 0, 3)),
        ]);

        return $response;
    }

    private function countVariants(ResearchResponse $response): int
    {
        $count = 0;
        foreach ($response->series() as $series) {
            foreach ($series['models'] ?? [] as $model) {
                $count += count($model['variants'] ?? []);
            }
        }

        return $count;
    }
}
