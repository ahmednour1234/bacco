<?php

namespace App\Enums\Catalog\Pricing;

/**
 * Lifecycle of a scraped-product → catalog-variant link.
 *
 * Only AutoAccepted and Confirmed links may carry a price into the catalog;
 * Pending links wait in the review queue rather than quietly attaching a price
 * to a product that may be wrong.
 */
enum MatchStatusEnum: string
{
    case Pending      = 'pending';       // awaiting human review
    case AutoAccepted = 'auto_accepted'; // high-confidence, accepted by rule
    case Confirmed    = 'confirmed';     // a human approved it
    case Rejected     = 'rejected';      // a human rejected it

    public function label(): string
    {
        return match ($this) {
            self::Pending      => 'Pending Review',
            self::AutoAccepted => 'Auto Accepted',
            self::Confirmed    => 'Confirmed',
            self::Rejected     => 'Rejected',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Pending      => 'بانتظار المراجعة',
            self::AutoAccepted => 'مقبول تلقائيًا',
            self::Confirmed    => 'مؤكد',
            self::Rejected     => 'مرفوض',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending      => 'bg-amber-100 text-amber-700',
            self::AutoAccepted => 'bg-sky-100 text-sky-700',
            self::Confirmed    => 'bg-emerald-100 text-emerald-700',
            self::Rejected     => 'bg-red-100 text-red-700',
        };
    }

    /** Whether a price may exist in the catalog for this link. */
    public function allowsPrice(): bool
    {
        return $this === self::AutoAccepted || $this === self::Confirmed;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
