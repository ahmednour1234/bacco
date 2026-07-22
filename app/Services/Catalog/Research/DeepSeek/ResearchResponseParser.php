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

        // Coerce common enum synonyms BEFORE validating so a good response with
        // real products is never thrown away over a minor label mismatch (e.g.
        // the model saying "available" instead of "current").
        $json = $this->coerceEnums($json);

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
     * Map common enum synonyms onto the values the schema accepts, so a valid
     * product list is never rejected for a wording difference. Unknown values
     * fall back to a safe default rather than failing the whole response.
     *
     * @param  array<string,mixed>  $json
     * @return array<string,mixed>
     */
    private function coerceEnums(array $json): array
    {
        $availability = [
            'available' => 'current', 'in stock' => 'current', 'in production' => 'current',
            'active' => 'current', 'stock' => 'current', 'current' => 'current',
            'discontinued' => 'discontinued', 'obsolete' => 'discontinued', 'eol' => 'discontinued',
            'regional' => 'regional', 'limited' => 'regional',
        ];

        $verification = [
            'exact_manufacturer_sku' => 'exact_manufacturer_sku',
            'official_model_and_size' => 'official_model_and_size',
            'official_series_range' => 'official_series_range',
            'distributor_only' => 'distributor_only',
            'ai_discovered_unverified' => 'ai_discovered_unverified',
            // common variations
            'exact_sku' => 'exact_manufacturer_sku', 'sku' => 'exact_manufacturer_sku',
            'official_model' => 'official_model_and_size', 'model_and_size' => 'official_model_and_size',
            'series_range' => 'official_series_range', 'range' => 'official_series_range',
            'distributor' => 'distributor_only',
            'unverified' => 'ai_discovered_unverified', 'ai_discovered' => 'ai_discovered_unverified',
        ];

        foreach ($json['series'] ?? [] as &$series) {
            foreach ($series['models'] ?? [] as &$model) {
                foreach ($model['variants'] ?? [] as &$variant) {
                    if (isset($variant['availability_status'])) {
                        $key = strtolower(trim((string) $variant['availability_status']));
                        $variant['availability_status'] = $availability[$key] ?? 'unknown';
                    }
                    if (isset($variant['verification_level'])) {
                        $key = strtolower(trim((string) $variant['verification_level']));
                        $variant['verification_level'] = $verification[$key] ?? 'ai_discovered_unverified';
                    }
                }
                unset($variant);
            }
            unset($model);
            // Variants sometimes sit directly on the series too.
            foreach ($series['variants'] ?? [] as &$variant) {
                if (isset($variant['availability_status'])) {
                    $key = strtolower(trim((string) $variant['availability_status']));
                    $variant['availability_status'] = $availability[$key] ?? 'unknown';
                }
                if (isset($variant['verification_level'])) {
                    $key = strtolower(trim((string) $variant['verification_level']));
                    $variant['verification_level'] = $verification[$key] ?? 'ai_discovered_unverified';
                }
            }
            unset($variant);
        }
        unset($series);

        return $json;
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
