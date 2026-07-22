<?php

namespace App\Services\Catalog\Research\DeepSeek;

use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use App\Services\Catalog\Research\DeepSeek\Schema\JsonSchemaValidator;
use App\Services\Catalog\Research\DeepSeek\Schema\ResearchResponseSchema;

/**
 * Turns a raw provider string into a validated ResearchResponse. Tolerant of
 * stray markdown fences the model may add despite instructions, but strict
 * about the schema: a response that does not match is rejected (never persisted
 * as if valid), so callers can retry or route it to review.
 */
class ResearchResponseParser
{
    public function __construct(private JsonSchemaValidator $validator) {}

    public function parse(string $raw, array $usage = []): ResearchResponse
    {
        $json = $this->extractJson($raw);

        if ($json === null) {
            return ResearchResponse::invalid($raw, ['Response did not contain valid JSON.']);
        }

        $errors = $this->validator->validate($json, ResearchResponseSchema::definition());

        if ($errors !== []) {
            return ResearchResponse::invalid($raw, $errors);
        }

        // Normalize optional top-level arrays so downstream code is simpler.
        $json['series']           ??= [];
        $json['unverified_items'] ??= [];
        $json['warnings']         ??= [];

        return ResearchResponse::valid($json, $raw, $usage);
    }

    /**
     * Extract the JSON object from a raw string. Handles: pure JSON, JSON wrapped
     * in ```json fences, and leading/trailing prose around a single object.
     *
     * @return array<string,mixed>|null
     */
    private function extractJson(string $raw): ?array
    {
        $raw = trim($raw);

        // Strip code fences if present.
        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $raw) ?? $raw;
            $raw = trim($raw);
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fall back: grab the outermost {...} span.
        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $slice   = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
