<?php

namespace App\Services\Catalog\Research\DeepSeek;

use App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use App\Services\Catalog\Research\DeepSeek\Prompts\ResearchPromptBuilder;
use Illuminate\Support\Facades\Log;

/**
 * DeepSeek implementation of the research provider. Composes the prompt builder,
 * the HTTP client and the response parser. Never throws for a "no results"
 * answer; a hard failure (network/circuit) is surfaced so the queue job can
 * retry it.
 */
class DeepSeekProvider implements AiResearchProvider
{
    public function __construct(
        private ResearchPromptBuilder  $prompts,
        private DeepSeekClient         $client,
        private ResearchResponseParser $parser,
    ) {}

    public function research(ResearchRequest $request): ResearchResponse
    {
        $system = $this->prompts->systemPrompt();
        $user   = $this->prompts->userPrompt($request);

        $result = $this->client->chat($system, $user);

        $response = $this->parser->parse($result['content'], $result['usage'] ?? []);

        if (! $response->valid) {
            Log::warning('DeepSeek research response failed schema validation.', [
                'family' => $request->familyName,
                'type'   => $request->type->value,
                'errors' => array_slice($response->validationErrors, 0, 5),
            ]);
        }

        return $response;
    }

    public function name(): string
    {
        return 'deepseek';
    }
}
