<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PricingService
{
    /**
     * Maximum items sent to Gemini per request.
     * Keeps token usage under control — items beyond this limit remain unpriced via Gemini.
     */
    private const MAX_GEMINI_BATCH = 25;

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
            $items = $this->enrichWithGemini($items, $unmatched);
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
        $apiKey       = (string) config('services.gemini.key', '');
        $primaryModel = (string) config('services.gemini.model', 'gemini-2.0-flash-lite');
        // Try models in order — all confirmed available for this API key
        $modelChain   = array_unique(array_filter([$primaryModel, 'gemini-2.0-flash-lite', 'gemini-flash-lite-latest', 'gemini-flash-latest']));

        if (empty($apiKey)) {
            Log::warning('PricingService: GEMINI_API_KEY not configured; skipping AI pricing.');
            return $items;
        }

        // Token protection: cap batch size
        $batch = array_slice($unmatchedIndices, 0, self::MAX_GEMINI_BATCH);

        // Compact payload — only essential fields to minimise input tokens
        $payload = [];
        foreach ($batch as $idx) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 80),
                'cat'  => mb_substr((string) ($items[$idx]['category']    ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand']       ?? ''), 0, 30),
                'unit' => mb_substr((string) ($items[$idx]['unit']        ?? ''), 0, 15),
            ];
        }

        $prompt = 'You are a procurement pricing expert for the Saudi Arabia construction and MEP materials market. '
            . 'Estimate a realistic current unit price in SAR for each item below. '
            . 'RULES: '
            . '(1) Always return a positive non-zero price — use your best market estimate, never 0. '
            . '(2) Base prices on typical Saudi supplier/contractor rates for 2024-2026. '
            . '(3) Return ONLY a valid compact JSON array with NO whitespace or newlines between elements. '
            . '(4) Each element must be exactly: {"i":<index>,"p":<price_number>} '
            . '(5) No markdown, no explanation, no extra keys, no pretty-printing. '
            . 'Example output: [{"i":0,"p":1500},{"i":1,"p":350}] '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);

        $lastStatus = null;

        foreach ($modelChain as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(60)->post($url, [
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.2,
                        'maxOutputTokens'  => 8192,
                    ],
                ]);

                if (! $response->successful()) {
                    $lastStatus = $response->status();
                    Log::warning('PricingService: Gemini model failed, trying next.', [
                        'model'  => $model,
                        'status' => $lastStatus,
                    ]);
                    continue; // try next model
                }

                $text = $response->json('candidates.0.content.parts.0.text') ?? '';
                $text = preg_replace('/^```json\s*/i', '', trim($text));
                $text = preg_replace('/```\s*$/i',      '', $text);

                // First try full JSON decode
                $priceData = json_decode($text, true);

                // Fallback: extract individual objects via regex if truncated
                if (! is_array($priceData)) {
                    Log::warning('PricingService: Full JSON parse failed — attempting partial extraction.', [
                        'model'        => $model,
                        'text_preview' => mb_substr($text, 0, 200),
                    ]);
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
                    continue; // try next model
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

                return $items; // success — no need to try further models

            } catch (\Throwable $e) {
                Log::error('PricingService: Exception calling Gemini.', [
                    'model'   => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::error('PricingService: All Gemini models failed.', ['last_status' => $lastStatus]);

        return $items;
    }
}
