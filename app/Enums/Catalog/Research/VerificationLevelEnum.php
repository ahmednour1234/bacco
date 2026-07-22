<?php

namespace App\Enums\Catalog\Research;

/**
 * How strongly a Product Variant is grounded in a real, official source.
 * Computed in code (VerificationService), never trusted blindly from the AI.
 *
 * Ordered strongest → weakest so rank() can gate what "verified" means.
 */
enum VerificationLevelEnum: string
{
    case ExactManufacturerSku  = 'exact_manufacturer_sku';
    case OfficialModelAndSize  = 'official_model_and_size';
    case OfficialSeriesRange   = 'official_series_range';
    case DistributorOnly       = 'distributor_only';
    case AiDiscoveredUnverified = 'ai_discovered_unverified';

    public function label(): string
    {
        return match ($this) {
            self::ExactManufacturerSku  => 'Exact Manufacturer SKU',
            self::OfficialModelAndSize  => 'Official Model & Size',
            self::OfficialSeriesRange   => 'Official Series Range',
            self::DistributorOnly       => 'Distributor Only',
            self::AiDiscoveredUnverified => 'AI Discovered (Unverified)',
        };
    }

    /** Strength rank: higher = stronger evidence. */
    public function rank(): int
    {
        return match ($this) {
            self::ExactManufacturerSku  => 5,
            self::OfficialModelAndSize  => 4,
            self::OfficialSeriesRange   => 3,
            self::DistributorOnly       => 2,
            self::AiDiscoveredUnverified => 1,
        };
    }

    /** Only official-grade evidence may back a "verified" variant. */
    public function isOfficialGrade(): bool
    {
        return $this->rank() >= self::OfficialSeriesRange->rank();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
