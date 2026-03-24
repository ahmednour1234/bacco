<?php

namespace App\Enums;

enum LogisticsStatusEnum: string
{
    case Pending    = 'pending';
    case Preparing  = 'preparing';
    case Dispatched = 'dispatched';
    case InTransit  = 'in_transit';
    case Delivered  = 'delivered';
    case Failed     = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Preparing  => 'Preparing',
            self::Dispatched => 'Dispatched',
            self::InTransit  => 'In Transit',
            self::Delivered  => 'Delivered',
            self::Failed     => 'Failed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
