<?php

namespace App\Enums\Catalog\Research;

/**
 * Status of a research Excel import (distinct from the existing pricing
 * `catalog_imports` — this module's imports table is `research_catalog_imports`).
 */
enum CatalogImportStatusEnum: string
{
    case Uploaded          = 'uploaded';
    case MappingRequired   = 'mapping_required';
    case Processing        = 'processing';
    case Completed         = 'completed';
    case PartiallyCompleted = 'partially_completed';
    case Failed            = 'failed';
    case Cancelled         = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded           => 'Uploaded',
            self::MappingRequired    => 'Mapping Required',
            self::Processing         => 'Processing',
            self::Completed          => 'Completed',
            self::PartiallyCompleted => 'Partially Completed',
            self::Failed             => 'Failed',
            self::Cancelled          => 'Cancelled',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
