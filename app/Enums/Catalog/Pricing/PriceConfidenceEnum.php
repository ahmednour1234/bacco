<?php

namespace App\Enums\Catalog\Pricing;

/**
 * How much a stored price can be relied on right now. Distinct from
 * PriceSourceEnum: source is where it came from (fixed), confidence is its
 * current standing (changes as prices age or get confirmed).
 */
enum PriceConfidenceEnum: string
{
    case Verified   = 'verified';   // confirmed by a human or a firm quote
    case Unverified = 'unverified'; // plausible, not yet confirmed
    case Estimated  = 'estimated';  // indicative only — never binding
    case Stale      = 'stale';      // was valid, now too old to trust

    public function label(): string
    {
        return match ($this) {
            self::Verified   => 'Verified',
            self::Unverified => 'Unverified',
            self::Estimated  => 'Estimated',
            self::Stale      => 'Stale',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Verified   => 'مؤكد',
            self::Unverified => 'غير مؤكد',
            self::Estimated  => 'تقديري',
            self::Stale      => 'قديم',
        };
    }

    /** Tailwind badge classes, matching the palette used across the catalog UI. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Verified   => 'bg-emerald-100 text-emerald-700',
            self::Unverified => 'bg-amber-100 text-amber-700',
            self::Estimated  => 'bg-sky-100 text-sky-700',
            self::Stale      => 'bg-gray-200 text-gray-600',
        };
    }

    public function isUsableForQuotation(): bool
    {
        return $this === self::Verified || $this === self::Unverified;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
