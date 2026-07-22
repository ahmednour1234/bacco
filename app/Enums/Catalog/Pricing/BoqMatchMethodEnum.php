<?php

namespace App\Enums\Catalog\Pricing;

/**
 * How a BOQ line was linked to a catalog product.
 *
 * BOQ text is written by engineers, not extracted from catalogs, so most
 * matches are inferred from specifications rather than identifiers. Only the
 * top tiers are certain enough to price without a human looking.
 */
enum BoqMatchMethodEnum: string
{
    case ExactSku   = 'exact_sku';    // the BOQ names a manufacturer SKU
    case BrandModel = 'brand_model';  // brand + model number both present
    case SpecMatch  = 'spec_match';   // type + size + material agree
    case FamilyOnly = 'family_only';  // right product family, specs unclear
    case Manual     = 'manual';       // a human linked them

    public function label(): string
    {
        return match ($this) {
            self::ExactSku   => 'Exact SKU',
            self::BrandModel => 'Brand + Model',
            self::SpecMatch  => 'Specification Match',
            self::FamilyOnly => 'Family Only',
            self::Manual     => 'Manual',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::ExactSku   => 'مطابقة SKU',
            self::BrandModel => 'ماركة + موديل',
            self::SpecMatch  => 'مطابقة مواصفات',
            self::FamilyOnly => 'نفس العائلة فقط',
            self::Manual     => 'يدوي',
        };
    }

    public function baseConfidence(): float
    {
        return match ($this) {
            self::Manual     => 100.0,
            self::ExactSku   => 96.0,
            self::BrandModel => 85.0,
            self::SpecMatch  => 68.0,
            self::FamilyOnly => 40.0,
        };
    }

    /**
     * Whether this method may be selected for pricing without review. A wrong
     * pick here goes straight into a customer quotation, so only identifier
     * based matches qualify.
     */
    public function canAutoSelect(): bool
    {
        return $this === self::ExactSku || $this === self::Manual;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
