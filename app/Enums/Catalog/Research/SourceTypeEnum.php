<?php

namespace App\Enums\Catalog\Research;

/**
 * Type of a source document. The ordering encodes the trust priority used for
 * verification: official manufacturer sources outrank distributors, which
 * outrank generic search results. Blogs/marketplaces are never a final source.
 */
enum SourceTypeEnum: string
{
    case OfficialProductPage   = 'official_product_page';
    case OfficialCatalogPdf    = 'official_catalog_pdf';
    case OfficialDatasheet     = 'official_datasheet';
    case OfficialCertificate   = 'official_certificate';
    case OfficialBimPage       = 'official_bim_page';
    case AuthorizedDistributor = 'authorized_distributor';
    case SearchResult          = 'search_result';
    case Other                 = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OfficialProductPage   => 'Official Product Page',
            self::OfficialCatalogPdf    => 'Official Catalog PDF',
            self::OfficialDatasheet     => 'Official Datasheet',
            self::OfficialCertificate   => 'Official Certificate',
            self::OfficialBimPage       => 'Official BIM Page',
            self::AuthorizedDistributor => 'Authorized Distributor',
            self::SearchResult          => 'Search Result',
            self::Other                 => 'Other',
        };
    }

    /** Trust priority: higher = stronger. Search/other are discovery-only. */
    public function trustRank(): int
    {
        return match ($this) {
            self::OfficialProductPage   => 7,
            self::OfficialCatalogPdf    => 6,
            self::OfficialDatasheet     => 5,
            self::OfficialCertificate   => 4,
            self::OfficialBimPage       => 3,
            self::AuthorizedDistributor => 2,
            self::SearchResult          => 1,
            self::Other                 => 0,
        };
    }

    /** True for manufacturer-owned official sources (eligible to "verify"). */
    public function isOfficial(): bool
    {
        return in_array($this, [
            self::OfficialProductPage, self::OfficialCatalogPdf,
            self::OfficialDatasheet, self::OfficialCertificate, self::OfficialBimPage,
        ], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
