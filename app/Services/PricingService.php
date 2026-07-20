<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PricingService
{
    /**
     * Items per DeepSeek call. Multiple calls are made in PARALLEL so ALL unpriced items get a price faster.
     * Smaller = more parallel requests = faster overall. 10 items per request is optimal.
     */
    private const DEEPSEEK_CHUNK_SIZE = 10;

    /**
     * Concurrent AI requests per pool batch.
     *
     * Http::pool sends everything it is handed simultaneously, so an unbounded
     * pool on a large BOQ opens thousands of connections at once and gets
     * rate-limited. Batching keeps the parallelism useful without that.
     */
    private const MAX_PARALLEL_CHUNKS = 4;

    /**
     * How long an AI price stays reusable.
     *
     * Shorter than QuotationRequest::EXPIRY_DAYS (10) on purpose: a quotation
     * that has expired must re-price against genuinely current rates, so the
     * cache has to have lapsed by the time the quotation does.
     */
    private const PRICE_CACHE_DAYS = 7;

    /**
     * Optional progress reporter, called as ($chunksDone, $chunksTotal).
     *
     * @var (callable(int, int): void)|null
     */
    private $onProgress = null;

    /** @param  callable(int, int): void  $callback */
    public function onProgress(callable $callback): self
    {
        $this->onProgress = $callback;
        return $this;
    }

    private function reportProgress(int $done, int $total): void
    {
        if ($this->onProgress !== null) {
            ($this->onProgress)($done, $total);
        }
    }

    /**
     * Fetch unit prices for an array of quotation items.
     *
     * Strategy:
     *   1. Try the products table first (keyword match on name + optional category match).
     *   2. Collect all unmatched items and send them in ONE batched DeepSeek call.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>  Items enriched with unit_price, price_source, price_status
     */
    /**
     * Build the cache key for one row's price.
     *
     * Keyed on the product itself — description and unit — not the quotation, so
     * the same item priced from two different BOQs agrees with itself. The unit
     * is part of the key because a price per metre and a price per piece are
     * different numbers for the same product.
     */
    private function priceCacheKey(array $item): string
    {
        $description = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) ($item['description'] ?? '')) ?? ''));
        $unit        = mb_strtolower(trim((string) ($item['unit'] ?? '')));

        return 'ai_price_' . hash('sha256', $description . '|' . $unit);
    }

    /**
     * Remember the AI's prices so a re-run returns the same numbers.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $indices  rows this pass priced
     */
    private function cachePrices(array $items, array $indices): void
    {
        foreach ($indices as $index) {
            $item = $items[$index] ?? null;

            if (! $item || empty($item['unit_price']) || $item['unit_price'] <= 0) {
                continue;
            }

            Cache::put(
                $this->priceCacheKey($item),
                [
                    'unit_price'   => (float) $item['unit_price'],
                    'price_source' => $item['price_source'] ?? 'ai',
                ],
                now()->addDays(self::PRICE_CACHE_DAYS),
            );
        }
    }

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

                continue;
            }

            // Reuse a recent AI price for the same product.
            //
            // Without this, re-pricing the same BOQ produced different numbers
            // every time: the model is sampled at a non-zero temperature, so it
            // does not return an identical figure twice. A client seeing two
            // prices for one line on the same day has no reason to trust either.
            //
            // Held for PRICE_CACHE_DAYS, deliberately shorter than the
            // quotation's own expiry window, so a re-price after expiry does
            // fetch genuinely current rates.
            $cached = Cache::get($this->priceCacheKey($item));

            if (is_array($cached) && isset($cached['unit_price'])) {
                $items[$index]['unit_price']   = (float) $cached['unit_price'];
                $items[$index]['price_source'] = $cached['price_source'] ?? 'ai_cached';
                $items[$index]['price_status'] = 'pending';

                continue;
            }

            $items[$index]['unit_price']   = null;
            $items[$index]['price_source'] = null;
            $items[$index]['price_status'] = 'pending';
            $unmatched[]                   = $index;
        }

        if (! empty($unmatched)) {
            $chunks = array_chunk($unmatched, self::DEEPSEEK_CHUNK_SIZE);

            if (count($chunks) === 1) {
                // Single chunk — direct call (no pool overhead)
                $items = $this->enrichWithDeepSeek($items, $chunks[0]);
                $this->cachePrices($items, $chunks[0]);
            } else {
                // Multiple chunks. Http::pool fires every request it is given at
                // once, so a large BOQ would open thousands of concurrent calls
                // and be rate-limited into mass failure. Run the pool in batches
                // instead: still parallel, but bounded.
                $batches = array_chunk($chunks, self::MAX_PARALLEL_CHUNKS);
                $total   = count($chunks);
                $done    = 0;

                foreach ($batches as $batch) {
                    $items = $this->enrichChunksParallel($items, $batch);

                    $this->cachePrices($items, array_merge(...$batch));

                    $done += count($batch);
                    $this->reportProgress($done, $total);
                }
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

        // CRITICAL: only accept a price whose UNIT matches the item's unit.
        // A product priced per m² must never be used to price an item measured in m³ —
        // that would return a real number that is the wrong "unit price".
        $unit = $this->normalizeUnit((string) ($item['unit'] ?? ''));
        if ($unit === '') {
            // No unit on the item → we cannot guarantee the price is per the correct unit.
            // Fall through to the AI estimator, which is told the unit explicitly.
            return null;
        }

        $query->whereHas('unit', function ($q) use ($unit) {
            $q->whereRaw('LOWER(TRIM(name)) = ?', [$unit])
              ->orWhereRaw('LOWER(TRIM(symbol)) = ?', [$unit]);
        });

        $product = $query->select('unit_price')->first();

        return $product ? (float) $product->unit_price : null;
    }

    /**
     * Canonicalize a free-text unit string so equivalent spellings match.
     * e.g. "م³", "م3", "م^3", "M3", "متر مكعب", "cubic meter" → "م3".
     * Returns '' when the input is empty.
     */
    private function normalizeUnit(string $unit): string
    {
        $u = trim(mb_strtolower($unit));
        if ($u === '') {
            return '';
        }

        // Normalize superscripts and notation: م³ / م^3 / م 3 → م3
        $u = strtr($u, ['²' => '2', '³' => '3', '^' => '', '.' => '']);
        $u = preg_replace('/\s+/', '', $u);

        // Map common synonyms to a single canonical token.
        static $map = [
            'متر مكعب' => 'م3', 'مترمكعب' => 'م3', 'cubicmeter' => 'م3', 'cubicmetre' => 'م3', 'cbm' => 'م3', 'm3' => 'م3',
            'متر مربع' => 'م2', 'مترمربع' => 'م2', 'squaremeter' => 'م2', 'squaremetre' => 'م2', 'sqm' => 'م2', 'm2' => 'م2',
            'متر طولي' => 'مط', 'مترطولي' => 'مط', 'linearmeter' => 'مط', 'lm' => 'مط',
            'متر' => 'م', 'meter' => 'م', 'metre' => 'م',
            'عدد' => 'عدد', 'piece' => 'عدد', 'pcs' => 'عدد', 'pc' => 'عدد', 'no' => 'عدد', 'nos' => 'عدد', 'unit' => 'عدد',
            'كيلو' => 'كجم', 'كيلوغرام' => 'كجم', 'كيلوجرام' => 'كجم', 'kg' => 'كجم', 'kilogram' => 'كجم',
            'طن' => 'طن', 'ton' => 'طن', 'tonne' => 'طن',
            'لتر' => 'لتر', 'liter' => 'لتر', 'litre' => 'لتر', 'l' => 'لتر',
            'كيس' => 'كيس', 'bag' => 'كيس', 'bags' => 'كيس',
        ];

        return $map[$u] ?? $u;
    }

    // -------------------------------------------------------------------------
    // DeepSeek fallback — helpers
    // -------------------------------------------------------------------------

    /**
     * Build the compact payload sent to DeepSeek for a set of item indices.
     */
    private function buildDeepSeekPayload(array $items, array $indices): array
    {
        $payload = [];
        foreach ($indices as $idx) {
            $payload[] = [
                'i'    => $idx,
                // 400 chars: BOQ lines routinely carry the specs that decide the
                // price (size, grade, rating), and the old 80-char cut dropped them.
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 400),
                'cat'  => mb_substr((string) ($items[$idx]['category']    ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand']       ?? ''), 0, 30),
                'unit' => mb_substr((string) ($items[$idx]['unit']        ?? ''), 0, 15),
            ];
        }
        return $payload;
    }

    /**
     * Build the DeepSeek prompt string for a given payload array.
     */
    private function buildPricingPrompt(array $payload): string
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
     * Parse a DeepSeek text response and apply prices to $items.
     */
    private function applyDeepSeekText(string $text, array $items, string $model): array
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
            Log::warning('PricingService: Could not extract any prices from DeepSeek response.', [
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
                $items[$idx]['price_source'] = 'deepseek';
            }
        }

        return $items;
    }

    /**
     * Send multiple chunks to DeepSeek in PARALLEL using Http::pool().
     * Falls back to sequential per-chunk calls if pool fails.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<list<int>>  $chunks
     * @return array<int, array<string, mixed>>
     */
    private function enrichChunksParallel(array $items, array $chunks): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        $model  = (string) config('services.deepseek.model', 'deepseek-chat');

        if (empty($apiKey)) {
            Log::warning('PricingService: DEEPSEEK_API_KEY not configured; skipping AI pricing.');
            return $items;
        }

        $url = 'https://api.deepseek.com/chat/completions';

        // Build request bodies once
        $requestBodies = [];
        foreach ($chunks as $ci => $chunkIndices) {
            $payload             = $this->buildDeepSeekPayload($items, $chunkIndices);
            $requestBodies[$ci]  = [
                'model'       => $model,
                'messages'    => [
                    ['role' => 'user', 'content' => $this->buildPricingPrompt($payload)],
                ],
                'temperature' => 0,
                'max_tokens'  => 8192,
                'user'        => 'Qimta_Platform',
            ];
        }

        $failedChunks = array_keys($chunks);

        // Single attempt with the configured model
        if (! empty($failedChunks)) {
            $pendingIndices = $failedChunks;
            $failedChunks   = [];

            try {
                $responses = Http::pool(function (Pool $pool) use ($url, $apiKey, $requestBodies, $pendingIndices) {
                    $reqs = [];
                    foreach ($pendingIndices as $ci) {
                        $reqs[] = $pool->as((string) $ci)
                            ->timeout(90)
                            ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                            ->post($url, $requestBodies[$ci]);
                    }
                    return $reqs;
                });

                foreach ($responses as $key => $response) {
                    $ci = (int) $key;

                    if (! $response->successful()) {
                        Log::warning('PricingService: Parallel chunk failed, will retry sequentially.', [
                            'model'  => $model,
                            'chunk'  => $ci,
                            'status' => $response->status(),
                        ]);
                        $failedChunks[] = $ci;
                        continue;
                    }

                    $text  = $response->json('choices.0.message.content') ?? '';
                    $items = $this->applyDeepSeekText($text, $items, $model);

                    // Released as soon as it is parsed. max_tokens is 8192, and
                    // Guzzle buffers each response in full — holding a whole
                    // pool's worth is what exhausted the memory limit.
                    unset($responses[$key], $response, $text);
                }

                unset($responses);
            } catch (\Throwable $e) {
                Log::error('PricingService: Http::pool exception, falling back to serial.', [
                    'model'   => $model,
                    'message' => $e->getMessage(),
                ]);
                foreach ($pendingIndices as $ci) {
                    $items = $this->enrichWithDeepSeek($items, $chunks[$ci]);
                }
                return $items;
            }
        }

        if (! empty($failedChunks)) {
            foreach ($failedChunks as $ci) {
                $items = $this->enrichWithDeepSeek($items, $chunks[$ci]);
            }
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // DeepSeek fallback (single batched request)
    // -------------------------------------------------------------------------

    /**
     * Send all unmatched items to DeepSeek in ONE request to minimise token consumption.
     * Only the fields DeepSeek actually needs are sent (description, category, brand, unit).
     * Output tokens are capped via max_tokens to protect against runaway usage.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $unmatchedIndices
     * @return array<int, array<string, mixed>>
     */
    private function enrichWithDeepSeek(array $items, array $unmatchedIndices): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        $model  = (string) config('services.deepseek.model', 'deepseek-chat');

        if (empty($apiKey)) {
            Log::warning('PricingService: DEEPSEEK_API_KEY not configured; skipping AI pricing.');
            return $items;
        }

        $payload    = $this->buildDeepSeekPayload($items, $unmatchedIndices);
        $prompt     = $this->buildPricingPrompt($payload);
        $lastStatus = null;

        try {
            $response = Http::timeout(90)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', [
                    'model'       => $model,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0,
                    'max_tokens'  => 8192,
                    'user'        => 'Qimta_Platform',
                ]);

            if (! $response->successful()) {
                $lastStatus = $response->status();
                Log::warning('PricingService: DeepSeek request failed.', ['model' => $model, 'status' => $lastStatus]);
            } else {
                $text  = $response->json('choices.0.message.content') ?? '';
                $items = $this->applyDeepSeekText($text, $items, $model);
                return $items;
            }
        } catch (\Throwable $e) {
            Log::error('PricingService: Exception calling DeepSeek.', ['model' => $model, 'message' => $e->getMessage()]);
        }

        Log::error('PricingService: DeepSeek request failed.', ['last_status' => $lastStatus]);

        return $items;
    }
}
