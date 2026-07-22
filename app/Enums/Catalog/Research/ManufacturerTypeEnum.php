<?php

namespace App\Enums\Catalog\Research;

enum ManufacturerTypeEnum: string
{
    case Saudi   = 'saudi';
    case Gcc     = 'gcc';
    case Chinese = 'chinese';
    case Global  = 'global';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Saudi   => 'Saudi',
            self::Gcc     => 'GCC',
            self::Chinese => 'Chinese',
            self::Global  => 'Global',
            self::Unknown => 'Unknown',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
