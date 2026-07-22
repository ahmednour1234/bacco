<?php

namespace App\Enums\Catalog\Research;

/**
 * Human/verification workflow state, shared by variants, models,
 * manufacturers and source evidence.
 */
enum VerificationStatusEnum: string
{
    case Pending           = 'pending';
    case Verified          = 'verified';
    case PartiallyVerified = 'partially_verified';
    case Rejected          = 'rejected';
    case NeedsReview       = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::Pending           => 'Pending',
            self::Verified          => 'Verified',
            self::PartiallyVerified => 'Partially Verified',
            self::Rejected          => 'Rejected',
            self::NeedsReview       => 'Needs Review',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
