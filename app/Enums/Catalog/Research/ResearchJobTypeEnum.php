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
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
