<?php

namespace App\Enums\Catalog\Research;

/** Status for review-queue items and duplicate candidates. */
enum ReviewStatusEnum: string
{
    case Open       = 'open';
    case InReview   = 'in_review';
    case Resolved   = 'resolved';
    case Dismissed  = 'dismissed';
    case Merged     = 'merged';
    case NotDuplicate = 'not_duplicate';

    public function label(): string
    {
        return match ($this) {
            self::Open         => 'Open',
            self::InReview     => 'In Review',
            self::Resolved     => 'Resolved',
            self::Dismissed    => 'Dismissed',
            self::Merged       => 'Merged',
            self::NotDuplicate => 'Not a Duplicate',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
