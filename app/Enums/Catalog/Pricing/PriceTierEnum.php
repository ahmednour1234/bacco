<?php

namespace App\Enums\Catalog\Pricing;

/**
 * The commercial band a price belongs to. The same real product carries very
 * different prices depending on how it is bought — retail vs trade vs large
 * quantity — so a price is meaningless without its tier.
 *
 * Ordered cheapest-expected → most expensive for sane default sorting.
 */
enum PriceTierEnum: string
{
    case Bulk      = 'bulk';       // large quantity, lowest unit price
    case Project   = 'project';    // negotiated for a specific project
    case Wholesale = 'wholesale';  // trade price, usually needs a MOQ
    case Retail    = 'retail';     // single-unit consumer price
    case ListPrice = 'list';       // manufacturer list / MSRP (reference only)

    public function label(): string
    {
        return match ($this) {
            self::Bulk      => 'Bulk (Large Quantity)',
            self::Project   => 'Project Price',
            self::Wholesale => 'Wholesale',
            self::Retail    => 'Retail',
            self::ListPrice => 'List Price / MSRP',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Bulk      => 'جملة بكميات كبيرة',
            self::Project   => 'سعر مشروع',
            self::Wholesale => 'جملة',
            self::Retail    => 'قطاعي',
            self::ListPrice => 'سعر القائمة',
        };
    }

    /**
     * List price is a published reference, not something anyone actually pays;
     * it must never be used as the basis of a quotation on its own.
     */
    public function isQuotable(): bool
    {
        return $this !== self::ListPrice;
    }

    /** Typical minimum quantity when the source does not state one. */
    public function defaultMinQuantity(): int
    {
        return match ($this) {
            self::Bulk      => 100,
            self::Wholesale => 10,
            default         => 1,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
