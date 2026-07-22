<?php

namespace App\Services\Catalog\Research\DeepSeek\Contracts;

use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;

/**
 * Abstraction over the AI research backend so the module is not tied to
 * DeepSeek. Any future provider (OpenAI-compatible, Gemini, a local model)
 * only needs to implement this one method. The concrete binding lives in
 * CatalogResearchServiceProvider and is chosen by config('catalog_research.provider').
 */
interface AiResearchProvider
{
    /**
     * Execute one research request and return a validated response.
     *
     * Implementations MUST:
     *  - send the system prompt that forbids inventing products,
     *  - request strict JSON output,
     *  - validate the raw response against the module JSON schema,
     *  - never throw for a "no results" answer (return an empty ResearchResponse),
     *  - log the call with secrets scrubbed.
     */
    public function research(ResearchRequest $request): ResearchResponse;

    /** Machine name of the provider, e.g. "deepseek". */
    public function name(): string;
}
