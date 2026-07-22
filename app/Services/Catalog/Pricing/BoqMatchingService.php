<?php

namespace App\Services\Catalog\Pricing;

use App\Enums\Catalog\Pricing\BoqMatchMethodEnum;
use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Models\BoqItem;
use App\Models\Catalog\Pricing\BoqVariantMatch;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Finds the real catalog products that satisfy a BOQ line, and prices them.
 *
 * This is what makes the catalog usable: a BOQ says "brass ball valve 2 inch"
 * — unquotable — while a variant is a specific SKU with a price. Several
 * candidates are kept per line (different manufacturers) and ranked, so a
 * reviewer can compare rather than being handed one opaque answer.
 *
 * Only identifier-grade matches auto-select. A wrong pick flows straight into a
 * customer quotation, so specification matches always wait for a human.
 */
class BoqMatchingService
{
    /** Candidates kept per BOQ line. */
    private const MAX_CANDIDATES = 5;

    /** Below this score a candidate is not worth showing. */
    private const MIN_SCORE = 35.0;

    public function __construct(
        private readonly BoqSpecParser $parser,
    ) {}

    /**
     * Match one BOQ line and store its ranked candidates.
     *
     * @return int number of candidates stored
     */
    public function matchItem(BoqItem $item): int
    {
        // Headings, definitions and contract clauses are not products. Matching
        // them yields confident-looking nonsense, so they are skipped outright.
        if (! $this->parser->isProductLine(
            (string) $item->description,
            (float) $item->quantity,
            $item->unit_id !== null,
        )) {
            return 0;
        }

        $specs = $this->parser->parse((string) $item->description, $item->brand);

        $candidates = $this->findCandidates($specs);

        if ($candidates === []) {
            return 0;
        }

        // Best first, capped — a long tail of weak guesses helps nobody.
        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);
        $candidates = array_slice($candidates, 0, self::MAX_CANDIDATES);

