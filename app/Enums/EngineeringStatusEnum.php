<?php

namespace App\Enums;

enum EngineeringStatusEnum: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Reviewing  = 'reviewing';
    case Approved   = 'approved';
    case Rejected   = 'rejected';
    case Completed  = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Reviewing  => 'Reviewing',
            self::Approved   => 'Approved',
            self::Rejected   => 'Rejected',
            self::Completed  => 'Completed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
