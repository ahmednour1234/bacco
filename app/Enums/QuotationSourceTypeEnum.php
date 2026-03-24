<?php

namespace App\Enums;

enum QuotationSourceTypeEnum: string
{
    case Manual  = 'manual';
    case Website = 'website';
    case Api     = 'api';

    public function label(): string
    {
        return match($this) {
            self::Manual  => 'Manual',
            self::Website => 'Website',
            self::Api     => 'API',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
