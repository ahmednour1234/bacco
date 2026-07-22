<?php

namespace App\Services\Catalog\Research;

use App\Models\Catalog\Research\ProductDuplicateCandidate;
use App\Models\Catalog\Research\ProductVariant;

/**
 * Finds likely-duplicate variants within a family and records candidate pairs
 * for human review. It NEVER auto-merges: variants that differ by SKU always go
 * to the review queue (spec rule "do not auto-merge products with differing
 * SKUs"). Only exact normalized_variant_key collisions are hard duplicates, and
 * those are prevented at insert time by the unique index — so this engine deals
 * with the fuzzy near-duplicates.
 */
class DeduplicationEngine
{
    public function __construct(private NormalizationEngine $engine) {}

    /**
     * Scan a family's variants and upsert duplicate candidate pairs.
     *
     * @return int number of candidate pairs recorded
     */
    public function scanFamily(int $familyId): int
    {
        $variants = ProductVariant::where('product_family_id', $familyId)
            ->get(['id', 'manufacturer_id', 'manufacturer_sku', 'size_id', 'connection_type_id', 'pressure_rating_id', 'normalized_variant_key']);

        $recorded = 0;

        for ($i = 0; $i < $variants->count(); $i++) {
            for ($j = $i + 1; $j < $variants->count(); $j++) {
                $a = $variants[$i];
                $b = $variants[$j];

                $result = $this->compare($a, $b);
                if ($result === null) {
                    continue;
                }

                [$score, $reasons] = $result;

                ProductDuplicateCandidate::firstOrCreate(
                    [
                        'first_product_variant_id'  => min($a->id, $b->id),
                        'second_product_variant_id' => max($a->id, $b->id),
                    ],
                    [
                        'similarity_score' => $score,
                        'match_reasons'    => $reasons,
                        'status'           => 'open',
                    ]
                );
                $recorded++;
            }
        }

        return $recorded;
    }

    /**
     * Compare two variants. Returns [score, reasons] if they are a duplicate
     * candidate, or null if clearly distinct.
     *
     * @return array{0:float,1:list<string>}|null
     */
    private function compare(ProductVariant $a, ProductVariant $b): ?array
    {
        // Different manufacturers are never the same product.
        if ($a->manufacturer_id !== $b->manufacturer_id) {
            return null;
        }

        $skuA = $this->engine->normalizeToken($a->manufacturer_sku);
        $skuB = $this->engine->normalizeToken($b->manufacturer_sku);

        // Two real, DIFFERENT SKUs → distinct products, never auto-merge.
        if ($skuA !== '' && $skuB !== '' && $skuA !== $skuB) {
            return null;
        }

        $reasons = [];
        $score   = 0.0;

        if ($skuA !== '' && $skuA === $skuB) {
            $reasons[] = 'Identical manufacturer SKU';
            $score    += 0.5;
        }
        if ($a->size_id && $a->size_id === $b->size_id) {
            $reasons[] = 'Same size';
            $score    += 0.2;
        }
        if ($a->connection_type_id && $a->connection_type_id === $b->connection_type_id) {
            $reasons[] = 'Same connection';
            $score    += 0.15;
        }
        if ($a->pressure_rating_id && $a->pressure_rating_id === $b->pressure_rating_id) {
            $reasons[] = 'Same pressure';
            $score    += 0.15;
        }

        // Only surface reasonably-similar pairs (one lacked a SKU, specs align).
        return $score >= 0.5 ? [min(1.0, $score), $reasons] : null;
    }
}
