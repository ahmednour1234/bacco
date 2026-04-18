<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Processing = 'processing';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case Refunded   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Pending    => __('app.status_pending'),
            self::Confirmed  => __('app.status_confirmed'),
            self::Processing => __('app.status_processing'),
            self::Shipped    => __('app.status_shipped'),
            self::Delivered  => __('app.status_delivered'),
            self::Completed  => __('app.status_completed'),
            self::Cancelled  => __('app.status_cancelled'),
            self::Refunded   => __('app.status_refunded'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
