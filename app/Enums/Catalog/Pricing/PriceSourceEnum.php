<?php

namespace App\Enums\Catalog\Pricing;

/**
 * How we learned a price. This is the pricing counterpart of the research
 * module's verification levels: an AI estimate must never be presented as a
 * firm supplier quote, so trust is encoded in the type rather than left to
 * whoever reads the number.
 *
 * Ordered strongest → weakest.
 */
enum PriceSourceEnum: string
{
    case SupplierQuote = 'supplier_quote'; // written quote from a supplier
    case Manual        = 'manual';         // entered by staff from a known source
    case CatalogPdf    = 'catalog_pdf';    // published manufacturer/distributor catalog
    case Scraped       = 'scraped';        // read from a public web page
    case AiEstimate    = 'ai_estimate';    // model estimate — indicative only

    public function label(): string
    {
        return match ($this) {
            self::SupplierQuote => 'Supplier Quote',
            self::Manual        => 'Manual Entry',
            self::CatalogPdf    => 'Catalog / PDF',
            self::Scraped       => 'Scraped (Web)',
            self::AiEstimate    => 'AI Estimate',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::SupplierQuote => 'عرض سعر من مورّد',
            self::Manual        => 'إدخال يدوي',
            self::CatalogPdf    => 'كتالوج / PDF',
            self::Scraped       => 'مسحوب من الويب',
            self::AiEstimate    => 'تقدير بالذكاء الاصطناعي',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::SupplierQuote => 5,
            self::Manual        => 4,
            self::CatalogPdf    => 3,
            self::Scraped       => 2,
            self::AiEstimate    => 1,
        };
    }

    /**
     * Whether this price may back a binding customer quotation. AI estimates
     * never can; scraped prices are indicative until a human confirms them.
     */
    public function isQuotable(): bool
    {
        return $this->rank() >= self::CatalogPdf->rank();
    }

    /** Estimates must be visibly labelled wherever they are shown. */
    public function requiresEstimateWarning(): bool
    {
        return $this === self::AiEstimate;
    }

    public function defaultConfidence(): PriceConfidenceEnum
    {
        return match ($this) {
            self::SupplierQuote, self::Manual => PriceConfidenceEnum::Verified,
            self::CatalogPdf, self::Scraped   => PriceConfidenceEnum::Unverified,
            self::AiEstimate                  => PriceConfidenceEnum::Estimated,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
