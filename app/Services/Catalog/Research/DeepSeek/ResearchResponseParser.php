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

        // Reshape common alternative structures the model returns (e.g. a
        // `manufacturer` array with series nested inside each manufacturer) into
        // the single-object + top-level-series shape the schema expects.
        $json = $this->normalizeShape($json);

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
     * Reshape alternative response structures into the canonical one:
     *   - `manufacturer` as an ARRAY of manufacturers, each with its own nested
     *     `series` → flatten to a single top-level `series` array, tagging every
     *     series/variant with its manufacturer name so persistence knows who
     *     makes it.
     *   - `manufacturers` (plural) treated the same way.
     *   - a manufacturer object that itself carries `series` → lift those series
     *     to the top level.
     *
     * @param  array<string,mixed>  $json
     * @return array<string,mixed>
     */
    private function normalizeShape(array $json): array
    {
        $series = $json['series'] ?? [];

        // Collect manufacturer nodes from any of the shapes we've seen.
        $manufacturerNodes = [];
        if (isset($json['manufacturers']) && is_array($json['manufacturers']) && array_is_list($json['manufacturers'])) {
            $manufacturerNodes = $json['manufacturers'];
        } elseif (isset($json['manufacturer']) && is_array($json['manufacturer']) && array_is_list($json['manufacturer'])) {
            $manufacturerNodes = $json['manufacturer'];
        }

        if ($manufacturerNodes !== []) {
            $firstManufacturer = null;

            foreach ($manufacturerNodes as $mfr) {
                if (! is_array($mfr)) {
                    continue;
                }
                $name = $mfr['name'] ?? null;
                $firstManufacturer ??= [
                    'name'             => $name,
                    'official_website' => $mfr['official_website'] ?? null,
                    'country'          => $mfr['country'] ?? null,
                ];

                foreach ($mfr['series'] ?? [] as $s) {
                    if (! is_array($s)) {
                        continue;
                    }
                    // Tag the series and its variants with this manufacturer.
                    $s['manufacturer'] = $name;
                    foreach ($s['models'] ?? [] as &$m) {
                        foreach ($m['variants'] ?? [] as &$v) {
                            $v['manufacturer'] ??= $name;
                        }
                        unset($v);
                    }
                    unset($m);
                    foreach ($s['variants'] ?? [] as &$v) {
                        $v['manufacturer'] ??= $name;
                    }
                    unset($v);

                    $series[] = $s;
                }
            }

            // Replace the array manufacturer with a single representative object
            // and the collected series at the top level.
            $json['manufacturer'] = $firstManufacturer;
            $json['series']       = $series;
        } elseif (isset($json['manufacturer']['series']) && is_array($json['manufacturer']['series'])) {
            // Single manufacturer object that nests its own series.
            $name = $json['manufacturer']['name'] ?? null;
            foreach ($json['manufacturer']['series'] as $s) {
                if (! is_array($s)) {
                    continue;
                }
                $s['manufacturer'] = $name;
                $series[] = $s;
            }
            unset($json['manufacturer']['series']);
            $json['series'] = $series;
        }

        return $json;
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
