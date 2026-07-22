<?php

namespace App\Enums\Catalog\Research;

/** The staged research pipeline — each row of Excel is researched in phases. */
enum ResearchJobTypeEnum: string
{
    case DiscoverManufacturers  = 'discover_manufacturers';
    case DiscoverProductSeries  = 'discover_product_series';
    case DiscoverModels         = 'discover_models';
    case DiscoverVariants       = 'discover_variants';
    case VerifyProduct          = 'verify_product';
    case VerifyApproval         = 'verify_approval';
    case VerifySource           = 'verify_source';
    case EnrichMissingFields    = 'enrich_missing_fields';
    case DetectDuplicates       = 'detect_duplicates';
    case RefreshExistingProducts = 'refresh_existing_products';

    // --- Deep Catalog Expansion ------------------------------------------
    // Growth comes from enumerating what manufacturers actually publish, not
    // from combining attributes. Both types below still obey the no-invention
    // rules: every product needs a source, every size must be published.
    case ManufacturerCatalogSweep = 'manufacturer_catalog_sweep';
    case SizeRangeExpansion       = 'size_range_expansion';

    public function label(): string
    {
        return match ($this) {
            self::DiscoverManufacturers  => 'Discover Manufacturers',
            self::DiscoverProductSeries  => 'Discover Product Series',
            self::DiscoverModels         => 'Discover Models',
            self::DiscoverVariants       => 'Discover Variants',
            self::VerifyProduct          => 'Verify Product',
            self::VerifyApproval         => 'Verify Approval',
            self::VerifySource           => 'Verify Source',
            self::EnrichMissingFields    => 'Enrich Missing Fields',
            self::DetectDuplicates       => 'Detect Duplicates',
            self::RefreshExistingProducts => 'Refresh Existing Products',
            self::ManufacturerCatalogSweep => 'Manufacturer Catalog Sweep',
            self::SizeRangeExpansion       => 'Official Size Range Expansion',
        };
    }

    /**
     * Expansion jobs enumerate an existing maker's published catalog rather
     * than researching one Excel row, so they are dispatched per manufacturer
     * and may run without a product family.
     */
    public function isExpansion(): bool
    {
        return $this === self::ManufacturerCatalogSweep
            || $this === self::SizeRangeExpansion;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
