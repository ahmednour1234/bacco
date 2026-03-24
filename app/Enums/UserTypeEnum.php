<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case Admin    = 'admin';
    case Employee = 'employee';
    case Client   = 'client';
    case Supplier = 'supplier';

    public function label(): string
    {
        return match($this) {
            self::Admin    => 'Admin',
            self::Employee => 'Employee',
            self::Client   => 'Client',
            self::Supplier => 'Supplier',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
