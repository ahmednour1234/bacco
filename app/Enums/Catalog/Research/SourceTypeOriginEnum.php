<?php

namespace App\Enums\Catalog\Research;

/** How a manufacturer got linked to a product family (the pivot's source_type). */
enum SourceTypeOriginEnum: string
{
    case ImportedFromExcel     = 'imported_from_excel';
    case DiscoveredByResearch  = 'discovered_by_research';
    case ManuallyAdded         = 'manually_added';

    public function label(): string
    {
        return match ($this) {
            self::ImportedFromExcel    => 'Imported from Excel',
            self::DiscoveredByResearch => 'Discovered by Research',
            self::ManuallyAdded        => 'Manually Added',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
