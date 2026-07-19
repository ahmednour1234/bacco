<?php

namespace App\Services\Pricing;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Product Specification & Pricing Qualification Engine.
 *
 * Qualifies each BOQ line before any price is attached. For every item it:
 *   1. classifies it (supply product vs service/installation/unclear);
 *   2. normalises the unit and flags impossible ones;
 *   3. separates specs the client confirmed from safe industry defaults;
 *   4. asks only for BLOCKING specs, grouped into one question per item;
 *   5. returns a pricing-readiness status.
 *
 * Division of labour: unit rules and the spec rubric are deterministic (see
 * UnitNormalizer and ProductSpecCatalog) so they never drift between calls; the
 * AI is used only for the judgement that genuinely needs it — reading intent
 * from a free-text description and deciding what is truly missing.
 *
 * Never invents a brand, model, electrical rating, dimension, or approval.
 */
class ProductSpecEngine
{
    /** Items per AI call; small enough that calls can run in parallel. */
    private const CHUNK_SIZE = 8;

    /** The spec caps the grouped question at five sub-parts. */
    private const MAX_QUESTION_PARTS = 5;

    public const CLASSIFICATIONS = [
        'SUPPLY_PRODUCT', 'CUSTOM_MANUFACTURED_PRODUCT', 'SOFTWARE_OR_LICENSE',
        'SERVICE', 'INSTALLATION', 'CONSULTATION', 'UNSUPPORTED_ITEM', 'UNCLEAR_ITEM',
    ];

    /** Only these may appear in a supply quotation. */
    public const SUPPLYABLE = [
        'SUPPLY_PRODUCT', 'CUSTOM_MANUFACTURED_PRODUCT', 'SOFTWARE_OR_LICENSE',
    ];

    public const STATUSES = [
        'READY_TO_PRICE', 'READY_WITH_ASSUMPTIONS', 'BLOCKED_MISSING_SPECIFICATIONS',
        'INVALID_QUANTITY_OR_UNIT', 'COMPATIBILITY_REVIEW_REQUIRED', 'NOT_A_SUPPLY_PRODUCT',
    ];

    /**
     * Qualify a list of BOQ items.
     *
     * @param  array<int, array<string, mixed>>  $items   each with description, quantity, unit, category, brand
     * @param  array<string, mixed>              $project shared context (name, type, location, delivery date…)
     * @return array<int, array<string, mixed>>  one qualified record per input item, same order
     */
    public function qualify(array $items, array $project = []): array
    {
        if (empty($items)) {
            return [];
        }

        // Deterministic pass first — it also gives the AI a cleaner starting point.
        $prepared = [];
        foreach ($items as $i => $item) {
            $prepared[$i] = $this->prepare($item);
        }

        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('ProductSpecEngine: DEEPSEEK_API_KEY not configured; returning deterministic results only.');
            return array_values($prepared);
        }

        $chunks = array_chunk(array_keys($prepared), self::CHUNK_SIZE);

        $prepared = count($chunks) === 1
            ? $this->qualifyChunk($prepared, $chunks[0], $project)
            : $this->qualifyChunksParallel($prepared, $chunks, $project);

        // Project-level cross-item intelligence runs last: it needs every line resolved.
        return array_values(ProjectIntelligence::analyse($prepared, $project));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Deterministic pre-pass
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalise a single row's unit against its matched product family.
     *
     * Deterministic and free — no AI call — so it is safe to run over every row
     * at extraction time. Returns the corrected unit, or null to leave as-is.
     *
     * Without this, a printer arrives measured in "liter/day" and a UPS in
     * "carton meter", because the extractor copies whatever the sheet said.
     */
    public function normalizeUnitFor(string $description, string $rawUnit): ?string
    {
        $catalog = ProductSpecCatalog::match($description);
        if ($catalog === null) {
            return null;
        }

        $unit = UnitNormalizer::normalize($rawUnit, $catalog);

        $normalized = (string) ($unit['normalized'] ?? '');
        if ($normalized === '' || strcasecmp($normalized, $rawUnit) === 0) {
            return null;
        }

        return $normalized;
    }

