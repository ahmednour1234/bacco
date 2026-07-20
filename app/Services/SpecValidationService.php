<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pre-pricing spec validation.
 *
 * Before any BOQ item is priced, this service asks DeepSeek to audit each line
 * the way a Saudi procurement/BOQ reviewer would, and classify it:
 *
 *   - valid             : unit is correct and specs are complete enough to price.
 *   - unit_error        : the unit does not match the item's nature. A corrected
 *                         unit is suggested (e.g. concrete priced per ton → m3).
 *   - needs_information : specs are missing that prevent an accurate price. The AI
 *                         returns the specific questions the user must answer.
 *
 * Scope note: SUPPLY ONLY. Installation / testing / commissioning are ignored.
 * The AI must NOT invent brands, models, or specs that aren't present.
 *
 * This is Phase 1 (unit + missing-spec). Duplication and price-gradient checks
 * are intentionally out of scope here.
 */
class SpecValidationService
{
    /** Items per DeepSeek call. Kept small so calls run in parallel. */
    private const CHUNK_SIZE = 10;

    /**
     * How long a spec verdict stays reusable.
     *
     * Matches the extraction and questions caches: a verdict about a row should
     * not outlive the rows it was reached from.
     */
    private const SPEC_CACHE_DAYS = 30;

    /**
     * Validate an array of items.
     *
     * Each input item should carry: id, description, unit, quantity, category, brand.
     * Returns the same array with each row enriched with:
     *   validation_status (string), suggested_unit (string|null),
     *   missing_specs (array<int,array{key,question,example}>), validation_note (string)
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function validate(array $items): array
    {
        if (empty($items)) {
            return $items;
        }

        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('SpecValidationService: DEEPSEEK_API_KEY not configured; skipping validation.');
            return $items;
        }

        // Reuse a verdict already reached for this exact row.
        //
        // Re-validating the same BOQ re-asked the AI about every row and could
        // reach a different conclusion each time, so the same item was flagged
        // on one pass and cleared on the next.
        $indices = [];

        foreach ($items as $index => $item) {
            $cached = Cache::store('ai')->get($this->specCacheKey($item));

            if (is_array($cached)) {
                $items[$index] = array_merge($item, $cached);
                continue;
            }

            $indices[] = $index;
        }

        if (empty($indices)) {
            return $items;
        }

        $chunks = array_chunk($indices, self::CHUNK_SIZE);

        $items = count($chunks) === 1
            ? $this->validateChunk($items, $chunks[0])
            : $this->validateChunksParallel($items, $chunks);

        $this->cacheSpecVerdicts($items, $indices);

        return $items;
    }

    /**
     * Cache key for one row's spec verdict.
     *
     * Keyed on the fields the validator reads, so editing a description or unit
     * re-validates while an untouched row does not.
     */
    private function specCacheKey(array $item): string
    {
        $signature = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) ($item['description'] ?? '')) ?? ''))
            . '|' . mb_strtolower(trim((string) ($item['unit'] ?? '')))
            . '|' . (float) ($item['quantity'] ?? 0);

        return 'spec_verdict_' . hash('sha256', $signature);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $indices
     */
    private function cacheSpecVerdicts(array $items, array $indices): void
    {
        foreach ($indices as $index) {
            $item = $items[$index] ?? null;

            if (! $item || ! isset($item['validation_status'])) {
                continue;
            }

            Cache::store('ai')->put($this->specCacheKey($item), [
                'validation_status' => $item['validation_status'],
                'validation_note'   => $item['validation_note']   ?? null,
                'suggested_unit'    => $item['suggested_unit']    ?? null,
            ], now()->addDays(self::SPEC_CACHE_DAYS));
        }
    }

    // -------------------------------------------------------------------------
    // Prompt / payload
    // -------------------------------------------------------------------------

    private function buildPayload(array $items, array $indices): array
    {
        $payload = [];
        foreach ($indices as $idx) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 120),
                'unit' => mb_substr((string) ($items[$idx]['unit']        ?? ''), 0, 15),
                'qty'  => (float) ($items[$idx]['quantity'] ?? 0),
                'cat'  => mb_substr((string) ($items[$idx]['category']    ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand']       ?? ''), 0, 30),
            ];
        }
        return $payload;
    }

    private function buildPrompt(array $payload): string
    {
        return 'You are a senior BOQ reviewer and pricing auditor for the Saudi Arabia (Riyadh) '
            . 'construction and MEP materials market. For each item below, review it BEFORE pricing '
            . 'and classify it. This is a SUPPLY-ONLY review: ignore installation, testing, and '
            . 'commissioning wording. NEVER invent a brand, model, or specification that is not present. '
            . 'CHECK TWO THINGS: '
            . '(A) UNIT CORRECTNESS — is the unit appropriate for the item nature? Examples: '
            . 'ready-mix concrete → m3 (not ton); steel/rebar → ton or kg; paint, plaster, tiling → m2; '
            . 'pipes and cables → meter WITH a size; equipment → number/Set. '
            . 'If the unit is wrong, verdict = "unit_error" and put the correct unit in "su". '
            . '(B) SPEC COMPLETENESS — are the specs enough to give an accurate unit price? '
            . 'Pipes need: size, material, PN/SDR/Schedule, brand. '
            . 'Cables need: cross-section (mm2), number of cores, copper/aluminium, insulation, voltage, brand. '
            . 'Pumps need: flow, head, motor power, duty/standby, brand. '
            . 'Aluminium/glazing need: profile type, thickness, thermal-break?, glass type/thickness. '
            . 'Ductwork needs: sheet gauge, pressure class. Insulation needs: thickness, density, material. '
            . 'If required specs are missing, verdict = "needs_information" and list the missing ones as questions. '
            . 'If unit is correct AND specs are sufficient, verdict = "valid". '
            . 'RULES: '
            . '(1) "vs" = "valid" | "unit_error" | "needs_information". '
            . '(2) "su" = corrected unit string (only meaningful for unit_error; else ""). '
            . '(3) "q" = array of missing-info questions, each '
            . '{"k":"<short_key>","q":"<question in Arabic>","ex":"<example>","sv":"<suggested value>"}. '
            . 'Empty array [] when nothing is missing. Keep to the essential questions only (max 4). '
            . '(4) "sv" is REQUIRED on every question: the single most likely value for this item in the '
            . 'Saudi market, inferred from the description itself and standard practice — so the line can be '
            . 'priced WITHOUT asking the user. Infer only what the description reasonably implies (e.g. an '
            . '"LED panel 60x60" implies a recessed 40W 4000K panel; a "UPS 3 KVA Online" implies a tower '
            . 'unit with internal batteries). Use "" ONLY when no defensible default exists — never guess a brand. '
            . '(5) "n" = a very short note in Arabic (max 10 words) explaining the verdict. '
            . '(6) Return ONLY a compact JSON array, no whitespace/newlines between elements, no markdown, no extra keys. '
            . '(7) Each element exactly: {"i":<index>,"vs":"<verdict>","su":"<unit>","q":[...],"n":"<note>"} '
            . 'Example: [{"i":0,"vs":"needs_information","su":"","q":[{"k":"size","q":"ما مقاس الماسورة؟","ex":"110mm","sv":"110mm"}],"n":"ينقص المقاس والخامة"},{"i":1,"vs":"unit_error","su":"م3","q":[],"n":"الخرسانة تقاس بالمتر المكعب"}] '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function buildRequestBody(array $payload): array
    {
        return [
            'model'       => (string) config('services.deepseek.model', 'deepseek-chat'),
            'messages'    => [
                ['role' => 'user', 'content' => $this->buildPrompt($payload)],
            ],
            'temperature' => 0,
            'max_tokens'  => 8192,
            'user'        => 'Qimta_Platform',
        ];
    }

    // -------------------------------------------------------------------------
    // HTTP
    // -------------------------------------------------------------------------

    private function validateChunk(array $items, array $indices): array
    {
        $apiKey  = (string) config('services.deepseek.key', '');
        $payload = $this->buildPayload($items, $indices);

        try {
            $response = Http::timeout(90)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', $this->buildRequestBody($payload));

            if (! $response->successful()) {
                Log::warning('SpecValidationService: DeepSeek request failed.', ['status' => $response->status()]);
                return $items;
            }

            $text = $response->json('choices.0.message.content') ?? '';
            return $this->applyResults($text, $items);
        } catch (\Throwable $e) {
            Log::error('SpecValidationService: Exception calling DeepSeek.', ['message' => $e->getMessage()]);
            return $items;
        }
    }

    /**
     * @param  list<list<int>>  $chunks
     */
    private function validateChunksParallel(array $items, array $chunks): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        $url    = 'https://api.deepseek.com/chat/completions';

        $bodies = [];
        foreach ($chunks as $ci => $indices) {
            $bodies[$ci] = $this->buildRequestBody($this->buildPayload($items, $indices));
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($url, $apiKey, $bodies) {
                $reqs = [];
                foreach ($bodies as $ci => $body) {
                    $reqs[] = $pool->as((string) $ci)
                        ->timeout(90)
                        ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                        ->post($url, $body);
                }
                return $reqs;
            });

            foreach ($responses as $key => $response) {
                $ci = (int) $key;

                if (! ($response instanceof Response) || ! $response->successful()) {
                    Log::warning('SpecValidationService: parallel chunk failed, retrying serially.', ['chunk' => $ci]);
                    $items = $this->validateChunk($items, $chunks[$ci]);
                    continue;
                }

                $text  = $response->json('choices.0.message.content') ?? '';
                $items = $this->applyResults($text, $items);
            }
        } catch (\Throwable $e) {
            Log::error('SpecValidationService: Http::pool exception, falling back to serial.', ['message' => $e->getMessage()]);
            foreach ($chunks as $indices) {
                $items = $this->validateChunk($items, $indices);
            }
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Response parsing
    // -------------------------------------------------------------------------

    private function applyResults(string $text, array $items): array
    {
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/i', '', $text);

        $data = json_decode($text, true);

        if (! is_array($data) || empty($data)) {
            Log::warning('SpecValidationService: could not parse validation results.', [
                'text_preview' => mb_substr($text, 0, 300),
            ]);
            return $items;
        }

        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $idx = $entry['i'] ?? null;
            if ($idx === null || ! array_key_exists($idx, $items)) {
                continue;
            }

            $verdict = in_array($entry['vs'] ?? null, ['valid', 'unit_error', 'needs_information'], true)
                ? $entry['vs']
                : 'valid';

            // Normalise the questions array to a stable shape.
            $questions = [];
            foreach ((array) ($entry['q'] ?? []) as $q) {
                if (! is_array($q)) {
                    continue;
                }
                $key      = trim((string) ($q['k'] ?? ''));
                $question = trim((string) ($q['q'] ?? ''));
                if ($question === '') {
                    continue;
                }
                $questions[] = [
                    'key'      => $key !== '' ? $key : 'spec_' . count($questions),
                    'question' => mb_substr($question, 0, 255),
                    'example'  => mb_substr((string) ($q['ex'] ?? ''), 0, 100),
                    // Most likely value, used to auto-resolve the line without
                    // interrupting the user. Empty when the AI had no basis.
                    'suggested' => mb_substr(trim((string) ($q['sv'] ?? '')), 0, 150),
                ];
            }

            // A "needs_information" verdict with no questions is not actionable —
            // downgrade it to valid so it doesn't block the flow forever.
            if ($verdict === 'needs_information' && empty($questions)) {
                $verdict = 'valid';
            }

            $items[$idx]['validation_status'] = $verdict;
            $items[$idx]['suggested_unit']    = $verdict === 'unit_error'
                ? mb_substr((string) ($entry['su'] ?? ''), 0, 50)
                : null;
            $items[$idx]['missing_specs']   = $questions;
            $items[$idx]['validation_note'] = mb_substr((string) ($entry['n'] ?? ''), 0, 255);
        }

        return $items;
    }
}
