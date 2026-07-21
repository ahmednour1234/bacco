<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Support\AiCache;

/**
 * Post-extraction BOQ validation gate.
 *
 * Runs immediately after the AI extracts BOQ items and BEFORE pricing is allowed.
 * It sends the whole item set to DeepSeek and asks it to audit each row across
 * several gates, returning a list of "questions" — each a problem it found, phrased
 * as a multiple-choice question the user must answer before pricing.
 *
 * Gates:
 *   quantity  — the quantity looks wrong/implausible for the item.
 *   unit      — the unit is wrong or missing for the item.
 *   specs     — minimum specifications are incomplete (grade, size, standard…).
 *   generic   — the description is too generic/vague to price.
 *   duplicate — this row appears to duplicate another row.
 *   scope     — the row does not seem to belong to the project scope.
 *   vat       — VAT configuration/rate looks off (checked once, not per row).
 *
 * The service NEVER blocks by itself: on any AI failure it returns an empty
 * question list so the caller can proceed with a warning. Applying the answers
 * back onto the items is the caller's job (see CreateQuotation::answerValidation).
 */
class BoqValidationService
{
    /** Rows per DeepSeek audit call. */
    private const CHUNK_SIZE = 25;

    /**
     * How long an audit's questions stay reusable.
     *
     * Long enough that re-uploading the same BOQ over a working week asks the
     * same things, which is the whole point — the questions are about the
     * document, and the document has not changed.
     */
    private const QUESTIONS_CACHE_DAYS = 30;

    /** The gate codes DeepSeek may emit, and that the caller knows how to apply. */
    public const GATES = ['quantity', 'unit', 'specs', 'generic', 'duplicate', 'scope', 'vat'];

    /**
     * Validate the extracted BOQ items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array{
     *     questions: list<array{row:int, gate:string, question:string, options:list<string>, suggested:?string}>,
     *     failed: bool
     * }
     */
    public function validate(array $items): array
    {
        if (empty($items)) {
            return ['questions' => [], 'failed' => false];
        }

        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('BoqValidationService: DEEPSEEK_API_KEY not configured; skipping validation.');
            return ['questions' => [], 'failed' => true];
        }

        // Reuse the questions for an identical set of rows.
        //
        // The audit is an AI call, so the same BOQ asked twice produced two
        // different question sets — a user re-uploading one file was asked
        // different things each time, with no way to tell which set was right.
        // Keyed on the rows themselves, so any real change to the BOQ produces
        // a different key and a fresh audit.
        $cacheKey = $this->questionsCacheKey($items);
        $cached   = AiCache::store()->get($cacheKey);

        if (is_array($cached)) {
            return ['questions' => $cached, 'failed' => false];
        }

        $questions = [];
        $anyFailed = false;

        foreach (array_chunk($items, self::CHUNK_SIZE, true) as $chunk) {
            $result = $this->auditChunk($chunk, $apiKey);
            if ($result === null) {
                $anyFailed = true;
                continue;
            }
            $questions = array_merge($questions, $result);
        }

        // Deterministic gate the AI cannot be trusted to compute: exact duplicate keys.
        // This backstops the AI's "duplicate" gate so obvious repeats are always caught.
        $questions = $this->mergeLocalDuplicateQuestions($items, $questions);
        $questions = array_values($questions);

        // Only a complete audit is worth keeping: caching a partial one would
        // pin the gaps in place for every later upload of the same file.
        if (! $anyFailed) {
            AiCache::store()->put($cacheKey, $questions, now()->addDays(self::QUESTIONS_CACHE_DAYS));
        }

