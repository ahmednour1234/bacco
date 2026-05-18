<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending   = 'pending';
    case Submitted = 'submitted';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Pending   => __('app.status_pending'),
            self::Submitted => __('app.status_submitted'),
            self::Approved  => __('app.status_approved'),
            self::Rejected  => __('app.status_rejected'),
            self::Refunded  => __('app.status_refunded'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
