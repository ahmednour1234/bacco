<?php

namespace App\Services\Seo;

use App\Models\SeoMeta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Generates bilingual SEO copy for a page by asking Claude.
 *
 * The result is never written anywhere — it is handed back to the admin form
 * so a human reviews and saves it. Search engines punish bad copy, and the
 * model has no way to know what the business is currently pushing, so an
 * unattended write-through would be the wrong trade.
 *
 * Structured outputs are used rather than "reply with JSON" prompting: the
 * schema is enforced server-side, so a malformed response is not a case the
 * caller has to handle.
 */
class SeoCopyGenerator
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /** Pinned: the response shape below is what this version of the API returns. */
    private const API_VERSION = '2023-06-01';

    /** Google truncates titles past ~60 chars and descriptions past ~160. */
    private const TITLE_MAX = 60;
    private const DESC_MAX  = 160;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {
    }

    public static function make(): self
    {
        $key = (string) config('services.anthropic.key', '');

        if ($key === '') {
            throw new RuntimeException(
                'ANTHROPIC_API_KEY is not set. Add it to .env to use AI-assisted SEO.'
            );
        }

        return new self($key, (string) config('services.anthropic.model', 'claude-opus-5-5'));
    }

    public static function isConfigured(): bool
    {
        return (string) config('services.anthropic.key', '') !== '';
    }

    /**
     * @param  array{page_url?: string, page_content?: string, focus?: string}  $context
     * @return array<string, string>
     */
    public function generate(SeoMeta $seo, array $context = []): array
    {
        $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => self::API_VERSION,
                'content-type'      => 'application/json',
            ])
            ->timeout(120)
            ->retry(2, 1000, throw: false)
            ->post(self::ENDPOINT, [
                'model'      => $this->model,
                'max_tokens' => 4096,
                'system'     => $this->systemPrompt(),
                'messages'   => [[
                    'role'    => 'user',
                    'content' => $this->userPrompt($seo, $context),
                ]],
                // Server-enforced shape — no need to defend against bad JSON below.
                'output_config' => [
                    'format' => [
                        'type'   => 'json_schema',
                        'schema' => $this->schema(),
                    ],
                ],
            ]);

        if ($response->failed()) {
            $detail = (string) ($response->json('error.message') ?? $response->body());

            Log::error('SeoCopyGenerator: Anthropic request failed.', [
                'status'     => $response->status(),
                'route_name' => $seo->route_name,
                'detail'     => mb_substr($detail, 0, 500),
            ]);

            throw new RuntimeException($this->friendlyError($response->status(), $detail));
        }

        return $this->extract($response->json(), $seo);
    }

    /**
     * Pull the JSON payload out of the first text block.
     *
     * Thinking blocks can precede the text block, so the content array is
     * scanned rather than indexed.
     *
     * @param  array<string, mixed>|null  $body
     * @return array<string, string>
     */
    private function extract(?array $body, SeoMeta $seo): array
    {
        // A safety decline returns HTTP 200, so stop_reason is checked before
        // the content is read.
        if (($body['stop_reason'] ?? null) === 'refusal') {
            throw new RuntimeException(
                'Claude declined to generate copy for this page. Try rewording the focus keywords.'
            );
        }

        $json = null;

        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'text') {
                $json = json_decode((string) ($block['text'] ?? ''), true);
                break;
            }
        }

        if (! is_array($json)) {
            Log::error('SeoCopyGenerator: no JSON in response.', [
                'route_name' => $seo->route_name,
                'body'       => mb_substr(json_encode($body) ?: '', 0, 500),
            ]);

            throw new RuntimeException('Claude returned an unreadable response. Please try again.');
        }

        $out = [];

        foreach (['title_en', 'title_ar', 'meta_desc_en', 'meta_desc_ar', 'keywords_en', 'keywords_ar'] as $field) {
            $out[$field] = trim((string) ($json[$field] ?? ''));
        }

        // Pretty-print so the admin can actually read it in the textarea.
        foreach (['schema_en', 'schema_ar'] as $field) {
            $raw = $json[$field] ?? null;
            $out[$field] = is_array($raw)
                ? (json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                : '';
        }

        return $out;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        You write SEO metadata for Qimta (كيمتا), a B2B construction-materials
        pricing and procurement platform serving Saudi Arabia and the GCC.

        What Qimta does: contractors and procurement teams upload a BOQ (Bill of
        Quantities); Qimta matches every line against an indexed catalogue of
        construction products and returns verified supplier pricing in under a
        minute.

        Rules:

        - Write Arabic natively. Do not translate the English — Saudi
          procurement professionals search in different terms than their English
          counterparts, and a translated title reads as foreign.
        - Titles: at most 60 characters so Google does not truncate them.
          Descriptions: at most 160.
        - Lead with the search intent, not the brand. "كيمتا" belongs at the end
          of a title, never the start.
        - Never invent numbers, certifications, client names, or guarantees.
          If a figure would strengthen the copy, use the placeholders
          :products and :brands — they are substituted with live catalogue
          counts at render time.
        - Keywords: 4-8 comma-separated terms a buyer would actually type. No
          keyword stuffing; the field is a weak signal and reads as spam when
          overloaded.
        - Schema: emit valid schema.org JSON-LD appropriate to the page type
          (WebPage, Organization, Product, FAQPage, CollectionPage…). Omit
          @context — it is added at render time. Include a FAQPage block only
          when the page genuinely answers questions.
        - Both locales must describe the same page truthfully. Do not promise
          in Arabic what the English does not.
        PROMPT;
    }

    /**
     * @param  array{page_url?: string, page_content?: string, focus?: string}  $context
     */
    private function userPrompt(SeoMeta $seo, array $context): string
    {
        $lines = [
            'Page route: ' . $seo->route_name,
            'Page label: ' . ($seo->label ?: '(none)'),
        ];

        if (! empty($context['page_url'])) {
            $lines[] = 'Page URL: ' . $context['page_url'];
        }

        if (! empty($context['focus'])) {
            $lines[] = 'Focus keywords the business wants to rank for: ' . $context['focus'];
        }

        // Showing the current copy lets the model improve rather than restart,
        // and keeps the voice consistent across pages.
        $current = array_filter([
            'title_en'     => $seo->title_en,
            'title_ar'     => $seo->title_ar,
            'meta_desc_en' => $seo->meta_desc_en,
            'meta_desc_ar' => $seo->meta_desc_ar,
        ]);

        if ($current) {
            $lines[] = '';
            $lines[] = 'Current copy (improve on it; keep what already works):';
            foreach ($current as $k => $v) {
                $lines[] = "  {$k}: {$v}";
            }
        }

        if (! empty($context['page_content'])) {
            $lines[] = '';
            $lines[] = 'Visible text on the page:';
            $lines[] = mb_substr((string) $context['page_content'], 0, 6000);
        }

        $lines[] = '';
        $lines[] = 'Write the metadata for this page.';

        return implode("\n", $lines);
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $schemaObject = [
            'type'                 => 'object',
            'description'          => 'schema.org JSON-LD for this page, without @context.',
            'properties'           => ['@type' => ['type' => 'string']],
            'required'             => ['@type'],
            'additionalProperties' => true,
        ];

        return [
            'type'       => 'object',
            'properties' => [
                'title_en' => [
                    'type'        => 'string',
                    'maxLength'   => self::TITLE_MAX,
                    'description' => 'English page title, at most ' . self::TITLE_MAX . ' characters.',
                ],
                'title_ar' => [
                    'type'        => 'string',
                    'maxLength'   => self::TITLE_MAX,
                    'description' => 'Arabic page title written natively, at most ' . self::TITLE_MAX . ' characters.',
                ],
                'meta_desc_en' => [
                    'type'        => 'string',
                    'maxLength'   => self::DESC_MAX,
                    'description' => 'English meta description, at most ' . self::DESC_MAX . ' characters.',
                ],
                'meta_desc_ar' => [
                    'type'        => 'string',
                    'maxLength'   => self::DESC_MAX,
                    'description' => 'Arabic meta description, at most ' . self::DESC_MAX . ' characters.',
                ],
                'keywords_en' => [
                    'type'        => 'string',
                    'description' => '4-8 comma-separated English keywords.',
                ],
                'keywords_ar' => [
                    'type'        => 'string',
                    'description' => '4-8 comma-separated Arabic keywords.',
                ],
                'schema_en' => $schemaObject,
                'schema_ar' => $schemaObject,
            ],
            'required' => [
                'title_en', 'title_ar',
                'meta_desc_en', 'meta_desc_ar',
                'keywords_en', 'keywords_ar',
                'schema_en', 'schema_ar',
            ],
            'additionalProperties' => false,
        ];
    }

    private function friendlyError(int $status, string $detail): string
    {
        return match (true) {
            $status === 401 => 'The Anthropic API key was rejected. Check ANTHROPIC_API_KEY in .env.',
            $status === 429 => 'Anthropic rate limit reached. Wait a moment and try again.',
            $status >= 500  => 'Anthropic is unavailable right now. Please try again shortly.',
            default         => 'Could not generate SEO copy: ' . mb_substr($detail, 0, 200),
        };
    }
}
