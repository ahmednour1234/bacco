<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use App\Enums\Catalog\Research\VerificationStatusEnum;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ResearchJob;

/**
 * Derives a Product Family's research_status from the state of its jobs and the
 * verification level of the variants discovered for it. Kept separate so both
 * the queue jobs and the admin UI compute progress the same way.
 */
class ResearchProgressService
{
    /** Recompute and persist the family's research_status. */
    public function refreshFamilyStatus(?ProductFamily $family): void
    {
        if (! $family) {
            return;
        }

        $jobs = ResearchJob::where('product_family_id', $family->id)->get();

        // Still work outstanding → researching.
        $hasPending = $jobs->contains(fn ($j) => in_array($j->status, [
            ResearchJobStatusEnum::Pending,
            ResearchJobStatusEnum::Queued,
            ResearchJobStatusEnum::Processing,
            ResearchJobStatusEnum::AwaitingValidation,
        ], true));

        if ($hasPending) {
            $family->update(['research_status' => ResearchStatusEnum::Researching]);

            return;
        }

        // All jobs terminal — grade by variant verification.
        $variants = $family->variants()->get(['verification_status']);

        if ($variants->isEmpty()) {
            $family->update(['research_status' => ResearchStatusEnum::NeedsReview]);

            return;
        }

        $verified = $variants->filter(
            fn ($v) => $v->verification_status === VerificationStatusEnum::Verified
        )->count();

        $status = match (true) {
            $verified === $variants->count() => ResearchStatusEnum::Verified,
            $verified > 0                    => ResearchStatusEnum::PartiallyVerified,
            default                          => ResearchStatusEnum::AwaitingVerification,
        };

        $family->update(['research_status' => $status]);
    }
}
