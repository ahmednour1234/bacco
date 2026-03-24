<?php

namespace App\Enums;

enum WebsiteTypeEnum: string
{
    case Ecommerce  = 'ecommerce';
    case Tender     = 'tender';
    case Government = 'government';
    case Supplier   = 'supplier';
    case Other      = 'other';

    public function label(): string
    {
        return match($this) {
            self::Ecommerce  => 'E-commerce',
            self::Tender     => 'Tender',
            self::Government => 'Government',
            self::Supplier   => 'Supplier',
            self::Other      => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
