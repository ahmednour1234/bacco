<?php

namespace App\Enums\Catalog\Research;

/** Market availability of a variant/model. Regional availability is never assumed. */
enum AvailabilityStatusEnum: string
{
    case Current      = 'current';
    case Discontinued = 'discontinued';
    case Regional     = 'regional';
    case Unknown      = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Current      => 'Current',
            self::Discontinued => 'Discontinued',
            self::Regional     => 'Regional',
            self::Unknown      => 'Unknown',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
