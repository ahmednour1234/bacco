<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Second-pass price validation.
 *
 * After PricingService produces a first unit price (from the products table or a
 * DeepSeek estimate), this service asks DeepSeek to independently RE-CHECK each
 * price against typical Saudi Arabian supplier / market rates and return a verdict:
 *
 *   - confirmed : the price is realistic for the Saudi market → keep it
 *   - corrected : the price is unrealistic → AI supplies a corrected market price
 *   - flagged   : AI cannot confirm → price is doubtful, needs human review
 *
 * The verdict, a verified price, and a short note are returned per item so the
 * caller can persist them. Prices are never silently changed: a correction is
 * recorded alongside the original so the audit trail stays intact.
 */
class PriceVerificationService
{
    /** Items per DeepSeek verification call. Kept small so calls run in parallel. */
    private const CHUNK_SIZE = 10;

    /**
     * How long a verdict stays reusable.
     *
     * Matches PricingService::PRICE_CACHE_DAYS so the price and the judgement of
     * that price expire together — a verdict outliving its price would be an
     * opinion about a number no longer in use.
     */
    private const VERDICT_CACHE_DAYS = 7;

    /**
     * Verify an array of already-priced items.
     *
     * Each input item must contain: id, description, unit, quantity, category,
     * brand, unit_price, price_source. Only rows with a positive unit_price are
     * verified; the rest are returned untouched (verdict = null).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>  Each priced row enriched with:
     *         verified_price (float), price_verdict (string), price_verification_note (string)
     */
    public function verify(array $items): array
    {
        // Only rows that actually carry a price can be verified.
        //
        // A row whose verdict is already cached is skipped entirely. Without
        // this the verifier undid the price cache: the price was reused, then
        // this pass asked the AI again, and a "corrected" verdict overwrote it
        // with a different number — so two runs of the same BOQ disagreed on
        // exactly the rows the model happened to correct.
        $toVerify = [];
        foreach ($items as $index => $item) {
            $price = $item['unit_price'] ?? null;

            if (! is_numeric($price) || (float) $price <= 0) {
                continue;
            }

            $cached = Cache::get($this->verdictCacheKey($item));

            if (is_array($cached)) {
                $items[$index] = array_merge($item, $cached);
                continue;
            }

            $toVerify[] = $index;
        }

        if (empty($toVerify)) {
            return $items;
        }

        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('PriceVerificationService: DEEPSEEK_API_KEY not configured; skipping verification.');
            return $items;
        }

        $chunks = array_chunk($toVerify, self::CHUNK_SIZE);

        $items = count($chunks) === 1
            ? $this->verifyChunk($items, $chunks[0])
            : $this->verifyChunksParallel($items, $chunks);

        $this->cacheVerdicts($items, $toVerify);

