<?php

namespace App\Services\Catalog\Research\DeepSeek\Dto;

use App\Enums\Catalog\Research\ResearchJobTypeEnum;

/**
 * Immutable description of a single research request. It is intentionally
 * provider-agnostic — the provider turns it into a concrete API payload.
 *
 * The request is scoped to ONE stage (discover manufacturers, discover series,
 * discover variants, verify…) so a family is never crammed into one huge prompt.
 */
final class ResearchRequest
{
    /**
     * @param  array<string,mixed>  $context  structured context for the prompt
     *                                        (family name, manufacturer, series…)
     */
    public function __construct(
        public readonly ResearchJobTypeEnum $type,
        public readonly string $familyName,
        public readonly string $normalizedFamilyName,
        public readonly array $context = [],
        public readonly ?string $manufacturerName = null,
        public readonly ?string $marketScope = null,
    ) {}

    /** @param array<string,mixed> $context */
    public static function make(
        ResearchJobTypeEnum $type,
        string $familyName,
        string $normalizedFamilyName,
        array $context = [],
        ?string $manufacturerName = null,
        ?string $marketScope = null,
    ): self {
        return new self($type, $familyName, $normalizedFamilyName, $context, $manufacturerName, $marketScope);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'type'                   => $this->type->value,
            'family_name'            => $this->familyName,
            'normalized_family_name' => $this->normalizedFamilyName,
            'manufacturer'           => $this->manufacturerName,
            'market_scope'           => $this->marketScope,
            'context'                => $this->context,
        ];
    }
}
