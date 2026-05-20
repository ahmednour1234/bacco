<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * BOQ Cleaning & Supply Extraction Layer.
 *
 * Three responsibilities:
 *   1. filterItem()       — decide whether a description is a procurable supply item
 *   2. cleanDescription() — strip generic non-informative phrases from a description
 *   3. splitCompoundItem() — expand "Product A with Product B" into separate items
 *   4. process()          — apply steps 2 & 3 to a batch of already-filtered items
 */
class BoqCleaningService
{
    // ── Rejection patterns ────────────────────────────────────────────────────

    /** Section totals, summaries, aggregate lines */
    private const TOTAL_PATTERN = '/\b(sub.?total|grand\s*total|section\s*total|total\s*price|total\s*amount|mechanical\s*total|electrical\s*total|civil\s*total|plumbing\s*total|hvac\s*total|fire\s*total|summary|subtotal)\b/i';

    /** Preliminary, mobilisation, provisional sum — at start of description */
    private const PRELIMINARY_PATTERN = '/^\s*(preliminar|mobiliz(?:ation)?|demobiliz(?:ation)?|provisional\s+sum|contingenc|p\.?\s*c\.?\s*sum|prime\s*cost\s*sum)\b/i';

    /** Mid-description keywords that signal a pure non-supply line */
    private const PURE_LABOR_PATTERN = '/\b(supervision|testing\s+and\s+commissioning|commissioning\s+only|labour\s+only|labor\s+only|install(?:ation)?\s+only|site\s*clearance|excavat(?:ion|ing)?|backfill(?:ing)?|compaction|scaffolding|temporary\s+works?|as.?built\s+draw(?:ing)?|shop\s+draw(?:ing)?|method\s+statement|performance\s+bond|insurance\s+premium|civil\s+works?\s+only|transportation\s+only|haulage\s+only)\b/i';

    /** Lines that START with an installation / labor verb — not a supply item */
    private const LABOR_VERB_START_PATTERN = '/^\s*(?:install(?:ing|ation)?|fix(?:ing)?|erect(?:ing|ion)?|lay(?:ing)?|paint(?:ing)?|plaster(?:ing)?|demolish(?:ing)?|remov(?:ing|al)?|dismantle|commission(?:ing)?|test(?:ing)?|supervise|supervision|excavat(?:ing|ion)?|transport(?:ing|ation)?|mobiliz(?:ation)?|civil\s+works?|maintenance\s+works?)\b/i';

    /** "Supply and Install" variants — product must be extracted from the description */
    private const SUPPLY_AND_INSTALL_PATTERN = '/\b(?:supply\s+and\s+install(?:ation)?|supply\s*[\/&]\s*install(?:ation)?|furnish\s+and\s+install|provide\s+and\s+install|supply,?\s*install)\b/i';

    /** Prefix to strip when "Supply and Install" is detected */
    private const SUPPLY_AND_INSTALL_PREFIX = '/^\s*(?:supply\s+and\s+install(?:ation)?\s+of?|supply\s*[\/&]\s*install(?:ation)?\s+of?|furnish\s+and\s+install\s+of?|provide\s+and\s+install\s+of?|supply,?\s*install\s+of?)\s*/i';

    /** Trailing installation clauses to strip after product extraction */
    private const TRAILING_INSTALL_CLAUSE = '/,?\s*(?:includ(?:ing|e)?\s+)?(?:install(?:ation|ing)?|erect(?:ion|ing)?|fix(?:ing)?|connect(?:ion|ing)?|commission(?:ing)?|test(?:ing)?|as\s+per\s+spec[a-z]*)\b.*/i';

    // ── Description-cleaning patterns (applied in order) ─────────────────────

