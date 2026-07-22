<?php

namespace App\Services\Catalog\Pricing;

use App\Enums\Catalog\Pricing\MatchMethodEnum;
use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Enums\Catalog\Pricing\PriceConfidenceEnum;
use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Models\Catalog\Pricing\ScraperPriceMatch;
use App\Models\Catalog\Research\ProductVariant;
use App\Services\Catalog\Research\NormalizationEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Links scraped products to catalog variants and, when the link is strong
 * enough, records the scraped price.
 *
 * Deliberately tiered, strongest evidence first:
 *   1. exact manufacturer SKU        → auto-accept
 *   2. normalized variant key hit    → auto-accept
 *   3. manufacturer + model token    → review
 *   4. (AI, added later)             → review
 *
 * A wrong link attaches a real price to the wrong product, which is worse than
 * having no price at all — so anything below the top two tiers waits for a
 * human instead of quietly creating a price.
 */
class ScraperPriceMatchingService
{
    /** Minimum score before a candidate is even worth recording. */
    private const MIN_SCORE = 55.0;

    public function __construct(
        private readonly NormalizationEngine $engine,
    ) {}

    /**
     * Match one scraped product row.
     *
     * @param  object  $scraped  row from scraper_products
     * @param  array<int,int>  $supplierMap  scraper_source_id => supplier_id
     * @return ScraperPriceMatch|null  null when nothing plausible was found
     */
    public function matchOne(object $scraped, array $supplierMap): ?ScraperPriceMatch
    {
        $candidate = $this->findCandidate($scraped);

        if ($candidate === null) {
            return null;
        }

        [$variant, $method, $score, $reasons] = $candidate;

        if ($score < self::MIN_SCORE) {
            return null;
        }

        $status = $method->canAutoAccept() && $score >= 85.0
            ? MatchStatusEnum::AutoAccepted
            : MatchStatusEnum::Pending;

        return DB::connection('catalog')->transaction(function () use (
            $scraped, $variant, $method, $score, $reasons, $status, $supplierMap
        ) {
            // updateOrCreate keeps re-runs idempotent: the same pair updates
            // rather than piling up duplicate candidate rows.
            $match = ScraperPriceMatch::updateOrCreate(
                [
                    'scraper_product_id' => (int) $scraped->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'scraper_source_id' => $scraped->source_id ?? null,
                    'product_family_id' => $variant->product_family_id,
                    'match_method'      => $method,
                    'confidence_score'  => round($score, 2),
                    'status'            => $status,
                    'scraped_name'      => Str::limit((string) ($scraped->name ?? ''), 500, ''),
                    'scraped_sku'       => $scraped->sku ?? null,
                    'scraped_price'     => $scraped->price ?? null,
                    'scraped_currency'  => $this->currencyOf($scraped),
                    'scraped_url'       => $scraped->source_url ?? null,
                    'match_reasons'     => $reasons,
                ]
            );

            // Only accepted links may carry a price into the catalog.
            if ($status->allowsPrice() && $scraped->price !== null) {
                $price = $this->recordPrice($scraped, $variant, $supplierMap);
                if ($price !== null && $match->price_id !== $price->id) {
                    $match->update(['price_id' => $price->id]);
                }
            }

            return $match;
        });
    }

