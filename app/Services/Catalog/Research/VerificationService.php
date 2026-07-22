<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\SourceTypeEnum;
use App\Enums\Catalog\Research\VerificationLevelEnum;
use App\Enums\Catalog\Research\VerificationStatusEnum;

/**
 * Applies the anti-hallucination verification rules IN CODE (not just in the
 * prompt). Given what the AI claimed about a variant and the sources it cited,
 * it decides the variant's verification_level and verification_status.
 *
 * Key rules enforced here:
 *  #1  A variant is only "verified" with an official source.
 *  #2  A manufacturer SKU is only trusted with a supporting source.
 *  #5  Distributor-only info is at most partially verified.
 *  #6  Any claim with no URL goes to review (pending, needs review).
 * The AI's own verification_level is treated as a hint and can only be
 * *downgraded* here, never blindly trusted upward.
 */
class VerificationService
{
    public function __construct(private SourceVerificationService $sources) {}

    /**
     * @param  array<string,mixed>  $variant  the AI variant node
     * @return array{level:VerificationLevelEnum, status:VerificationStatusEnum, needs_review:bool, reasons:list<string>}
     */
    public function assessVariant(array $variant, bool $officialDomainMatched): array
    {
        $reasons = [];
        $sources = $variant['sources'] ?? [];
        $sku     = $variant['manufacturer_sku'] ?? null;

        $claimed = $this->claimedLevel($variant);

        // Classify the best available source.
        $hasOfficial    = false;
        $hasDistributor = false;
        $hasAnyUrl      = false;

        foreach ($sources as $src) {
            $url  = $src['url'] ?? null;
            $type = $this->sourceType($src['source_type'] ?? null);
            if ($url) {
                $hasAnyUrl = true;
            }
            $domain = $this->sources->extractDomain($url);
            if ($this->sources->isBlacklisted($domain)) {
                continue; // marketplaces never count
            }
            if ($type?->isOfficial() && ($src['is_official'] ?? false) && $officialDomainMatched) {
                $hasOfficial = true;
            } elseif ($type === SourceTypeEnum::AuthorizedDistributor) {
                $hasDistributor = true;
            }
        }

        // Rule #6: no URL at all → straight to review.
        if (! $hasAnyUrl) {
            $reasons[] = 'No source URL provided.';

            return [
                'level'        => VerificationLevelEnum::AiDiscoveredUnverified,
                'status'       => VerificationStatusEnum::NeedsReview,
                'needs_review' => true,
                'reasons'      => $reasons,
            ];
        }

        // Derive the effective level from evidence, never above what's supported.
        $effective = match (true) {
            $hasOfficial && $sku       => VerificationLevelEnum::ExactManufacturerSku,
            $hasOfficial               => VerificationLevelEnum::OfficialModelAndSize,
            $hasDistributor            => VerificationLevelEnum::DistributorOnly,
            default                    => VerificationLevelEnum::AiDiscoveredUnverified,
        };

        // Never let the AI's claim exceed the evidence (#2, downgrade-only).
        if ($claimed && $claimed->rank() < $effective->rank()) {
            $effective = $claimed;
            $reasons[] = 'Downgraded to the AI-claimed level.';
        }
        if ($claimed && $effective->rank() < $claimed->rank()) {
            $reasons[] = 'AI claimed a higher level than evidence supports; downgraded.';
        }

        // Rule #1 & #5: status follows the level.
        [$status, $needsReview] = match (true) {
            $effective->isOfficialGrade()                        => [VerificationStatusEnum::Verified, false],
            $effective === VerificationLevelEnum::DistributorOnly => [VerificationStatusEnum::PartiallyVerified, true],
            default                                               => [VerificationStatusEnum::NeedsReview, true],
        };

        // Rule #2: an SKU with no official source cannot be "verified".
        if ($sku && ! $hasOfficial) {
            $reasons[] = 'SKU not backed by an official source.';
            if ($status === VerificationStatusEnum::Verified) {
                $status      = VerificationStatusEnum::PartiallyVerified;
                $needsReview = true;
            }
        }

        return [
            'level'        => $effective,
            'status'       => $status,
            'needs_review' => $needsReview,
            'reasons'      => $reasons,
        ];
    }

    private function claimedLevel(array $variant): ?VerificationLevelEnum
    {
        $v = $variant['verification_level'] ?? null;

        return $v ? VerificationLevelEnum::tryFrom($v) : null;
    }

    private function sourceType(?string $value): ?SourceTypeEnum
    {
        return $value ? SourceTypeEnum::tryFrom($value) : null;
    }
}
