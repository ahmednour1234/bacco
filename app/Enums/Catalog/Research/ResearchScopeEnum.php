<?php

namespace App\Enums\Catalog\Research;

/**
 * Geographic / manufacturer scope a research plan targets.
 * Existence globally is never treated as proof of Saudi availability.
 */
enum ResearchScopeEnum: string
{
    case Saudi               = 'saudi';
    case Gcc                 = 'gcc';
    case Global              = 'global';
    case SelectedManufacturers = 'selected_manufacturers';

    public function label(): string
    {
        return match ($this) {
            self::Saudi               => 'Saudi',
            self::Gcc                 => 'GCC',
            self::Global              => 'Global',
            self::SelectedManufacturers => 'Selected Manufacturers',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