    /**
     * Find the best catalog variant for a scraped row.
     *
     * @return array{0:ProductVariant,1:MatchMethodEnum,2:float,3:array<string,mixed>}|null
     */
    private function findCandidate(object $scraped): ?array
    {
        $sku  = trim((string) ($scraped->sku ?? ''));
        $name = trim((string) ($scraped->name ?? ''));

        // --- Tier 1: exact manufacturer SKU -------------------------------
        if ($sku !== '' && $this->isDistinctiveSku($sku)) {
            $normSku = $this->engine->normalizeToken($sku);

            $variant = ProductVariant::query()
                ->where(fn ($q) => $q
                    ->where('manufacturer_sku', $sku)
                    ->orWhere('manufacturer_part_number', $sku))
                ->first();

            if ($variant !== null) {
                return [$variant, MatchMethodEnum::Sku, 95.0, [
                    'matched_on' => 'manufacturer_sku',
                    'sku'        => $sku,
                ]];
            }

            // Same SKU after normalization (dashes/case/spacing differences).
            if ($normSku !== '') {
                $variant = ProductVariant::query()
                    ->whereNotNull('manufacturer_sku')
                    ->whereRaw(
                        "LOWER(REPLACE(REPLACE(REPLACE(manufacturer_sku,'-',''),' ',''),'/','')) = ?",
                        [$normSku]
                    )
                    ->first();

                if ($variant !== null) {
                    return [$variant, MatchMethodEnum::Sku, 90.0, [
                        'matched_on'     => 'normalized_sku',
                        'normalized_sku' => $normSku,
                    ]];
                }
            }
        }

        if ($name === '') {
            return null;
        }

        // --- Tier 2: normalized variant key -------------------------------
        // The scraped title often embeds size/connection; the shared engine
        // normalizes both sides the same way so "1 1/4\"" and "DN32" agree.
        $size = $this->engine->normalizeSize($name);
        if (! empty($size['normalized'])) {
            $variant = ProductVariant::query()
                ->where('normalized_variant_key', 'like', '%|' . $size['normalized'] . '|%')
                ->whereRaw('LOWER(variant_name) LIKE ?', ['%' . mb_strtolower($this->firstToken($name)) . '%'])
                ->first();

            if ($variant !== null) {
                return [$variant, MatchMethodEnum::NormalizedKey, 88.0, [
                    'matched_on' => 'normalized_size_and_name',
                    'size'       => $size['normalized'],
                ]];
            }
        }

        // --- Tier 3: manufacturer + model token ---------------------------
        // Weakest automatic tier — always routed to review.
        $token = $this->firstToken($name);
        if (mb_strlen($token) >= 4) {
            $variant = ProductVariant::query()
                ->whereRaw('LOWER(variant_name) LIKE ?', ['%' . mb_strtolower($token) . '%'])
                ->first();

            if ($variant !== null) {
                return [$variant, MatchMethodEnum::ManufacturerModel, 62.0, [
                    'matched_on' => 'name_token',
                    'token'      => $token,
                ]];
            }
        }

        return null;
    }

    /**
     * Create or refresh the price for an accepted match.
     *
     * Scraped prices are recorded as retail unless the row says otherwise:
     * the Saudi sites in the scraper DB publish consumer pricing, and claiming
     * a wholesale tier we cannot see would corrupt supplier comparisons.
     */
    private function recordPrice(object $scraped, ProductVariant $variant, array $supplierMap): ?ProductVariantPrice
    {
        $price = (float) ($scraped->price ?? 0);
        if ($price <= 0) {
            return null;
        }

        $sourceId   = (int) ($scraped->source_id ?? 0);
        $supplierId = $supplierMap[$sourceId] ?? null;
        $source     = PriceSourceEnum::Scraped;

        return ProductVariantPrice::updateOrCreate(
            [
                'product_variant_id' => $variant->id,
                'supplier_id'        => $supplierId,
                'price_tier'         => PriceTierEnum::Retail->value,
                'min_quantity'       => 1,
                'currency'           => $this->currencyOf($scraped),
            ],
            [
                'price'              => $price,
                'source'             => $source,
                'confidence'         => $source->defaultConfidence(),
                'source_url'         => $scraped->source_url ?? null,
                'scraper_product_id' => (int) $scraped->id,
                'scraper_source_id'  => $sourceId ?: null,
                'captured_at'        => $scraped->last_scraped_at ?? now(),
                'is_active'          => true,
            ]
        );
    }

    /**
     * Whether a SKU string is distinctive enough to match on.
     *
     * Probing the live data showed the only "exact SKU matches" between the
     * scraper and the catalog were bare numbers like 1100, 440 and 700 — these
     * are voltages, wattages and sizes that happen to collide, not product
     * identifiers. Matching on them would attach a cable's price to a server.
     *
     * A real manufacturer SKU mixes letters and digits (F5-BIG-I5800-AC,
     * FR-5000, NL95046), so that is what we require.
     */
    private function isDistinctiveSku(string $sku): bool
    {
        $sku = trim($sku);

        // Too short to identify anything.
        if (mb_strlen($sku) < 4) {
            return false;
        }

        // Digits only (with optional separators) — a number, not a SKU.
        if (preg_match('/^[\d\s\-.,\/]+$/', $sku)) {
            return false;
        }

        // Must contain at least one letter AND one digit.
        return preg_match('/[a-zA-Z]/', $sku) === 1
            && preg_match('/\d/', $sku) === 1;
    }

    /** Scraper rows are Saudi storefronts; SAR unless a column says otherwise. */
    private function currencyOf(object $scraped): string
    {
        $currency = $scraped->currency ?? null;

        return is_string($currency) && $currency !== '' ? strtoupper($currency) : 'SAR';
    }

    /** First meaningful word of a title, used as a cheap model hint. */
    private function firstToken(string $name): string
    {
        foreach (preg_split('/[\s,\-–—|]+/u', $name) ?: [] as $part) {
            $part = trim($part);
            if (mb_strlen($part) >= 4) {
                return $part;
            }
        }

        return '';
    }
}
