<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Third-pass quotation analysis.
 *
 * After PricingService produces unit prices, this service inspects the WHOLE set
 * of priced items and surfaces problems the per-item passes cannot see, plus a
 * market price range per item. Nothing here mutates prices — it only reports.
 *
 * It produces four kinds of finding, keyed by a stable code so the UI can style
 * and translate each one:
 *
 *   - DUPLICATION        : two or more rows describe the same item (near-identical
 *                          description + unit). A likely double-count in the BOQ.
 *   - PRICE_INCONSISTENCY: rows that describe the same item carry different unit
 *                          prices. One of them is probably wrong.
 *   - VAT_MISMATCH        : the 15% VAT the caller computed does not match a fresh
 *                          recomputation from the non-rejected line items.
 *
 * The market range (min / avg / max unit price per item) comes from a fresh
 * DeepSeek query, mirroring PricingService's batched-parallel call shape.
 */
class PriceAnalysisService
{
    /** Saudi standard VAT rate. Mirrors the rate hard-coded in the pricing blade. */
    public const VAT_RATE = 0.15;

    /** Absolute SAR tolerance when comparing the caller's VAT to a recomputation. */
    private const VAT_TOLERANCE = 0.5;

    /** Items per DeepSeek range call; kept small so calls run in parallel. */
    private const CHUNK_SIZE = 10;

    /**
     * Run every analysis over a set of already-priced items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  float|null  $reportedVat  The VAT figure the caller displays, to be checked.
     * @return array{
     *     findings: list<array{code:string, severity:string, message:string, rows:list<int>}>,
     *     ranges: array<int, array{min:float, avg:float, max:float}>,
     *     summary: array{subtotal:float, vat:float, total:float}
     * }
     */
    public function analyze(array $items, ?float $reportedVat = null): array
    {
        $findings = [];

        $findings = array_merge($findings, $this->detectDuplicates($items));
        $findings = array_merge($findings, $this->detectPriceInconsistencies($items));

        $summary  = $this->computeSummary($items);

        if ($reportedVat !== null) {
            $vatFinding = $this->checkVat($reportedVat, $summary['vat']);
            if ($vatFinding !== null) {
                $findings[] = $vatFinding;
            }
        }

        return [
            'findings' => array_values($findings),
            'ranges'   => $this->fetchMarketRanges($items),
            'summary'  => $summary,
        ];
    }

    // -------------------------------------------------------------------------
    // DUPLICATION
    // -------------------------------------------------------------------------

    /**
     * Group rows by a normalized (description + unit) key; any key with more than
     * one non-rejected row is a suspected duplicate.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{code:string, severity:string, message:string, rows:list<int>}>
     */
    private function detectDuplicates(array $items): array
    {
        $groups = [];
        foreach ($items as $index => $item) {
            if (($item['price_status'] ?? 'pending') === 'rejected') {
                continue;
            }
            $key = $this->normalizeKey($item);
            if ($key === '') {
                continue;
            }
            $groups[$key][] = $index;
        }

        $findings = [];
        foreach ($groups as $rows) {
            if (count($rows) < 2) {
                continue;
            }
            $findings[] = [
                'code'     => 'DUPLICATION',
                'severity' => 'warning',
                'message'  => trans_choice(
                    'app.analysis_duplication_msg',
                    count($rows),
                    ['count' => count($rows), 'desc' => $this->label($items[$rows[0]])]
                ),
                'rows'     => $rows,
            ];
        }

        return $findings;
    }

    // -------------------------------------------------------------------------
    // PRICE_INCONSISTENCY
    // -------------------------------------------------------------------------

