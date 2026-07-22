<?php

namespace App\Enums\Catalog\Research;

/**
 * Lifecycle of the research effort on a Product Family.
 * Prices are never part of this module — this only tracks discovery/verification.
 */
enum ResearchStatusEnum: string
{
    case NotStarted           = 'not_started';
    case Queued               = 'queued';
    case Researching          = 'researching';
    case AwaitingVerification = 'awaiting_verification';
    case PartiallyVerified    = 'partially_verified';
    case Verified             = 'verified';
    case NeedsReview          = 'needs_review';
    case Failed               = 'failed';
    case Paused               = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted           => 'Not Started',
            self::Queued               => 'Queued',
            self::Researching          => 'Researching',
            self::AwaitingVerification => 'Awaiting Verification',
            self::PartiallyVerified    => 'Partially Verified',
            self::Verified             => 'Verified',
            self::NeedsReview          => 'Needs Review',
            self::Failed               => 'Failed',
            self::Paused               => 'Paused',
        };
    }

    /** Whether a family in this state may (re)start research. */
    public function canStart(): bool
    {
        return in_array($this, [
            self::NotStarted, self::Failed, self::Paused, self::NeedsReview,
        ], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