        return ['questions' => $questions, 'failed' => $anyFailed];
    }

    /**
     * Cache key for a set of rows' questions.
     *
     * Built from the fields the audit actually reads, in row order, so an
     * unchanged BOQ hits and any real edit misses.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function questionsCacheKey(array $items): string
    {
        $signature = '';

        foreach ($items as $item) {
            $signature .= mb_strtolower(trim((string) ($item['description'] ?? '')))
                . '|' . trim((string) ($item['unit'] ?? ''))
                . '|' . (float) ($item['quantity'] ?? 0)
                . "\n";
        }

        return 'boq_questions_' . hash('sha256', $signature);
    }

    // -------------------------------------------------------------------------
    // DeepSeek audit
    // -------------------------------------------------------------------------

    /**
     * @param  array<int, array<string, mixed>>  $chunk  Preserves original indices.
     * @return list<array{row:int, gate:string, question:string, options:list<string>, suggested:?string}>|null
     *         Null on request failure so the caller can mark validation as failed.
     */
    private function auditChunk(array $chunk, string $apiKey): ?array
    {
        $payload = [];
        foreach ($chunk as $idx => $item) {
            $payload[] = [
                'i'    => $idx,
                'd'    => mb_substr((string) ($item['description'] ?? ''), 0, 120),
                'qty'  => (float) ($item['quantity'] ?? 0),
                'unit' => mb_substr((string) ($item['unit'] ?? ''), 0, 20),
                'cat'  => mb_substr((string) ($item['category'] ?? ''), 0, 30),
                'br'   => mb_substr((string) ($item['brand'] ?? ''), 0, 30),
            ];
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post('https://api.deepseek.com/chat/completions', [
                    'model'       => (string) config('services.deepseek.model', 'deepseek-chat'),
                    'messages'    => [['role' => 'user', 'content' => $this->buildPrompt($payload)]],
                    'temperature' => 0,
                    'max_tokens'  => 8192,
                    'user'        => 'Qimta_Platform',
                ]);

            if (! $response->successful()) {
                Log::warning('BoqValidationService: DeepSeek request failed.', ['status' => $response->status()]);
                return null;
            }

            return $this->parseQuestions($response->json('choices.0.message.content') ?? '');
        } catch (\Throwable $e) {
            Log::error('BoqValidationService: Exception calling DeepSeek.', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function buildPrompt(array $payload): string
    {
        return 'You are a senior BOQ (Bill of Quantities) auditor for the Saudi Arabia construction and MEP market. '
            . 'Audit each item below BEFORE it is priced. For each PROBLEM you find, emit one question the user must '
            . 'answer to resolve it. Do NOT emit anything for items that are fine. '
            . 'GATES to check per item: '
            . '"quantity" (quantity missing, zero, or implausible for the item); '
            . '"unit" (unit missing or wrong for the item — e.g. steel priced per M2 should be TON); '
            . '"specs" (minimum specs incomplete: grade/diameter/standard/strength missing so it cannot be priced accurately); '
            . '"generic" (description too vague/generic to price, e.g. "steel works"); '
            . '"duplicate" (this row clearly repeats another row — reference the other row index in the question text); '
            . '"scope" (row does not belong to this construction project scope). '
            . 'RULES: '
            . '(1) "row" = the item index "i". '
            . '(2) "gate" = one of quantity|unit|specs|generic|duplicate|scope. '
            . '(3) "q" = a short clear question in Arabic (max 20 words). '
            . '(4) "opts" = 2 to 4 answer options in Arabic. CRITICAL: options MUST be CONCRETE, SPECIFIC, '
            . 'READY-TO-APPLY values — never vague placeholders. '
            . 'For "specs"/"generic": propose ACTUAL standard values for THIS item, e.g. for PPR pipe give real '
            . 'diameters like "قطر 20 مم (DN20)", "قطر 25 مم (DN25)", "قطر 32 مم (DN32)"; for rebar give real grades '
            . 'like "حديد B500B قطر 12 مم"; for concrete give real strengths like "خرسانة C30/37". '
            . 'Do NOT return generic answers like "أقطار متعددة" or "قطر واحد" — always name real sizes/grades/brands. '
            . 'For "unit" the options MUST be concrete unit choices (e.g. "طن (TON)", "متر طولي (LM)"). '
            . 'For "duplicate"/"scope" include a keep vs remove choice. '
            . '(5) "sug" = the option string you recommend (must be one of opts), or "" if none. '
            . '(6) Return ONLY a valid compact JSON array, no markdown, no extra keys, no newlines between elements. '
            . '(7) Each element EXACTLY: {"row":<i>,"gate":"<gate>","q":"<question>","opts":["..",".."],"sug":"<option>"} '
            . 'If every item is fine, return []. '
            . 'Items: ' . json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return list<array{row:int, gate:string, question:string, options:list<string>, suggested:?string}>
     */
    private function parseQuestions(string $text): array
    {
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/i', '', $text);

        $data = json_decode($text, true);
        if (! is_array($data)) {
            Log::warning('BoqValidationService: could not parse questions.', ['preview' => mb_substr($text, 0, 300)]);
            return [];
        }

        $questions = [];
        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $row  = $entry['row'] ?? null;
            $gate = $entry['gate'] ?? null;
            if (! is_numeric($row) || ! in_array($gate, self::GATES, true)) {
                continue;
            }

            $opts = array_values(array_filter(
                is_array($entry['opts'] ?? null) ? $entry['opts'] : [],
                fn($o) => is_string($o) && trim($o) !== ''
            ));
            if (count($opts) < 2) {
                continue;
            }

            $question = trim((string) ($entry['q'] ?? ''));
            if ($question === '') {
                continue;
            }

            $suggested = is_string($entry['sug'] ?? null) && in_array($entry['sug'], $opts, true)
                ? $entry['sug']
                : null;

            $options = array_map(fn($o) => mb_substr((string) $o, 0, 120), array_slice($opts, 0, 4));

            // Which item field a free-text answer should be written to for this gate.
            // Duplicate is a keep/remove decision — no meaningful free-text target.
            $customField = match ((string) $gate) {
                'unit'    => 'unit',
                'generic' => 'description',
                'scope'   => 'description',
                'specs'   => 'brand',   // "specify the brand/grade" → brand field
                default   => null,
            };

            // Offer an explicit "other / specify" option whenever free text makes sense.
            if ($customField !== null) {
                $options[] = __('app.validation_other_option');
            }

            $questions[] = [
                'row'          => (int) $row,
                'gate'         => (string) $gate,
                'question'     => mb_substr($question, 0, 255),
                'options'      => array_values($options),
                'suggested'    => $suggested,
                'custom_field' => $customField,
                'custom_option'=> $customField !== null ? __('app.validation_other_option') : null,
            ];
        }

        return $questions;
    }

    // -------------------------------------------------------------------------
    // Deterministic duplicate backstop
    // -------------------------------------------------------------------------

    /**
     * Emit a duplicate question for any group of rows sharing an exact normalized
     * (description + unit) key, unless the AI already flagged one of those rows as
     * a duplicate.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    private function mergeLocalDuplicateQuestions(array $items, array $questions): array
    {
        $alreadyFlagged = [];
        foreach ($questions as $q) {
            if (($q['gate'] ?? '') === 'duplicate') {
                $alreadyFlagged[(int) $q['row']] = true;
            }
        }

        $groups = [];
        foreach ($items as $idx => $item) {
            $desc = mb_strtolower(trim((string) ($item['description'] ?? '')));
            $desc = preg_replace('/\s+/u', ' ', $desc);
            if ($desc === '') {
                continue;
            }
            $key = $desc . '|' . mb_strtolower(trim((string) ($item['unit'] ?? '')));
            $groups[$key][] = $idx;
        }

        foreach ($groups as $rows) {
            if (count($rows) < 2) {
                continue;
            }
            // Skip if the AI already raised a duplicate for any row in this group.
            $overlap = array_filter($rows, fn($r) => isset($alreadyFlagged[$r]));
            if (! empty($overlap)) {
                continue;
            }

            $primary = $rows[0];
            $questions[] = [
                'row'       => (int) $primary,
                'gate'      => 'duplicate',
                'question'  => __('app.validation_dup_question', [
                    'desc'  => mb_substr((string) ($items[$primary]['description'] ?? ''), 0, 60),
                    'count' => count($rows),
                ]),
                'options'   => [
                    __('app.validation_dup_keep'),
                    __('app.validation_dup_remove'),
                ],
                'suggested'    => __('app.validation_dup_remove'),
                'custom_field' => null,
                'custom_option'=> null,
                // Extra rows so the caller can remove the duplicates on "remove".
                'dup_rows'  => array_slice($rows, 1),
            ];
        }

        return $questions;
    }
}