    /**
     * Rows that describe the same item (same normalized key) but carry different
     * unit prices. Rows without a numeric price are ignored.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{code:string, severity:string, message:string, rows:list<int>}>
     */
    private function detectPriceInconsistencies(array $items): array
    {
        $groups = [];
        foreach ($items as $index => $item) {
            if (($item['price_status'] ?? 'pending') === 'rejected') {
                continue;
            }
            $price = $item['unit_price'] ?? null;
            if (! is_numeric($price)) {
                continue;
            }
            $key = $this->normalizeKey($item);
            if ($key === '') {
                continue;
            }
            $groups[$key][$index] = round((float) $price, 2);
        }

        $findings = [];
        foreach ($groups as $priced) {
            $distinct = array_unique(array_values($priced));
            if (count($distinct) < 2) {
                continue;
            }

            $rows = array_keys($priced);
            $findings[] = [
                'code'     => 'PRICE_INCONSISTENCY',
                'severity' => 'danger',
                'message'  => __('app.analysis_inconsistency_msg', [
                    'desc' => $this->label($items[$rows[0]]),
                    'min'  => number_format(min($distinct), 2),
                    'max'  => number_format(max($distinct), 2),
                ]),
                'rows'     => $rows,
            ];
        }

        return $findings;
    }

    // -------------------------------------------------------------------------
    // VAT review
    // -------------------------------------------------------------------------

    /**
     * Compare the caller's reported VAT against a fresh recomputation.
     * Returns a finding only when they diverge beyond tolerance.
     *
     * @return array{code:string, severity:string, message:string, rows:list<int>}|null
     */
    private function checkVat(float $reportedVat, float $expectedVat): ?array
    {
        if (abs($reportedVat - $expectedVat) <= self::VAT_TOLERANCE) {
            return null;
        }

        return [
            'code'     => 'VAT_MISMATCH',
            'severity' => 'danger',
            'message'  => __('app.analysis_vat_mismatch_msg', [
                'reported' => number_format($reportedVat, 2),
                'expected' => number_format($expectedVat, 2),
            ]),
            'rows'     => [],
        ];
    }

