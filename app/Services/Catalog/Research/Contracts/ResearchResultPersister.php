<?php

namespace App\Services\Catalog\Research\Contracts;

use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;

/**
 * Persists a validated research response into the catalog (series, models,
 * variants, sources, evidence, approvals) applying the anti-hallucination and
 * verification rules. Implemented in Phase 5; declared here so Phase 4's result
 * job can depend on the abstraction.
 *
 * @return array{accepted:int, rejected:int, duplicate:int}
 */
interface ResearchResultPersister
{
    /**
     * @return array{accepted:int, rejected:int, duplicate:int}
     */
    public function persist(ResearchJob $job, ResearchResponse $response): array;
}