        return DB::connection('catalog')->transaction(function () use ($item, $specs, $candidates) {
            $rank   = 1;
            $stored = 0;

            foreach ($candidates as $candidate) {
                /** @var ProductVariant $variant */
                $variant = $candidate['variant'];
                $method  = $candidate['method'];

                $price = $this->bestPriceFor($variant->id, (float) $item->quantity);

                // Only the top candidate may be auto-selected, and only when
                // the match rests on an identifier rather than inference.
                $autoSelect = $rank === 1
                    && $method->canAutoSelect()
                    && $candidate['score'] >= 90.0;

                BoqVariantMatch::updateOrCreate(
                    [
                        'boq_item_id'        => $item->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'boq_id'            => $item->boq_id,
                        'product_family_id' => $variant->product_family_id,
                        'manufacturer_id'   => $variant->manufacturer_id,
                        'match_method'      => $method,
                        'confidence_score'  => round($candidate['score'], 2),
                        'rank'              => $rank,
                        'status'            => $autoSelect ? MatchStatusEnum::AutoAccepted : MatchStatusEnum::Pending,
                        'is_selected'       => $autoSelect,
                        'parsed_specs'      => $specs,
                        'match_reasons'     => $candidate['reasons'],
                        'spec_conflicts'    => $candidate['conflicts'],
                        'price_id'          => $price?->id,
                        'unit_price'        => $price?->price,
                        'currency'          => $price?->currency,
                        'price_tier'        => $price?->price_tier?->value,
                        'price_source'      => $price?->source?->value,
                    ]
                );

                $rank++;
                $stored++;
            }

            return $stored;
        });
    }

    /**
     * Search the catalog for variants that could satisfy these specs.
     *
     * @param  array<string,mixed>  $specs
     * @return list<array{variant:ProductVariant, method:BoqMatchMethodEnum, score:float, reasons:array, conflicts:array}>
     */
    private function findCandidates(array $specs): array
    {
        $found = [];

        // --- Tier 1: the BOQ names an actual SKU -------------------------
        if (! empty($specs['sku'])) {
            $variants = ProductVariant::query()
                ->where(fn ($q) => $q
                    ->where('manufacturer_sku', $specs['sku'])
                    ->orWhere('manufacturer_part_number', $specs['sku']))
                ->limit(3)->get();

            foreach ($variants as $variant) {
                $found[] = [
                    'variant'   => $variant,
                    'method'    => BoqMatchMethodEnum::ExactSku,
                    'score'     => 96.0,
                    'reasons'   => ['matched_on' => 'sku', 'sku' => $specs['sku']],
                    'conflicts' => [],
                ];
            }
        }

        // --- Tier 2: brand + model both present --------------------------
        if ($found === [] && ! empty($specs['model'])) {
            $variants = ProductVariant::query()
                ->with('manufacturer:id,name')
                ->whereHas('model', fn ($q) => $q->where('model_number', 'like', $specs['model'] . '%'))
                ->limit(self::MAX_CANDIDATES)->get();

            foreach ($variants as $variant) {
                $score  = BoqMatchMethodEnum::BrandModel->baseConfidence();
                $reasons = ['matched_on' => 'model', 'model' => $specs['model']];

                // A matching brand raises confidence; a clashing one lowers it.
                if (! empty($specs['brand']) && $variant->manufacturer) {
                    $same = str_contains(
                        mb_strtolower($variant->manufacturer->name),
                        mb_strtolower($specs['brand'])
                    );
                    $score += $same ? 8 : -20;
                    $reasons['brand_match'] = $same;
                }

                $found[] = [
                    'variant'   => $variant,
                    'method'    => BoqMatchMethodEnum::BrandModel,
                    'score'     => $score,
                    'reasons'   => $reasons,
                    'conflicts' => [],
                ];
            }
        }

        // --- Tier 3: specification match ---------------------------------
        if ($found === []) {
            $found = $this->matchBySpecs($specs);
        }

        return array_values(array_filter($found, fn ($c) => $c['score'] >= self::MIN_SCORE));
    }

    /**
     * Score variants against the parsed specs.
     *
     * Deliberately additive: each spec that agrees adds confidence, each that
     * clashes subtracts and is recorded as a conflict, and a missing spec does
     * neither. That keeps a partially described BOQ line matchable without
     * pretending we know more than the text says.
     *
     * @param  array<string,mixed>  $specs
     * @return list<array{variant:ProductVariant, method:BoqMatchMethodEnum, score:float, reasons:array, conflicts:array}>
     */
    private function matchBySpecs(array $specs): array
    {
        $keywords = $specs['keywords'] ?? [];

        if ($keywords === []) {
            return [];
        }

        // Narrow by the strongest content words before scoring, so this stays
        // an indexed query rather than a scan of the whole catalog.
        $query = ProductVariant::query()->with(['manufacturer:id,name']);

        $query->where(function ($q) use ($keywords) {
            foreach (array_slice($keywords, 0, 4) as $word) {
                $q->orWhere('variant_name', 'like', '%' . $word . '%');
            }
        });

        if (! empty($specs['size'])) {
            // Size is the single most discriminating spec — a 2" valve is not
            // a substitute for a 4" one, so prefer rows that carry it.
            $query->where('normalized_variant_key', 'like', '%' . $specs['size'] . '%');
        }

        $variants = $query->limit(40)->get();
        $results  = [];

        foreach ($variants as $variant) {
            $score     = 30.0;
            $reasons   = [];
            $conflicts = [];
            $name      = mb_strtolower((string) $variant->variant_name);

            $hits = 0;
            foreach ($keywords as $word) {
                if (str_contains($name, $word)) {
                    $hits++;
                }
            }
            $score += min(25, $hits * 6);
            $reasons['keyword_hits'] = $hits;

            $key = mb_strtolower((string) $variant->normalized_variant_key);

            if (! empty($specs['size'])) {
                if (str_contains($key, (string) $specs['size'])) {
                    $score += 20;
                    $reasons['size'] = $specs['size'];
                } else {
                    $score -= 15;
                    $conflicts['size'] = ['boq' => $specs['size'], 'variant' => 'not found'];
                }
            }

            if (! empty($specs['connection'])) {
                if (str_contains($key, (string) $specs['connection'])) {
                    $score += 10;
                    $reasons['connection'] = $specs['connection'];
                }
            }

            if (! empty($specs['pressure'])) {
                if (str_contains($key, (string) $specs['pressure'])) {
                    $score += 8;
                    $reasons['pressure'] = $specs['pressure'];
                }
            }

            if (! empty($specs['brand']) && $variant->manufacturer) {
                if (str_contains(mb_strtolower($variant->manufacturer->name), mb_strtolower($specs['brand']))) {
                    $score += 12;
                    $reasons['brand'] = $variant->manufacturer->name;
                }
            }

            $results[] = [
                'variant'   => $variant,
                'method'    => $hits > 0 ? BoqMatchMethodEnum::SpecMatch : BoqMatchMethodEnum::FamilyOnly,
                'score'     => $score,
                'reasons'   => $reasons,
                'conflicts' => $conflicts,
            ];
        }

        return $results;
    }

    /**
     * The price to quote for this variant at this quantity.
     *
     * Prefers the cheapest quotable price whose quantity band covers the BOQ
     * quantity — estimates and list prices are excluded by the scope, so a
     * non-binding number never reaches a quotation.
     */
    private function bestPriceFor(int $variantId, float $quantity): ?ProductVariantPrice
    {
        $qty = max(1, (int) round($quantity));

        return ProductVariantPrice::query()
            ->where('product_variant_id', $variantId)
            ->quotable()
            ->forQuantity($qty)
            ->orderBy('price')
            ->first();
    }
}