    /**
     * Recompute subtotal / VAT / total from non-rejected priced rows.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array{subtotal:float, vat:float, total:float}
     */
    private function computeSummary(array $items): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            if (($item['price_status'] ?? 'pending') === 'rejected') {
                continue;
            }
            $price = $item['unit_price'] ?? null;
            if (! is_numeric($price)) {
                continue;
            }
            $subtotal += (float) $price * (float) ($item['quantity'] ?? 0);
        }

        $vat = round($subtotal * self::VAT_RATE, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'vat'      => $vat,
            'total'    => round($subtotal + $vat, 2),
        ];
    }

    // -------------------------------------------------------------------------
    // Market range (min / avg / max) via DeepSeek
    // -------------------------------------------------------------------------

    /**
     * Ask DeepSeek for a realistic Saudi market unit-price range per priced item.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{min:float, avg:float, max:float}>  Keyed by item index.
     */
    private function fetchMarketRanges(array $items): array
    {
        $indices = [];
        foreach ($items as $index => $item) {
            if (($item['price_status'] ?? 'pending') === 'rejected') {
                continue;
            }
            if (is_numeric($item['unit_price'] ?? null) && (float) $item['unit_price'] > 0) {
                $indices[] = $index;
            }
        }

        if (empty($indices)) {
            return [];
        }

        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('PriceAnalysisService: DEEPSEEK_API_KEY not configured; skipping market ranges.');
            return [];
        }

        $ranges = [];
        foreach (array_chunk($indices, self::CHUNK_SIZE) as $chunk) {
            $ranges += $this->fetchRangeChunk($items, $chunk, $apiKey);
        }

        return $ranges;
    }

    /**
     * @param  list<int>  $indices
     * @return array<int, array{min:float, avg:float, max:float}>
     */
    private function fetchRangeChunk(array $items, array $indices, string $apiKey): array
    {
        $payload = [];
        foreach ($indices as $idx) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($items[$idx]['description'] ?? ''), 0, 80),
                'cat'  => mb_substr((string) ($items[$idx]['category'] ?? ''), 0, 30),
                'br'   => mb_substr((string) ($items[$idx]['brand'] ?? ''), 0, 30),
                'unit' => mb_substr((string) ($items[$idx]['unit'] ?? ''), 0, 15),
            ];
        }

        $prompt = 'You are a procurement pricing expert for the Saudi Arabia construction and MEP materials market. '
            . 'For each item below, give a realistic CURRENT unit-price RANGE in SAR based on typical 2024-2026 Saudi '
            . 'supplier rates: the lowest ("mn"), the average ("av") and the highest ("mx") you would expect to see. '
            . 'RULES: '
            . '(1) All three must be positive numbers with mn <= av <= mx. '
            . '(2) Return ONLY a valid compact JSON array, NO whitespace/newlines between elements, no markdown, no extra keys. '
            . '(3) Each element must be exactly: {"i":<index>,"mn":<number>,"av":<number>,"mx":<number>} '
            . 'Example: [{"i":0,"mn":1200,"av":1500,"mx":1900}] '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::timeout(90)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', [
                    'model'       => (string) config('services.deepseek.model', 'deepseek-chat'),
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.2,
                    'max_tokens'  => 8192,
                    'user'        => 'Qimta_Platform',
                ]);

            if (! $response->successful()) {
                Log::warning('PriceAnalysisService: DeepSeek range request failed.', ['status' => $response->status()]);
                return [];
            }

            return $this->parseRanges($response->json('choices.0.message.content') ?? '');
        } catch (\Throwable $e) {
            Log::error('PriceAnalysisService: Exception fetching ranges.', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * @return array<int, array{min:float, avg:float, max:float}>
     */
    private function parseRanges(string $text): array
    {
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/i', '', $text);

        $data = json_decode($text, true);

        if (! is_array($data)) {
            preg_match_all(
                '/\{\s*"i"\s*:\s*(\d+)\s*,\s*"mn"\s*:\s*([\d.]+)\s*,\s*"av"\s*:\s*([\d.]+)\s*,\s*"mx"\s*:\s*([\d.]+)\s*\}/',
                $text,
                $matches,
                PREG_SET_ORDER
            );
            $data = array_map(fn($m) => [
                'i'  => (int) $m[1],
                'mn' => (float) $m[2],
                'av' => (float) $m[3],
                'mx' => (float) $m[4],
            ], $matches);
        }

        if (! is_array($data)) {
            return [];
        }

        $ranges = [];
        foreach ($data as $entry) {
            $idx = $entry['i'] ?? null;
            if (! is_int($idx) && ! (is_string($idx) && ctype_digit($idx))) {
                continue;
            }
            $mn = is_numeric($entry['mn'] ?? null) ? (float) $entry['mn'] : null;
            $av = is_numeric($entry['av'] ?? null) ? (float) $entry['av'] : null;
            $mx = is_numeric($entry['mx'] ?? null) ? (float) $entry['mx'] : null;

            if ($mn === null || $av === null || $mx === null || $mn <= 0 || $mx <= 0) {
                continue;
            }

            // Guard against the model returning them out of order.
            $sorted = [$mn, $av, $mx];
            sort($sorted);

            $ranges[(int) $idx] = [
                'min' => round($sorted[0], 2),
                'avg' => round($sorted[1], 2),
                'max' => round($sorted[2], 2),
            ];
        }

        return $ranges;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * A normalized grouping key: lower-cased, whitespace-collapsed description + unit.
     * Empty when there is no description to compare on.
     */
    private function normalizeKey(array $item): string
    {
        $desc = mb_strtolower(trim((string) ($item['description'] ?? '')));
        $desc = preg_replace('/\s+/u', ' ', $desc);

        if ($desc === '') {
            return '';
        }

        $unit = mb_strtolower(trim((string) ($item['unit'] ?? '')));

        return $desc . '|' . $unit;
    }

    /** Short human label for a finding message. */
    private function label(array $item): string
    {
        $desc = trim((string) ($item['description'] ?? ''));
        return $desc !== '' ? mb_substr($desc, 0, 60) : '—';
    }
}
