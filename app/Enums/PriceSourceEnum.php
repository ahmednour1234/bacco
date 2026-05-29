<?php

namespace App\Enums;

enum PriceSourceEnum: string
{
    case Manual   = 'manual';
    case Supplier = 'supplier';
    case Catalog  = 'catalog';
    case DeepSeek = 'deepseek';
    case Boq      = 'boq';

    public function label(): string
    {
        return match($this) {
            self::Manual   => 'Manual',
            self::Supplier => 'Supplier',
            self::Catalog  => 'Catalog',
            self::DeepSeek => 'AI (DeepSeek)',
            self::Boq      => 'BOQ',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
