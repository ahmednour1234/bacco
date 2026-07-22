<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\SourceStatusEnum;
use App\Enums\Catalog\Research\SourceTypeEnum;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\SourceDocument;
use Illuminate\Support\Str;

/**
 * Validates source documents: extracts the domain, checks it against the
 * manufacturer's official domain, and flags blacklisted/marketplace hosts.
 * Enforces (in code) rules #6, #7, #8 of the anti-hallucination spec:
 *   - a source with no URL → not official,
 *   - a non-manufacturer URL → flagged,
 *   - the manufacturer's official domain must match for "official" sources.
 */
class SourceVerificationService
{
    public function extractDomain(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        return Str::of($host)->lower()->replaceFirst('www.', '')->value();
    }

    /** True when the domain is a marketplace/reseller that can never be final. */
    public function isBlacklisted(?string $domain): bool
    {
        if (! $domain) {
            return false;
        }

        foreach ((array) config('catalog_research.blacklisted_source_domains', []) as $bad) {
            if (str_contains($domain, $bad)) {
                return true;
            }
        }

        return false;
    }

    /** Does this URL belong to the manufacturer's official domain? */
    public function matchesManufacturer(?string $url, ?Manufacturer $manufacturer): bool
    {
        if (! $manufacturer || ! $manufacturer->official_domain) {
            return false;
        }

        $domain = $this->extractDomain($url);

        return $domain !== null && str_contains($domain, Str::lower($manufacturer->official_domain));
    }

    /**
     * Verify a persisted source document and update its status/officiality.
     * A blacklisted or non-manufacturer domain is never treated as official.
     */
    public function verifySource(SourceDocument $source): SourceDocument
    {
        $domain = $this->extractDomain($source->source_url);
        $source->domain = $domain;

        if (! $source->source_url) {
            $source->is_official   = false;
            $source->source_status = SourceStatusEnum::Flagged;
        } elseif ($this->isBlacklisted($domain)) {
            $source->is_official   = false;
            $source->source_status = SourceStatusEnum::Flagged;
        } else {
            $official = $source->source_type instanceof SourceTypeEnum
                ? $source->source_type->isOfficial()
                : false;

            $matches = $this->matchesManufacturer($source->source_url, $source->manufacturer);
            $source->is_official   = $official && $matches;
            $source->source_status = $matches ? SourceStatusEnum::Active : SourceStatusEnum::Flagged;
        }

        $source->checked_at = now();
        $source->save();

        return $source;
    }
}