    /**
     * Build the base record: catalog match, normalised unit, quantity sanity.
     * Everything here is decidable without the AI.
     */
    private function prepare(array $item): array
    {
        $description = trim((string) ($item['description'] ?? ''));
        $quantity    = (float) ($item['quantity'] ?? 0);
        $rawUnit     = (string) ($item['unit'] ?? '');

        $catalog = ProductSpecCatalog::match($description);
        $unit    = UnitNormalizer::normalize($rawUnit, $catalog);

        $quantityWarnings = [];
        if ($quantity <= 0) {
            $quantityWarnings[] = [
                'code'     => 'QUANTITY_NOT_POSITIVE',
                'severity' => 'error',
                'message'  => 'Quantity must be greater than zero.',
            ];
        } elseif ($quantity != floor($quantity) && in_array($unit['normalized'], ['PCS', 'SET', 'LICENSE'], true)) {
            $quantityWarnings[] = [
                'code'     => 'QUANTITY_FRACTIONAL',
                'severity' => 'warning',
                'message'  => "A fractional quantity ({$quantity}) is not valid for a countable unit ({$unit['normalized']}).",
            ];
        }

        return [
            'original_item_name'              => $description,
            'normalized_product_name'         => $description,
            'classification'                  => null,
            'category'                        => (string) ($item['category'] ?? ''),
            'catalog_key'                     => $catalog['key'] ?? null,
            'quantity'                        => $quantity,
            'original_unit'                   => $rawUnit,
            'normalized_unit'                 => $unit['normalized'],
            'supplyable'                      => true,
            'pricing_status'                  => null,
            'confirmed_specifications'        => [],
            'inferred_specifications'         => [],
            'missing_blocking_specifications' => [],
            'conditional_questions'           => [],
            'assumptions'                     => [],
            'quantity_warnings'               => $quantityWarnings,
            'unit_warnings'                   => $unit['warnings'],
            'compatibility_warnings'          => [],
            'recommended_final_description'   => $description,
            'pricing_basis'                   => '',
            'confidence_score'                => 0,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Prompt
    // ─────────────────────────────────────────────────────────────────────────

    private function buildPrompt(array $records, array $indices, array $project): string
    {
        $payload = [];
        foreach ($indices as $idx) {
            $r       = $records[$idx];
            $catalog = $r['catalog_key'] ? ProductSpecCatalog::all()[$r['catalog_key']] ?? null : null;

            $payload[] = array_filter([
                'i'        => $idx,
                'd'        => mb_substr($r['original_item_name'], 0, 200),
                'qty'      => $r['quantity'],
                'unit'     => $r['normalized_unit'],
                'cat'      => mb_substr($r['category'], 0, 40),
                // The rubric for this family, so the model audits against fixed
                // criteria instead of improvising a different list each call.
                'blocking' => $catalog['blocking']    ?? null,
                'optional' => $catalog['conditional'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');
        }

        $ctx = array_filter([
            'project_name'     => $project['name']     ?? null,
            'project_type'     => $project['type']     ?? null,
            'location'         => $project['location'] ?? null,
            'delivery_date'    => $project['delivery_date'] ?? null,
            'brand_policy'     => $project['brand_policy']  ?? null,
            'quotation_type'   => $project['quotation_type'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return 'You are a senior procurement engineer, quantity surveyor and technical pre-sales specialist '
            . 'for the Saudi Arabia (Riyadh) construction, MEP, IT, security, furniture and general-supply market. '
            . 'Qualify each BOQ line BEFORE pricing. Think like an experienced engineer, not a form-filler. '
            . ($ctx ? 'PROJECT CONTEXT: ' . json_encode($ctx, JSON_UNESCAPED_UNICODE) . '. ' : '')
            . 'FOR EACH ITEM DO ALL OF THE FOLLOWING: '
            . '(1) CLASSIFY as exactly one of: SUPPLY_PRODUCT, CUSTOM_MANUFACTURED_PRODUCT, SOFTWARE_OR_LICENSE, '
            . 'SERVICE, INSTALLATION, CONSULTATION, UNSUPPORTED_ITEM, UNCLEAR_ITEM. '
            . 'Installation, testing, commissioning, supervision, training, maintenance and consultancy are NOT supply products. '
            . 'If a line mixes supply AND installation, classify it as SUPPLY_PRODUCT and set "split":true so the caller separates the service. '
            . '(2) SEPARATE SPECIFICATIONS. Work through the item\'s "blocking" list one entry at a time. '
            . 'Each one lands in exactly ONE of three places: "cs" if the description or project context '
            . 'states it; "is" if you can apply a defensible market-standard default; "mb" if it genuinely '
            . 'cannot be defaulted (a brand, a model number, an electrical rating, a custom dimension). '
            . 'Together, "cs" + "is" + "mb" MUST cover every entry in the "blocking" list — none may be skipped. '
            . 'Prefer "is" over "mb": most specs (processor generation, screen size, OS, warranty, colour '
            . 'temperature, IP rating) have a standard answer for this market, so default them and move on. '
            . 'Every entry in "is" MUST also appear as a plain-language sentence in "as" (assumptions). '
            . '(3) MISSING BLOCKING SPECS. "mb" = ONLY specs without which a defensible price is impossible. '
            . 'Do NOT list something already answered by the description, the project context, the product '
            . 'category, or a safe standard default. An empty "mb" is the expected outcome for most items. '
            . '(4) ONE GROUPED QUESTION. If "mb" is non-empty, "q" = a SINGLE Arabic question asking for all of them together '
            . '(maximum ' . self::MAX_QUESTION_PARTS . ' sub-parts, comma-separated in one sentence). Never one question per field. '
            . 'If "mb" is empty, "q" = "". '
            . '(5) STATUS "st": READY_TO_PRICE (all blocking specs confirmed), '
            . 'READY_WITH_ASSUMPTIONS (priceable as a budgetary estimate using stated assumptions), '
            . 'BLOCKED_MISSING_SPECIFICATIONS (a blocking spec is genuinely unknowable), '
            . 'NOT_A_SUPPLY_PRODUCT (service/installation/unsupported). '
            . '(6) "fd" = the FULL procurement-grade product description. This is the single most '
            . 'important field: a buyer must be able to source and price the item from "fd" ALONE, '
            . 'without seeing the original BOQ line. Write out EVERY spec in the "blocking" list for '
            . 'this item — the ones the client stated, plus the ones you inferred — as one comma-separated '
            . 'specification string. Expand vague trade shorthand into the real specification. '
            . 'It must NEVER contain questions, placeholders, or the words "TBD"/"to be confirmed". '
            . 'EXAMPLE — input "Laptop for work - Core i7 / 16 GB / 512 GB SSD" must become: '
            . '"Business Laptop, Intel Core i7-1355U (13th Gen), 16 GB DDR4 RAM, 512 GB NVMe SSD, '
            . '14-inch FHD (1920x1080) Display, Intel Iris Xe Integrated Graphics, Windows 11 Pro, '
            . '3-Year Manufacturer Warranty". Note how the generation, GPU, screen size, OS and warranty '
            . 'are all present even though the client never wrote them — each one is listed in "is" and '
            . 'restated in "as" as an assumption. A description that merely repeats the client\'s words is WRONG. '
            . '(7) "pb" = one short phrase stating the pricing basis (e.g. "budgetary, standard configuration assumed"). '
            . '(8) "cf" = confidence 0-100 that this description can be priced accurately. '
            . 'HARD RULES: never invent a brand, model number, electrical rating, dimension for a custom-manufactured item, '
            . 'or a fire/safety approval. If such a value is genuinely required and unknown, it belongs in "mb", not in "is". '
            . 'OUTPUT: return ONLY a compact JSON array, no markdown, no commentary. Each element exactly: '
            . '{"i":<index>,"cl":"<classification>","split":<bool>,"cs":{},"is":{},"as":[],"mb":[],"q":"","st":"<status>","fd":"","pb":"","cf":0} '
            . 'ITEMS: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function buildRequestBody(array $records, array $indices, array $project): array
    {
        return [
            'model'       => (string) config('services.deepseek.model', 'deepseek-chat'),
            'messages'    => [['role' => 'user', 'content' => $this->buildPrompt($records, $indices, $project)]],
            'temperature' => 0.1,
            'max_tokens'  => 8192,
            'user'        => 'Qimta_Platform',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP
    // ─────────────────────────────────────────────────────────────────────────

    private function qualifyChunk(array $records, array $indices, array $project): array
    {
        $apiKey = (string) config('services.deepseek.key', '');

        try {
            $response = Http::timeout(120)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', $this->buildRequestBody($records, $indices, $project));

            if (! $response->successful()) {
                Log::warning('ProductSpecEngine: DeepSeek request failed.', ['status' => $response->status()]);
                return $this->fallback($records, $indices);
            }

            return $this->apply((string) ($response->json('choices.0.message.content') ?? ''), $records);
        } catch (\Throwable $e) {
            Log::error('ProductSpecEngine: exception calling DeepSeek.', ['message' => $e->getMessage()]);
            return $this->fallback($records, $indices);
        }
    }

    /** @param list<list<int>> $chunks */
    private function qualifyChunksParallel(array $records, array $chunks, array $project): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        $url    = 'https://api.deepseek.com/chat/completions';

        try {
            $responses = Http::pool(function (Pool $pool) use ($url, $apiKey, $records, $chunks, $project) {
                $reqs = [];
                foreach ($chunks as $ci => $indices) {
                    $reqs[] = $pool->as((string) $ci)
                        ->timeout(120)
                        ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                        ->post($url, $this->buildRequestBody($records, $indices, $project));
                }
                return $reqs;
            });

            foreach ($responses as $key => $response) {
                $ci = (int) $key;

                if (! ($response instanceof Response) || ! $response->successful()) {
                    Log::warning('ProductSpecEngine: parallel chunk failed, retrying serially.', ['chunk' => $ci]);
                    $records = $this->qualifyChunk($records, $chunks[$ci], $project);
                    continue;
                }

                $records = $this->apply((string) ($response->json('choices.0.message.content') ?? ''), $records);
            }
        } catch (\Throwable $e) {
            Log::error('ProductSpecEngine: Http::pool exception, falling back to serial.', ['message' => $e->getMessage()]);
            foreach ($chunks as $indices) {
                $records = $this->qualifyChunk($records, $indices, $project);
            }
        }

        return $records;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Response handling
    // ─────────────────────────────────────────────────────────────────────────

    private function apply(string $text, array $records): array
    {
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/', '', (string) $text);

        $data = json_decode((string) $text, true);

        if (! is_array($data) || $data === []) {
            Log::warning('ProductSpecEngine: unparseable AI response.', ['preview' => mb_substr($text, 0, 300)]);
            return $records;
        }

        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $i = $entry['i'] ?? null;
            if ($i === null || ! array_key_exists($i, $records)) {
                continue;
            }

            $records[$i] = $this->mergeEntry($records[$i], $entry);
        }

        return $records;
    }

    /** Merge one AI verdict into its record, enforcing the engine's invariants. */
    private function mergeEntry(array $record, array $entry): array
    {
        $classification = in_array($entry['cl'] ?? null, self::CLASSIFICATIONS, true)
            ? $entry['cl']
            : 'UNCLEAR_ITEM';

        $supplyable = in_array($classification, self::SUPPLYABLE, true);

        $missing = array_values(array_filter(
            array_map(fn ($m) => trim((string) $m), (array) ($entry['mb'] ?? [])),
            fn ($m) => $m !== ''
        ));

        $assumptions = array_values(array_filter(
            array_map(fn ($a) => trim((string) $a), (array) ($entry['as'] ?? [])),
            fn ($a) => $a !== ''
        ));

        $status = in_array($entry['st'] ?? null, self::STATUSES, true) ? $entry['st'] : null;

        // Invariants the model is not allowed to violate, in priority order.
        if (! $supplyable) {
            $status = 'NOT_A_SUPPLY_PRODUCT';
        } elseif ($this->hasError($record['quantity_warnings']) || $this->hasError($record['unit_warnings'])) {
            $status = 'INVALID_QUANTITY_OR_UNIT';
        } elseif ($missing !== []) {
            $status = 'BLOCKED_MISSING_SPECIFICATIONS';
        } elseif ($status === null) {
            $status = $assumptions === [] ? 'READY_TO_PRICE' : 'READY_WITH_ASSUMPTIONS';
        } elseif ($status === 'READY_TO_PRICE' && $assumptions !== []) {
            // Assumptions were applied, so this is budgetary by definition.
            $status = 'READY_WITH_ASSUMPTIONS';
        }

        $finalDescription = trim((string) ($entry['fd'] ?? ''));
        if ($finalDescription === '' || $this->looksLikeQuestion($finalDescription)) {
            $finalDescription = $record['original_item_name'];
        }

        // The whole point of the pass is a description a buyer can source from.
        // If the model handed back the client's own wording, the line was not
        // actually enriched — surface it rather than letting it pass as qualified.
        if ($this->isNotEnriched($finalDescription, $record['original_item_name'])) {
            Log::info('ProductSpecEngine: description not enriched by the model.', [
                'item'   => mb_substr($record['original_item_name'], 0, 120),
                'family' => $record['catalog_key'],
            ]);
        }

        $question = trim((string) ($entry['q'] ?? ''));

        return array_merge($record, [
            'classification'                  => $classification,
            'supplyable'                      => $supplyable,
            'split_supply_and_installation'   => (bool) ($entry['split'] ?? false),
            'confirmed_specifications'        => is_array($entry['cs'] ?? null) ? $entry['cs'] : [],
            'inferred_specifications'         => is_array($entry['is'] ?? null) ? $entry['is'] : [],
            'assumptions'                     => $assumptions,
            'missing_blocking_specifications' => $missing,
            'grouped_question'                => $missing === [] ? '' : $question,
            'pricing_status'                  => $status,
            'normalized_product_name'         => trim((string) ($entry['fd'] ?? '')) ?: $record['original_item_name'],
            'recommended_final_description'   => $finalDescription,
            'pricing_basis'                   => mb_substr(trim((string) ($entry['pb'] ?? '')), 0, 255),
            'confidence_score'                => max(0, min(100, (int) ($entry['cf'] ?? 0))),
        ]);
    }

    /**
     * When the AI is unreachable, fall back to the deterministic verdict rather
     * than guessing — an unqualified line is safer than a wrongly-qualified one.
     */
    private function fallback(array $records, array $indices): array
    {
        foreach ($indices as $i) {
            if (! array_key_exists($i, $records) || $records[$i]['pricing_status'] !== null) {
                continue;
            }

            $records[$i]['classification'] = 'UNCLEAR_ITEM';
            $records[$i]['pricing_status'] = $this->hasError($records[$i]['quantity_warnings'])
                || $this->hasError($records[$i]['unit_warnings'])
                    ? 'INVALID_QUANTITY_OR_UNIT'
                    : 'BLOCKED_MISSING_SPECIFICATIONS';
            $records[$i]['pricing_basis']  = 'Specification review unavailable; line not qualified.';
        }

        return $records;
    }

    /** @param list<array{severity?:string}> $warnings */
    private function hasError(array $warnings): bool
    {
        foreach ($warnings as $w) {
            if (($w['severity'] ?? '') === 'error') {
                return true;
            }
        }
        return false;
    }

    /**
     * True when the "enriched" description is really just the client's own line.
     *
     * A genuine qualification expands trade shorthand into a full specification,
     * so it is materially longer and carries more comma-separated parts. This is
     * a heuristic, used only to flag the case for review — never to block a line.
     */
    private function isNotEnriched(string $final, string $original): bool
    {
        $normalise = static fn (string $s) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($s)));

        if ($normalise($final) === $normalise($original)) {
            return true;
        }

        // Fewer than two spec separators means it is still a bare product name.
        return substr_count($final, ',') < 2 && mb_strlen($final) < mb_strlen($original) * 1.5;
    }

    /** Guards rule 7: a question must never leak into the final description. */
    private function looksLikeQuestion(string $text): bool
    {
        if (str_contains($text, '?') || str_contains($text, '؟')) {
            return true;
        }

        foreach (['tbd', 'to be confirmed', 'to be advised', 'please confirm', 'يرجى تحديد', 'يرجى تأكيد', 'غير محدد'] as $needle) {
            if (mb_stripos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
