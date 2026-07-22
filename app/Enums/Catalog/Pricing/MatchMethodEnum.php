<?php

namespace App\Enums\Catalog\Pricing;

/**
 * How a scraped product was linked to a catalog variant. The method decides
 * how much the link is trusted — an exact SKU hit is near-certain, while a
 * fuzzy name match is a suggestion that a human should confirm.
 */
enum MatchMethodEnum: string
{
    case Sku               = 'sku';                // identical manufacturer SKU
    case NormalizedKey     = 'normalized_key';     // normalized variant key hit
    case ManufacturerModel = 'manufacturer_model'; // manufacturer + model + size
    case Ai                = 'ai';                 // model judged them equivalent
    case Manual            = 'manual';             // a human linked them

    public function label(): string
    {
        return match ($this) {
            self::Sku               => 'Exact SKU',
            self::NormalizedKey     => 'Normalized Key',
            self::ManufacturerModel => 'Manufacturer + Model',
            self::Ai                => 'AI Match',
            self::Manual            => 'Manual',
        };
    }

    /** Baseline confidence (0..100) before any per-match adjustments. */
    public function baseConfidence(): float
    {
        return match ($this) {
            self::Manual            => 100.0,
            self::Sku               => 95.0,
            self::NormalizedKey     => 88.0,
            self::ManufacturerModel => 75.0,
            self::Ai                => 60.0,
        };
    }

    /**
     * Whether this method may create a price without human review. Fuzzy and
     * AI matches never auto-accept: a wrong link would attach a real price to
     * the wrong product, which is worse than having no price at all.
     */
    public function canAutoAccept(): bool
    {
        return $this === self::Sku
            || $this === self::NormalizedKey
            || $this === self::Manual;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