    /** @var list<non-empty-string> */
    private const CLEAN_PATTERNS = [
        '/,?\s*complete\s+with\s+(?:all\s+)?(?:necessary\s+)?(?:accessories|fittings)[^,;)]*?(?=[,;)]|$)/i',
        '/,?\s*including\s+(?:all\s+)?fittings(?:[,\s]+(?:and\s+)?supports?)?(?:[,\s]+(?:and\s+)?accessories)?/i',
        '/,?\s*with\s+fittings(?:[,\s]+(?:and\s+)?supports?)?(?:[,\s]+(?:and\s+)?accessories)?/i',
        '/,?\s*(?:and|with|or)\s+accessories/i',
        '/,?\s*(?:and|with|or)\s+supports?/i',
        '/,?\s*including\s+all\s+(?:necessary\s+)?(?:works?|materials?|accessories|fittings)[^,;)]*?(?=[,;)]|$)/i',
        '/,?\s*all\s+necessary\s+works?/i',
        '/,?\s*as\s+per\s+(?:the\s+)?(?:approved\s+)?(?:drawings?|specifications?|specs?|manufacturer[\'s]*\s+(?:data\s*sheet|specs?|recommendations?))[^,;)]*?(?=[,;)]|$)/i',
        '/,?\s*as\s+(?:shown\s+)?(?:on\s+)?(?:the\s+)?drawings?/i',
        '/,?\s*as\s+per\s+(?:project\s+)?specifications?/i',
        '/,?\s*as\s+indicated(?:\s+on\s+(?:the\s+)?drawings?)?/i',
        '/,?\s*as\s+specified/i',
        '/\s*\([^)]*?(?:as\s+per|as\s+specified|as\s+indicated|per\s+draw(?:ing)?|refer\s+to)[^)]*?\)/i',
    ];

    // ── Compound-split helpers ────────────────────────────────────────────────

    /** Generic accessory terms that are NOT standalone products */
    private const NON_STANDALONE_PATTERN = '/^(?:fittings?|supports?|accessories|hangers?|clips?|brackets?|hardware|miscellaneous|misc|gaskets?|sealants?|bolts?\s*(?:and\s*nuts?)?|nuts?\s*(?:and\s*bolts?)?)$/i';

