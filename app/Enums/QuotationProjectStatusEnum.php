<?php

namespace App\Enums;

enum QuotationProjectStatusEnum: string
{
    case Tender     = 'tender';
    case Pending    = 'pending';
    case Active     = 'active';
    case OnHold     = 'on_hold';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Tender    => __('app.status_tender'),
            self::Pending   => __('app.status_pending'),
            self::Active    => __('app.status_active'),
            self::OnHold    => __('app.status_on_hold'),
            self::Completed => __('app.status_completed'),
            self::Cancelled => __('app.status_cancelled'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
