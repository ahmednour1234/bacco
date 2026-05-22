<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PricingService
{
    /**
     * Items per Gemini call. Multiple calls are made so ALL unpriced items get a price.
     */
    private const GEMINI_CHUNK_SIZE = 100;

    /**
     * Fetch unit prices for an array of quotation items.
     *
     * Strategy:
     *   1. Try the products table first (keyword match on name + optional category match).
     *   2. Collect all unmatched items and send them in ONE batched Gemini call.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>  Items enriched with unit_price, price_source, price_status
     */
    public function fetchPrices(array $items): array
    {
        $unmatched = [];

        foreach ($items as $index => $item) {
            // Skip already-priced rows (e.g., re-fetch after partial approval)
            if (! empty($item['unit_price'])) {
                continue;
            }

            $price = $this->lookupProductsTable($item);

            if ($price !== null) {
                $items[$index]['unit_price']   = $price;
                $items[$index]['price_source'] = 'products';
                $items[$index]['price_status'] = 'pending';
            } else {
                $items[$index]['unit_price']   = null;
                $items[$index]['price_source'] = null;
                $items[$index]['price_status'] = 'pending';
                $unmatched[]                   = $index;
            }
        }

        if (! empty($unmatched)) {
            $chunks = array_chunk($unmatched, self::GEMINI_CHUNK_SIZE);

            if (count($chunks) === 1) {
                // Single chunk — direct call (no pool overhead)
                $items = $this->enrichWithGemini($items, $chunks[0]);
            } else {
                // Multiple chunks — send ALL in parallel to save time
                $items = $this->enrichChunksParallel($items, $chunks);
            }
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Products table lookup
    // -------------------------------------------------------------------------

    /**
     * Search the products table for a matching product by keyword.
     * Returns the unit_price when a confident match is found, null otherwise.
     */
    private function lookupProductsTable(array $item): ?float
    {
        $description = trim((string) ($item['description'] ?? ''));

        if (empty($description)) {
            return null;
        }

        // Extract significant words (≥ 3 chars)
        $words = array_values(array_filter(
            explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', ' ', $description)),
            fn(string $w) => strlen($w) >= 3
        ));

        if (empty($words)) {
            return null;
        }

        $query = Product::query()
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->where('active', true);

        // Require up to 4 keywords to appear in the product name (AND logic)
        foreach (array_slice($words, 0, 4) as $word) {
            $query->where('name', 'like', '%' . $word . '%');
        }

        // Narrow by category name if available
        $category = trim((string) ($item['category'] ?? ''));
        if ($category !== '') {
            $query->whereHas('category', fn($q) => $q->where('name', 'like', '%' . $category . '%'));
        }

        $product = $query->select('unit_price')->first();

        return $product ? (float) $product->unit_price : null;
    }

    // -------------------------------------------------------------------------
    // Gemini fallback — helpers
    // -------------------------------------------------------------------------

    /**
     * Build the compact payload sent to Gemini for a set of item indices.
     */
    private function buildGeminiPayload(array $items, array $indices): array
    {
        $payload = [];
        foreach ($indices as $idx) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 80),
                'cat'  => mb_substr((string) ($items[$idx]['category']    ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand']       ?? ''), 0, 30),
                'unit' => mb_substr((string) ($items[$idx]['unit']        ?? ''), 0, 15),
            ];
        }
        return $payload;
    }

    /**
     * Build the Gemini prompt string for a given payload array.
     */
    private function buildGeminiPrompt(array $payload): string
    {
        return 'You are a procurement pricing expert for the Saudi Arabia construction and MEP materials market. '
            . 'Estimate a realistic current unit price in SAR for each item below. '
            . 'RULES: '
            . '(1) Always return a positive non-zero price — use your best market estimate, never 0. '
            . '(2) Base prices on typical Saudi supplier/contractor rates for 2024-2026. '
            . '(3) Return ONLY a valid compact JSON array with NO whitespace or newlines between elements. '
            . '(4) Each element must be exactly: {"i":<index>,"p":<price_number>} '
            . '(5) No markdown, no explanation, no extra keys, no pretty-printing. '
            . 'Example output: [{"i":0,"p":1500},{"i":1,"p":350}] '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Parse a Gemini text response and apply prices to $items.
     */
    private function applyGeminiText(string $text, array $items, string $model): array
    {
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/i', '', $text);

        $priceData = json_decode($text, true);

        if (! is_array($priceData)) {
            preg_match_all('/\{\s*"i"\s*:\s*(\d+)\s*,\s*"p"\s*:\s*([\d.]+)\s*\}/', $text, $matches, PREG_SET_ORDER);
            if (! empty($matches)) {
                $priceData = array_map(fn($m) => ['i' => (int) $m[1], 'p' => (float) $m[2]], $matches);
                Log::info('PricingService: Partial extraction recovered ' . count($priceData) . ' price(s).');
            }
        }

        if (! is_array($priceData) || empty($priceData)) {
            Log::warning('PricingService: Could not extract any prices from Gemini response.', [
                'model'        => $model,
                'text_preview' => mb_substr($text, 0, 300),
            ]);
            return $items;
        }

        foreach ($priceData as $entry) {
            $idx   = $entry['i'] ?? null;
            $price = $entry['p'] ?? null;

            if ($idx === null || ! array_key_exists($idx, $items)) {
                continue;
            }

            $price = is_numeric($price) ? (float) $price : 0.0;

            if ($price > 0) {
                $items[$idx]['unit_price']   = $price;
                $items[$idx]['price_source'] = 'gemini';
            }
        }

        return $items;
    }

    /**
     * Send multiple chunks to Gemini in PARALLEL using Http::pool().
     * Falls back to sequential per-chunk calls if pool fails.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<list<int>>  $chunks
     * @return array<int, array<string, mixed>>
     */
    private function enrichChunksParallel(array $items, array $chunks): array
    {
        $apiKey       = (string) config('services.gemini.key', '');
        $primaryModel = (string) config('services.gemini.model', 'gemini-2.0-flash-lite');
        $modelChain   = array_unique(array_filter([$primaryModel, 'gemini-2.0-flash-lite', 'gemini-flash-lite-latest', 'gemini-flash-latest']));

        if (empty($apiKey)) {
            Log::warning('PricingService: GEMINI_API_KEY not configured; skipping AI pricing.');
            return $items;
        }

        // Build payloads + request bodies once
        $requestBodies = [];
        foreach ($chunks as $ci => $chunkIndices) {
            $payload             = $this->buildGeminiPayload($items, $chunkIndices);
            $requestBodies[$ci]  = [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $this->buildGeminiPrompt($payload)]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.2,
                    'maxOutputTokens'  => 8192,
                ],
            ];
        }

        // Track which chunks still need pricing
        $failedChunks = array_keys($chunks);

        foreach ($modelChain as $model) {
            if (empty($failedChunks)) {
                break;
            }

            $url            = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $pendingIndices = $failedChunks;
            $failedChunks   = [];

            try {
                $responses = Http::pool(function (Pool $pool) use ($url, $requestBodies, $pendingIndices) {
                    $reqs = [];
                    foreach ($pendingIndices as $ci) {
                        $reqs[] = $pool->as((string) $ci)->timeout(90)->post($url, $requestBodies[$ci]);
                    }
                    return $reqs;
                });

                foreach ($responses as $key => $response) {
                    $ci = (int) $key;

                    if (! $response->successful()) {
                        Log::warning('PricingService: Parallel chunk failed, will retry.', [
                            'model'  => $model,
                            'chunk'  => $ci,
                            'status' => $response->status(),
                        ]);
                        $failedChunks[] = $ci;
                        continue;
                    }

                    $text  = $response->json('candidates.0.content.parts.0.text') ?? '';
                    $items = $this->applyGeminiText($text, $items, $model);
                }
            } catch (\Throwable $e) {
                Log::error('PricingService: Http::pool exception, falling back to serial.', [
                    'model'   => $model,
                    'message' => $e->getMessage(),
                ]);
                // Fall back: process remaining chunks one by one
                foreach ($pendingIndices as $ci) {
                    $items = $this->enrichWithGemini($items, $chunks[$ci]);
                }
                return $items;
            }
        }

        if (! empty($failedChunks)) {
            Log::error('PricingService: Some chunks failed all models.', ['chunks' => $failedChunks]);
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Gemini fallback (single batched request)
    // -------------------------------------------------------------------------

    /**
     * Send all unmatched items to Gemini in ONE request to minimise token consumption.
     * Only the fields Gemini actually needs are sent (description, category, brand, unit).
     * Output tokens are capped via maxOutputTokens to protect against runaway usage.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $unmatchedIndices
     * @return array<int, array<string, mixed>>
     */
    private function enrichWithGemini(array $items, array $unmatchedIndices): array
    {
        $apiKey     = (string) config('services.gemini.key', '');
        $modelChain = array_unique(array_filter([
            (string) config('services.gemini.model', 'gemini-2.0-flash-lite'),
            'gemini-2.0-flash-lite',
            'gemini-flash-lite-latest',
            'gemini-flash-latest',
        ]));

        if (empty($apiKey)) {
            Log::warning('PricingService: GEMINI_API_KEY not configured; skipping AI pricing.');
            return $items;
        }

        $payload    = $this->buildGeminiPayload($items, $unmatchedIndices);
        $prompt     = $this->buildGeminiPrompt($payload);
        $lastStatus = null;

        foreach ($modelChain as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(90)->post($url, [
                    'contents'         => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.2,
                        'maxOutputTokens'  => 8192,
                    ],
                ]);

                if (! $response->successful()) {
                    $lastStatus = $response->status();
                    Log::warning('PricingService: Gemini model failed, trying next.', ['model' => $model, 'status' => $lastStatus]);
                    continue;
                }

                $text  = $response->json('candidates.0.content.parts.0.text') ?? '';
                $items = $this->applyGeminiText($text, $items, $model);

                return $items;

            } catch (\Throwable $e) {
                Log::error('PricingService: Exception calling Gemini.', ['model' => $model, 'message' => $e->getMessage()]);
            }
        }

        Log::error('PricingService: All Gemini models failed.', ['last_status' => $lastStatus]);

        return $items;
    }
}