    /** Pure spec continuation — just a number + unit + optional adjective */
    private const SPEC_CONTINUATION_PATTERN = '/^[\d.]+\s*(?:mm|cm|m|m2|m3|kg|kw|kva|kvar|v|a|bar|psi|inch|ft|liter|l)?\s*(?:long|wide|high|deep|thick|dia|diameter|capacity|rating)?$/i';

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Determine whether a BOQ line description represents a procurable supply item.
     *
     * @return array{keep: true, description: string, extraction_type: string}
     *            |array{keep: false, rejection_reason: string}
     */
    public function filterItem(string $description): array
    {
        $desc = trim($description);

        if ($desc === '') {
            return ['keep' => false, 'rejection_reason' => 'Empty description'];
        }

        // 1. Total / summary aggregate lines
        if (preg_match(self::TOTAL_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Section total or summary line'];
        }

        // 2. Preliminary / provisional sum (matched at start of description)
        if (preg_match(self::PRELIMINARY_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Preliminary, provisional sum, or mobilization item'];
        }

        // 3. Mid-description keywords indicating a pure labor / non-supply line
        if (preg_match(self::PURE_LABOR_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Non-supply item (labor, site works, or project execution)'];
        }

        // 4. Description starts with an installation or labor verb
        if (preg_match(self::LABOR_VERB_START_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Starts with installation or labor verb'];
        }

        // 5. "Supply and Install" — extract the supply product from the description
        if (preg_match(self::SUPPLY_AND_INSTALL_PATTERN, $desc)) {
            $cleaned = preg_replace(self::SUPPLY_AND_INSTALL_PREFIX, '', $desc) ?? $desc;
            $cleaned = preg_replace(self::TRAILING_INSTALL_CLAUSE, '', $cleaned) ?? $cleaned;
            $cleaned = trim((string) $cleaned, ' ,;');

            return [
                'keep'            => true,
                'description'     => $cleaned !== '' ? $cleaned : $desc,
                'extraction_type' => 'extracted_from_supply_and_install',
            ];
        }

        // 6. Accept as a supply item
        return [
            'keep'            => true,
            'description'     => $desc,
            'extraction_type' => 'supply_only',
        ];
    }

    /**
     * Strip generic non-informative phrases from a description while preserving
     * technical specifications, dimensions, and product identifiers.
     */
    public function cleanDescription(string $description): string
    {
        $desc = $description;

        foreach (self::CLEAN_PATTERNS as $pattern) {
            $desc = preg_replace($pattern, '', $desc) ?? $desc;
        }

        $desc = trim((string) $desc, ' ,;()');
        $desc = preg_replace('/\s{2,}/', ' ', $desc) ?? $desc;

        return trim((string) $desc);
    }

    /**
     * Attempt to split a compound item description such as
     * "Wash Basin with Mixer and Bottle Trap" into individual supply items.
     *
     * Each resulting sub-item inherits the parent's quantity, unit, and unit_price.
     * Generic accessory terms (fittings, supports…) and pure spec continuations
     * (e.g. "3m long") are discarded.
     *
     * If splitting yields only one valid product the original item is returned unchanged.
     *
     * @param  array<string, mixed>  $item
     * @return list<array<string, mixed>>
     */
    public function splitCompoundItem(array $item): array
    {
        $desc = trim((string) ($item['description'] ?? ''));

        if (! preg_match('/\s+(?:with|and|&)\s+/i', $desc)) {
            return [$item];
        }

        // Split on " with " first (high-confidence boundary), then " and " / " & "
        $parts        = [];
        $withSegments = preg_split('/\s+with\s+/i', $desc, -1, PREG_SPLIT_NO_EMPTY) ?: [$desc];

        foreach ($withSegments as $segment) {
            $andParts = preg_split('/\s+(?:and|&)\s+/i', $segment, -1, PREG_SPLIT_NO_EMPTY) ?: [$segment];
            foreach ($andParts as $part) {
                $cleaned = trim((string) $part, ' ,;');
                if ($cleaned !== '') {
                    $parts[] = $cleaned;
                }
            }
        }

        $valid = array_values(array_filter($parts, fn (string $p) => $this->isValidStandaloneProduct($p)));

        if (count($valid) <= 1) {
            return [$item];
        }

        $originalRaw = is_array($item['raw_data'] ?? null) ? $item['raw_data'] : [];
        $results     = [];

        foreach ($valid as $product) {
            $newItem                = $item;
            $newItem['description'] = $product;
            $newItem['raw_data']    = array_merge($originalRaw, ['split_from' => $desc]);
            $results[]              = $newItem;
        }

        return $results;
    }

    /**
     * Apply description cleaning and compound splitting to a batch of
     * already-filtered items.
     *
     * Items whose description becomes empty after cleaning are moved to the
     * rejected list.
     *
     * @param  list<array<string, mixed>>  $items
     * @return array{accepted: list<array<string, mixed>>, rejected: list<array<string, mixed>>}
     */
    public function process(array $items): array
    {
        $accepted = [];
        $rejected = [];

        foreach ($items as $item) {
            $cleaned = $this->cleanDescription((string) ($item['description'] ?? ''));

            if ($cleaned === '') {
                $item['status']   = 'rejected';
                $rawData          = is_array($item['raw_data'] ?? null) ? $item['raw_data'] : [];
                $item['raw_data'] = array_merge($rawData, [
                    'rejection_reason' => 'Description became empty after cleaning generic phrases',
                ]);
                $rejected[] = $item;
                continue;
            }

            $item['description'] = $cleaned;

            if (is_array($item['raw_data'] ?? null)) {
                $item['raw_data']['cleaned_description'] = $cleaned;
            }

            $subItems = $this->splitCompoundItem($item);
            $accepted = array_merge($accepted, $subItems);
        }

        return ['accepted' => $accepted, 'rejected' => $rejected];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Return true if the string looks like a valid standalone product name,
     * false for generic accessory terms or pure spec continuations.
     */
    private function isValidStandaloneProduct(string $part): bool
    {
        $part = trim($part);

        if (strlen($part) < 3) {
            return false;
        }

        if (preg_match(self::NON_STANDALONE_PATTERN, $part)) {
            return false;
        }

        if (preg_match(self::SPEC_CONTINUATION_PATTERN, $part)) {
            return false;
        }

        return true;
    }
}
