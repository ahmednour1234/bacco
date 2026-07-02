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

    /**
     * Bare discipline / trade / system / category names that are headings, not
     * procurable products — even when they appear on their own BOQ row with a
     * price (e.g. cost-summary rows: "Electrical", "Plumbing", "Structural
     * Concrete", "Low current"). Matched only when the WHOLE description is one
     * of these (optionally followed by "works"/"system"), never as a substring,
     * so real products like "Electrical socket" are not affected.
     */
    private const DISCIPLINE_HEADING_PATTERN = '/^\s*(?:structural(?:\s+concrete)?|architectural|electrical|mechanical|plumbing|hvac|fire\s*fighting|firefighting|fire\s*protection|low\s*current|extra\s*low\s*voltage|elv|civil|finishing|finishes|elevators?|lifts?|escalators?|landscap(?:e|ing)|infrastructure|security|safety|drainage|sanitary|water\s*supply|works?|general)(?:\s+(?:works?|systems?|installations?))?\s*$/iu';

    /** Preliminary, mobilisation, provisional sum — at start of description */
    private const PRELIMINARY_PATTERN = '/^\s*(preliminar|mobiliz(?:ation)?|demobiliz(?:ation)?|provisional\s+sum|contingenc|p\.?\s*c\.?\s*sum|prime\s*cost\s*sum|scope\s+of\s+works?|bill\s+of\s+quantities|general\s+(?:conditions?|requirements?))\b/i';

    /**
     * Descriptions that are NOT standalone procurable items:
     *   - Floor/level labels: "GF Floor", "B1 Floor", "Level 3", "Ground Floor"
     *   - Pure dimensions/specs: "Dia 32 mm", "32 mm Dia", "Ø25", "100 mm"
     *   - Single generic words with no product context
     */
    private const FRAGMENT_PATTERN = '/^(?:(?:GF|BF|RF|GR|LG|UG|G(?:round)?|B\d+|[A-Z]?\d+(?:st|nd|rd|th)?|L\d+|Mez(?:zanine)?)\s*[-\x{2013}]?\s*(?:Floor|Level|Fl\.|Storey)|(?:Floor|Level|Storey)\s*[-\x{2013}]?\s*(?:\d+|[A-Z]{1,2}\d*)|(?:Dia(?:meter)?|OD|ID)\.?\s*[\d.]+\s*(?:mm|cm|m|inch(?:es)?|in)\.?|[\d.]+\s*(?:mm|cm|m)\s*(?:Dia(?:meter)?)?\.?|\d+(?:[.,]\d+)?\s+(?:SQM|sqm|sq\.?\s*m|M2|m2)\b|\d+|No\.?)\s*$/iu';

    /** Mid-description keywords that signal a pure non-supply line */
    private const PURE_LABOR_PATTERN = '/\b(supervision|testing\s+and\s+commissioning|commissioning\s+only|labour\s+only|labor\s+only|install(?:ation)?\s+only|site\s*clearance|excavat(?:ion|ing)?|backfill(?:ing)?|compaction|scaffolding|temporary\s+works?|as.?built\s+draw(?:ings?)?|shop\s+draw(?:ings?)?|method\s+statement|performance\s+bond|insurance\s+premium|civil\s+works?\s+only|transportation\s+only|haulage\s+only|wiring\s+(?:for|of|at|to|between)|cabling\s+(?:for|of|at|to|between)|points?\s+(?:wiring|cabling)|conduit\s+installation|duct(?:ing)?\s+(?:for|of)|pipe\s+routing|electrical\s+wiring\s+(?:floor|wall|ceiling|raised)\s+mounted|housekeeping|bodily\s+injury|(?:third\s+party|public|employer.?s?)\s+(?:all\s+risks?\s+)?liability|contractors?\s+all\s+risk|site\s+insurance|works?\s+insurance|\bCAR\s+(?:to\s+cover|insurance|policy)|night\s+shift|payments?\s+shall\s+be\s+arranged|rental\s+(?:payments?|fees?|charges?)|provide\s+protection|security\s+(?:as\s+per|guard|personnel|service)|preparation\s+of\s+(?:shop|as.?built|construction|working)\s+draw|\bpreliminar(?:y|ies)|\bmobilization|pre.?construction|project\s+management\s+(?:fee|cost)|overhead\s+(?:and\s+profit|cost)|contingenc(?:y|ies)|modification\s+of\s+existing|modif(?:y|ication)\s+existing|surface\s+prepar(?:ing|ation)?\s+for|terminat(?:e|ing|ion)\s+(?:and\s+)?(?:labelling|labeling|testing|commissioning)|cable\s+(?:terminat|label)|labelling\s+and\s+testing|integration\s+(?:works?|services?|only|fee|cost)|all\s+necessary\s+(?:accessories|materials|fittings|works?)|hangers?\s*,\s*supports?|تركيب\s+فقط|أعمال\s+(?:مدنية|هدم|التكسير|التكسير\s+و|الهدم|الإزالة|التشطيب)|تكسير\s+و(?:إزالة|تنظيف)|تصميم\s+و(?:إنشاء|انشاء|تركيب|تنفيذ|اختبار|انشاء\s+و))\b/iu';

    /**
     * Prose / description-fragment lines that are NOT procurable products.
     * These are sentences, measurement notes, or generic spec clauses that a PDF
     * table wrapping split off onto their own row (e.g. "The price includes all
     * materials, labor", "Measurement will be based on the geometric cubic meter",
     * "the supervising engineer's instructions", "soil-contacting elements").
     * Matched against the WHOLE (trimmed) description so real products are safe.
     */
    private const PROSE_FRAGMENT_PATTERN = '/^\s*(?:
        (?:the\s+)?(?:price|work|scope)\s+includes\b
        | measurement\s+will\s+be\b
        | (?:the\s+)?supervising\s+engineer
        | (?:in\s+)?(?:accordance|compliance)\b
        | (?:as\s+per|according\s+to|following)\s+(?:the\s+)?(?:supervising|engineer|project|design|drawing|specification|standard|instruction)
        | necessary\s+(?:steps?|actions?|processes?|works?)\b
        | soil.?contacting\s+elements?
        | everything\s+necessary\b
        | (?:all\s+)?(?:foundation|foundational)\s+works?\b
        | usage\s+requirements?\b
        | (?:the\s+)?(?:technical\s+)?specifications?\b
        | (?:the\s+)?(?:project\s+)?requirements?\.?\s*$
        | (?:the\s+)?(?:saudi|international)\s+(?:building\s+)?code
        | designated\s+materials?\b
        | approved\s+materials?\b
        | imperfections?\s*$
        | design\.?\s*$
        | water\s*$
        | control\s*$
        | operation\s*$
        | experimentation\s*$
        | includes?\s+(?:all|everything)\b
        | (?:by\s+)?(?:square|linear|cubic)\s+met(?:er|re)s?\s*:?\s*(?:supply|internal|the)?\s*$
        | (?:in\s+)?(?:square|linear|cubic)\s+met(?:er|re)s?\s*,?\s*supply(?:\s+and)?\s*$
        | (?:in\s+)?number\s*,?\s*supply(?:\s+and)?\s*$
        | this\s+work\s+includes?\b
        | supply\s*$
    )/ixu';

    /** Lines that START with an installation / labor verb — not a supply item */
    private const LABOR_VERB_START_PATTERN = '/^\s*(?:install(?:ing|ation)?|fix(?:ing)?|erect(?:ing|ion)?|lay(?:ing)?|paint(?:ing)?|plaster(?:ing)?|demolish(?:ing)?|remov(?:ing|al)?|dismantle|commission(?:ing)?|test(?:ing)?|supervise|supervision|excavat(?:ing|ion)?|transport(?:ing|ation)?|mobiliz(?:ation)?|civil\s+works?|maintenance\s+works?|modif(?:y|ication)|adjust(?:ment|ing)?|repair(?:ing)?|terminat(?:e|ing|ion)|connect(?:ing|ion)|wire\s+(?:the|all|from|to)|cleaning(?!\s+(?:material|agent|solution|cloth|equipment|product|suppl|kit|wipe|tool|brush|chemical))|housekeeping|cart\s+away|secure\b|concealed\s+mounting|preparation\s+of|repaint(?:ing)?|relocat(?:e|ing|ion)?|chipping|surface\s+prepar(?:ing|ation)?|(?:floor\s+)?level(?:l?ing)(?!\s+(?:screed|concrete|compound|agent))|protect(?:ing|ion\s+of)?\s+(?:existing|the)|night\s+shift|hangers?\b|all\s+necessary\s+|all\s+required\s+|تركيب|تمديد|تشغيل|صيانة|إزالة|هدم|ديمو|فك\s+و\s*إعادة\s+تركيب|فك\s+و|فكّ?\b|تغيير\s+(?:[اإأ]تجاه|موضع)|تجديد|تأهيل|معالجة\s+الجدران|دهان|تكسير|تصميم|إنشاء|انشاء|استبدال|تنفيذ|حفر|ردم|رصف|أعمال)\b/iu';

    /** Items that require engineering design or specialist approval before procurement */
    private const ENGINEERING_REQUIRED_PATTERN = '/\b(
        # Electrical systems
        panel\s*boards?|distribution\s*panel\s*boards?|distribution\s*boards?|switchboard|switchgear|transformer|mdb|smdb|ssb|msb|ups|genset|generator|ats\b|
        # HVAC systems
        chiller|air\s*handling\s*unit|\bahu\b|fahu|fan\s*coil(?:\s*unit)?|\bfcu\b|\bvrf\b|\bvrv\b|doas|crac|
        cooling\s*tower|heat\s*exchanger|pressure\s*reducing\s*valve|
        # Fire suppression
        fm\s*200|fm200|halon|suppression\s*system|fire\s*alarm\s*panel|fire\s*panel|deluge\s*valve|fm\s*200\s*panel|
        # BMS & Controls
        \bbms\b|scada|building\s*management\s*system|\bddc\b|\bplc\b|
        # Pumping equipment
        pump\s*set|booster\s*pump|fire\s*pump|sump\s*pump|submersible\s*pump|
        # Compound HVAC
        variable\s*refrigerant|variable\s*flow|
        # Arabic equivalents
        لوحة\s*كهربائية|لوحة\s*توزيع|وحدة\s*معالجة\s*هواء|مولد\s*كهربائي|مبرد\s*مياه|نظام\s*إطفاء
    )\b/ixu';

    /** "Supply and Install" variants — product must be extracted from the description */
    private const SUPPLY_AND_INSTALL_PATTERN = '/\b(?:supply\s+and\s+install(?:ation)?|supply\s*[\/&]\s*install(?:ation)?|furnish\s+and\s+install|provide\s+and\s+install|supply,?\s*install|توريد\s+و\s*تركيب|توريد\s+و\s*تثبيت|توريد\s+و\s*تمديد|توريد\s+و\s*توصيل)\b/iu';

    /** Prefix to strip when "Supply and Install" is detected */
    private const SUPPLY_AND_INSTALL_PREFIX = '/^\s*(?:supply\s+and\s+install(?:ation)?(?:\s+of\b)?|supply\s*[\/&]\s*install(?:ation)?(?:\s+of\b)?|furnish\s+and\s+install(?:\s+of\b)?|provide\s+and\s+install(?:\s+of\b)?|supply,?\s*install(?:\s+of\b)?|توريد\s+و\s*(?:تركيب|تثبيت|تمديد|توصيل)\s+(?:ال)?)\s*/iu';

    /** Trailing installation clauses to strip after product extraction */
    private const TRAILING_INSTALL_CLAUSE = '/,?\s*(?:includ(?:ing|e)?\s+)?(?:install(?:ation|ing)?|erect(?:ion|ing)?|fix(?:ing)?|connect(?:ion|ing)?|commission(?:ing)?|test(?:ing)?|as\s+per\s+spec[a-z]*)\b.*/i';

    // ── Description-cleaning patterns (applied in order) ─────────────────────

    /** @var list<non-empty-string> */
    private const CLEAN_PATTERNS = [
        // English generic phrases
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
        // Arabic generic trailing clauses — strip everything from these phrases onwards
        '/[،,]?\s*والبند\s+محمل\s+عليه.*/u',
        '/[،,]?\s*شامل\s+(?:جميع|كافة)\s+الأعمال.*/u',
        '/[،,]?\s*متضمن(?:ا)?\s+(?:جميع|كافة)\s+الأعمال.*/u',
        '/[،,]?\s*(?:طبقاً|وفقاً)\s+(?:للمواصفات|للرسومات|للمخطط|لتوجيهات).*/u',
        '/[،,]?\s*مع\s+أخذ\s+الموافقة.*/u',
        '/[،,]?\s*(?:ويكون|وتكون)\s+(?:العمل|الخامات).*/u',
        '/[،,]?\s*موضح\s+(?:في|ب|بـ?)\s*المخطط.*/u',
        '/[،,]?\s*وبأى\s+أماكن\s+أخرى.*/u',
        '/[،,]?\s*يحمل\s+على\s+البند.*/u',
        '/[،,]?\s*باللون\s+المطلوب.*/u',
        '/\s+(?:TAIF|BOQ|TAIF-BOQ)[-\s]*(?:[A-Z0-9]+)?\b.*$/u',
    ];

    // ── Compound-split helpers ────────────────────────────────────────────────

    /** Generic accessory terms that are NOT standalone products */
    private const NON_STANDALONE_PATTERN = '/^(?:fittings?|supports?|accessories|hangers?|clips?|brackets?|hardware|miscellaneous|misc|gaskets?|sealants?|bolts?\s*(?:and\s*nuts?)?|nuts?\s*(?:and\s*bolts?)?)$/i';

    /** Pure spec continuation — just a number + unit + optional adjective */
    private const SPEC_CONTINUATION_PATTERN = '/^[\d.]+\s*(?:mm|cm|m|m2|m3|kg|kw|kva|kvar|v|a|bar|psi|inch|ft|liter|l)?\s*(?:long|wide|high|deep|thick|dia|diameter|capacity|rating)?$/i';

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return true when a BOQ item requires engineering design or specialist
     * approval before it can be procured (e.g. panel boards, chillers, BMS).
     */
    public function requiresEngineering(string $description): bool
    {
        return (bool) preg_match(self::ENGINEERING_REQUIRED_PATTERN, $description);
    }

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

        // Strip leading BOQ item numbers like "2-", "1.", "A:", "البند 3:", "درج 1:"
        // Also strip comma-separated floor number lists: "3,4,5,6,7,8,9 Concealed mounting"
        $stripped = preg_replace('/^\s*(?:[\x{0600}-\x{06FF}]+\s+)?[\d\w]+[-:.)][\s\-]*/u', '', $desc);
        $stripped = preg_replace('/^\s*\d+(?:\s*,\s*\d+)+\s+/', '', (string) $stripped);
        $stripped = trim((string) $stripped);

        // 1. Total / summary aggregate lines
        if (preg_match(self::TOTAL_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Section total or summary line'];
        }

        // 2. Preliminary / provisional sum (matched at start of description)
        if (preg_match(self::PRELIMINARY_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Preliminary, provisional sum, or mobilization item'];
        }

        // 2a. Bare discipline / trade / system name used as a heading (not a product).
        //     Checked against both the raw and the number-stripped description.
        if (preg_match(self::DISCIPLINE_HEADING_PATTERN, $desc) ||
            ($stripped !== '' && preg_match(self::DISCIPLINE_HEADING_PATTERN, $stripped))) {
            return ['keep' => false, 'rejection_reason' => 'Discipline / trade / system heading, not a procurable product'];
        }

        // 2b. Fragment-only lines: floor labels, pure dimension specs, bare numbers
        if (preg_match(self::FRAGMENT_PATTERN, $desc) ||
            ($stripped !== '' && $stripped !== $desc && preg_match(self::FRAGMENT_PATTERN, $stripped))) {
            return ['keep' => false, 'rejection_reason' => 'Fragment or non-product line (floor label, dimension spec, or bare number)'];
        }

        // 2c. Continuation fragment: description contains unmatched closing parenthesis
        //     e.g. "Fluke test) - wall mounted" or "J45 termination, labelling, Fluke test) - ..."
        //     This signals the row is a tail-end fragment of a parenthetical from the previous row.
        if (substr_count($desc, ')') > substr_count($desc, '(')) {
            return ['keep' => false, 'rejection_reason' => 'Continuation fragment (unmatched closing parenthesis)'];
        }

        // 2e. Continuation fragment: starts with a lowercase word fragment.
        //     Catches 1-3 char fragments ("t", "k", "ier") AND 4+ char words ending in
        //     common suffix patterns ("kets" from sockets, "ers", "ons", etc.).
        //     BOQ product names start with a capital letter or a known abbreviation.
        if (preg_match('/^\s*([a-z]+)[\s,]/', $desc, $_m) &&
            !preg_match('/^\s*(?:the|and|an|of|on|at|in|to|by|for|up|uv|ip|ac|dc|ph|led|pvc|no)\b/i', $desc) &&
            (strlen($_m[1]) <= 3 || preg_match('/(?:ets|ers|ons|ings|ies|nds|rds|sts|nts|cts|ths)$/i', $_m[1]))) {
            return ['keep' => false, 'rejection_reason' => 'Continuation fragment (starts with lowercase word fragment)'];
        }

        // 2d. Standalone vague concept or adjective words that are not procurable products.
        //     e.g. "Integration", "Wiring", "Cabling", "Pendent", "Upright" alone on a row.
        if (preg_match('/^\s*(?:integration|commissioning|testing|inspection|survey|assessment|report|documentation|coordination|management|administration|overhead|profit|wiring|cabling|pendent|pendant|upright|recessed|exposed|flush|concealed)\s*$/i', $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Standalone non-product concept word'];
        }

        // 2f. Prose / description-fragment lines: sentences, measurement notes, and
        //     generic spec clauses that a wrapped PDF table split onto their own row.
        if (preg_match(self::PROSE_FRAGMENT_PATTERN, $desc) ||
            ($stripped !== '' && $stripped !== $desc && preg_match(self::PROSE_FRAGMENT_PATTERN, $stripped))) {
            return ['keep' => false, 'rejection_reason' => 'Prose or description fragment, not a procurable product'];
        }

        // 3. Mid-description keywords indicating a pure labor / non-supply line
        if (preg_match(self::PURE_LABOR_PATTERN, $desc)) {
            return ['keep' => false, 'rejection_reason' => 'Non-supply item (labor, site works, or project execution)'];
        }

        // 4. Description starts with an installation or labor verb (check both raw and number-stripped)
        if (preg_match(self::LABOR_VERB_START_PATTERN, $desc) ||
            ($stripped !== $desc && $stripped !== '' && preg_match(self::LABOR_VERB_START_PATTERN, $stripped))) {
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

        // Collapse consecutive PDF-wrapped rows (same qty+unit+category where the
        // later rows are continuation text) into a single item BEFORE filtering,
        // so a single BOQ line is never priced multiple times.
        $items = $this->mergeSplitRows($items);

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
     * Collapse consecutive rows that a PDF/table extractor split from ONE BOQ line.
     *
     * A later row is folded into the current item when it shares the SAME quantity,
     * unit and category AND its description is a continuation fragment (starts
     * lowercase / with a dash / a connective word, or is a prose/spec clause) rather
     * than a new product name. The merged text is appended to the first row and the
     * duplicate rows are dropped so the line is priced ONCE.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function mergeSplitRows(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $prev = $merged !== [] ? $merged[count($merged) - 1] : null;

            if ($prev !== null
                && $this->sameGrouping($prev, $item)
                && $this->isContinuationFragment((string) ($item['description'] ?? ''))
            ) {
                $prevDesc = trim((string) ($prev['description'] ?? ''));
                $addDesc  = trim((string) ($item['description'] ?? ''));
                $merged[count($merged) - 1]['description'] = trim($prevDesc . ' ' . $addDesc);
                continue;
            }

            $merged[] = $item;
        }

        return $merged;
    }

    /**
     * True when two rows belong to the same original BOQ line: identical quantity,
     * unit and category. Missing values compare loosely (both empty = same).
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function sameGrouping(array $a, array $b): bool
    {
        $qtyA = is_numeric($a['quantity'] ?? null) ? (float) $a['quantity'] : null;
        $qtyB = is_numeric($b['quantity'] ?? null) ? (float) $b['quantity'] : null;

        // Both must have a real, equal quantity — the strongest split signal.
        if ($qtyA === null || $qtyB === null || abs($qtyA - $qtyB) > 0.0001) {
            return false;
        }

        $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));

        return $norm($a['unit'] ?? '') === $norm($b['unit'] ?? '')
            && $norm($a['category'] ?? '') === $norm($b['category'] ?? '');
    }

    /**
     * True when a description reads as a continuation of the previous row rather
     * than a new product: it starts lowercase, with a dash/connective, or is a
     * prose/measurement/spec clause. Capitalised product-looking names return false.
     */
    private function isContinuationFragment(string $description): bool
    {
        $d = trim($description);

        if ($d === '') {
            return true;
        }

        // Starts with a connective / dash / lowercase letter → continuation.
        if (preg_match('/^\s*(?:[-\x{2013}\x{2014}]|the\b|and\b|or\b|in\b|to\b|by\b|for\b|with\b|[a-z\x{0600}-\x{06FF}])/u', $d)) {
            return true;
        }

        // Prose / spec / instruction clause → continuation even if capitalised.
        if (preg_match(self::PROSE_FRAGMENT_PATTERN, $d)) {
            return true;
        }

        return false;
    }

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