        return $items;
    }

    /**
     * Cache key for one row's verdict.
     *
     * Includes the price being judged: the same product at a different price is
     * a different question, and reusing the old verdict would be wrong.
     */
    private function verdictCacheKey(array $item): string
    {
        $description = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) ($item['description'] ?? '')) ?? ''));
        $unit        = mb_strtolower(trim((string) ($item['unit'] ?? '')));
        $price       = (float) ($item['unit_price'] ?? 0);

        return 'price_verdict_' . hash('sha256', $description . '|' . $unit . '|' . $price);
    }

    /**
     * Remember each verdict so a re-run does not re-judge — and so a "corrected"
     * price cannot drift to a new number on every pass.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $indices
     */
    private function cacheVerdicts(array $items, array $indices): void
    {
        foreach ($indices as $index) {
            $item = $items[$index] ?? null;

            if (! $item || empty($item['price_verdict'])) {
                continue;
            }

            // unit_price still holds the price that was sent for judging — the
            // verifier writes its answer to verified_price and leaves the input
            // alone — so the key matches what the next run will look up.
            Cache::put($this->verdictCacheKey($item), [
                'verified_price'          => $item['verified_price'] ?? null,
                'price_verdict'           => $item['price_verdict'],
                'price_verification_note' => $item['price_verification_note'] ?? null,
            ], now()->addDays(self::VERDICT_CACHE_DAYS));
        }
    }

    // -------------------------------------------------------------------------
    // Prompt / payload
    // -------------------------------------------------------------------------

    /**
     * Compact payload: only what the model needs to judge the price.
     */
    private function buildPayload(array $items, array $indices): array
    {
        $payload = [];
        foreach ($indices as $idx) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 80),
                'cat'  => mb_substr((string) ($items[$idx]['category']    ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand']       ?? ''), 0, 30),
                'unit' => mb_substr((string) ($items[$idx]['unit']        ?? ''), 0, 15),
                'p'    => (float) ($items[$idx]['unit_price'] ?? 0),
            ];
        }
        return $payload;
    }

    private function buildPrompt(array $payload): string
    {
        return 'You are a senior procurement auditor for the Saudi Arabia construction and MEP materials market. '
            . 'For each item below you are given a proposed unit price in SAR ("p"). '
            . 'Independently CHECK whether that price is realistic versus typical current Saudi supplier and '
            . 'market rates (2024-2026) for that exact item, unit and brand. '
            . 'RULES: '
            . '(1) Decide a verdict for each item: '
            . '"confirmed" if the given price is within a sensible market range; '
            . '"corrected" if it is clearly too high or too low — then provide the realistic market price; '
            . '"flagged" if you cannot judge it confidently. '
            . '(2) "vp" = the verified market unit price in SAR (a positive number). '
            . 'For "confirmed" set vp equal to the given price. For "corrected" set vp to your realistic price. '
            . 'For "flagged" set vp to the given price. '
            . '(3) "n" = a very short reason in Arabic (max 8 words), e.g. "ضمن نطاق السوق" or "أعلى من سعر السوق بكثير". '
            . '(4) Return ONLY a valid compact JSON array, NO whitespace/newlines between elements, no markdown, no extra keys. '
            . '(5) Each element must be exactly: {"i":<index>,"v":"confirmed|corrected|flagged","vp":<number>,"n":"<reason>"} '
            . 'Example: [{"i":0,"v":"confirmed","vp":1500,"n":"ضمن نطاق السوق"},{"i":1,"v":"corrected","vp":420,"n":"أعلى من السوق"}] '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    // -------------------------------------------------------------------------
    // HTTP
    // -------------------------------------------------------------------------

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

    /**
     * Verify a single chunk with one DeepSeek request.
     */
    private function verifyChunk(array $items, array $indices): array
    {
        $apiKey  = (string) config('services.deepseek.key', '');
        $payload = $this->buildPayload($items, $indices);

        try {
            $response = Http::timeout(90)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', $this->buildRequestBody($payload));

            if (! $response->successful()) {
                Log::warning('PriceVerificationService: DeepSeek request failed.', ['status' => $response->status()]);
                return $items;
            }

            $text = $response->json('choices.0.message.content') ?? '';
            return $this->applyVerdicts($text, $items);
        } catch (\Throwable $e) {
            Log::error('PriceVerificationService: Exception calling DeepSeek.', ['message' => $e->getMessage()]);
            return $items;
        }
    }

    /**
     * Verify several chunks in parallel; fall back to serial on pool failure.
     *
     * @param  list<list<int>>  $chunks
     */
    private function verifyChunksParallel(array $items, array $chunks): array
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

                if (! ($response instanceof \Illuminate\Http\Client\Response) || ! $response->successful()) {
                    Log::warning('PriceVerificationService: parallel chunk failed, retrying serially.', ['chunk' => $ci]);
                    $items = $this->verifyChunk($items, $chunks[$ci]);
                    continue;
                }

                $text  = $response->json('choices.0.message.content') ?? '';
                $items = $this->applyVerdicts($text, $items);
            }
        } catch (\Throwable $e) {
            Log::error('PriceVerificationService: Http::pool exception, falling back to serial.', ['message' => $e->getMessage()]);
            foreach ($chunks as $indices) {
                $items = $this->verifyChunk($items, $indices);
            }
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Response parsing
    // -------------------------------------------------------------------------

    /**
     * Parse a DeepSeek verification response and apply verdicts to $items.
     */
    private function applyVerdicts(string $text, array $items): array
    {
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/i', '', $text);

        $data = json_decode($text, true);

        // Tolerant fallback: pull individual objects out of a malformed blob.
        if (! is_array($data)) {
            preg_match_all(
                '/\{\s*"i"\s*:\s*(\d+)\s*,\s*"v"\s*:\s*"(confirmed|corrected|flagged)"\s*,\s*"vp"\s*:\s*([\d.]+)\s*,\s*"n"\s*:\s*"([^"]*)"\s*\}/u',
                $text,
                $matches,
                PREG_SET_ORDER
            );
            $data = array_map(fn($m) => [
                'i'  => (int) $m[1],
                'v'  => $m[2],
                'vp' => (float) $m[3],
                'n'  => $m[4],
            ], $matches);
        }

        if (! is_array($data) || empty($data)) {
            Log::warning('PriceVerificationService: could not parse any verdicts.', [
                'text_preview' => mb_substr($text, 0, 300),
            ]);
            return $items;
        }

        foreach ($data as $entry) {
            $idx = $entry['i'] ?? null;
            if ($idx === null || ! array_key_exists($idx, $items)) {
                continue;
            }

            $verdict = in_array($entry['v'] ?? null, ['confirmed', 'corrected', 'flagged'], true)
                ? $entry['v']
                : 'flagged';

            $original = (float) ($items[$idx]['unit_price'] ?? 0);
            $vp       = is_numeric($entry['vp'] ?? null) ? (float) $entry['vp'] : $original;

            // A verified price must be positive; otherwise fall back to the original and flag it.
            if ($vp <= 0) {
                $vp      = $original;
                $verdict = 'flagged';
            }

            $items[$idx]['verified_price']          = round($vp, 2);
            $items[$idx]['price_verdict']           = $verdict;
            $items[$idx]['price_verification_note'] = mb_substr((string) ($entry['n'] ?? ''), 0, 255);
        }

        return $items;
    }
}
