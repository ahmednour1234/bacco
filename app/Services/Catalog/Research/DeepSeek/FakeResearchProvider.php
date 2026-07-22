<?php

namespace App\Services\Catalog\Research\DeepSeek;

use App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;

/**
 * Deterministic in-memory provider for tests. Makes NO network calls. Either
 * returns a queued canned response or, by default, echoes a minimal valid
 * empty response. Use ->queue() / ->queueRaw() to script behaviour.
 */
class FakeResearchProvider implements AiResearchProvider
{
    /** @var list<ResearchResponse> */
    private array $responses = [];

    /** @var list<ResearchRequest> */
    public array $received = [];

    private ResearchResponseParser $parser;

    public function __construct(?ResearchResponseParser $parser = null)
    {
        $this->parser = $parser ?? app(ResearchResponseParser::class);
    }

    /** Queue a ready-made response object. */
    public function queue(ResearchResponse $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    /** Queue a raw JSON string; it is parsed+validated like the real thing. */
    public function queueRaw(string $rawJson): self
    {
        $this->responses[] = $this->parser->parse($rawJson);

        return $this;
    }

    public function research(ResearchRequest $request): ResearchResponse
    {
        $this->received[] = $request;

        return array_shift($this->responses) ?? ResearchResponse::empty();
    }

    public function name(): string
    {
        return 'fake';
    }
}
