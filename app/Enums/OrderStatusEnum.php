<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Open   = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open   => __('app.status_open'),
            self::Closed => __('app.status_closed'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
