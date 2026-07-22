<?php

namespace App\Services\Catalog\Research\DeepSeek\Dto;

/**
 * A validated research response. The raw provider text is kept for auditing;
 * `data` is the schema-validated associative array (or an empty structure when
 * the provider found nothing). `valid` distinguishes "found nothing" from
 * "response rejected by schema".
 */
final class ResearchResponse
{
    /**
     * @param  array<string,mixed>  $data            schema-valid parsed payload
     * @param  list<string>         $validationErrors
     * @param  array<string,mixed>  $usage           token usage, if reported
     */
    public function __construct(
        public readonly bool $valid,
        public readonly array $data,
        public readonly string $rawResponse = '',
        public readonly array $validationErrors = [],
        public readonly array $usage = [],
    ) {}

    public static function invalid(string $raw, array $errors): self
    {
        return new self(false, [], $raw, $errors);
    }

    public static function valid(array $data, string $raw, array $usage = []): self
    {
        return new self(true, $data, $raw, [], $usage);
    }

    /** Convenience empty response (no results, still valid). */
    public static function empty(string $raw = ''): self
    {
        return new self(true, [
            'product_family'   => null,
            'manufacturer'     => null,
            'series'           => [],
            'unverified_items' => [],
            'warnings'         => [],
        ], $raw);
    }

    /** @return list<array<string,mixed>> */
    public function series(): array
    {
        return $this->data['series'] ?? [];
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->data['warnings'] ?? [];
    }
}
