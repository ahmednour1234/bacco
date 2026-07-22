<?php

namespace App\Repositories\Catalog\Research;

use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query layer for the product catalog (variants) with the full filter set from
 * the spec. No pricing columns exist, so none can be filtered/returned.
 */
class ProductVariantRepository
{
    /** @param array<string,mixed> $filters */
    public function filtered(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->latest('product_variants.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string,mixed> $filters */
    public function baseQuery(array $filters): Builder
    {
        return ProductVariant::query()
            ->with(['manufacturer', 'model.series', 'family', 'size', 'connectionType', 'pressureRating', 'approvals'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('manufacturer_sku', 'like', "%{$s}%")
                  ->orWhere('variant_name', 'like', "%{$s}%")
                  ->orWhere('manufacturer_part_number', 'like', "%{$s}%");
            }))
            ->when($filters['family_id'] ?? null, fn ($q, $v) => $q->where('product_family_id', $v))
            ->when($filters['manufacturer_id'] ?? null, fn ($q, $v) => $q->where('manufacturer_id', $v))
            ->when($filters['model_id'] ?? null, fn ($q, $v) => $q->where('product_model_id', $v))
            ->when($filters['manufacturer_sku'] ?? null, fn ($q, $v) => $q->where('manufacturer_sku', 'like', "%{$v}%"))
            ->when($filters['size_id'] ?? null, fn ($q, $v) => $q->where('size_id', $v))
            ->when($filters['connection_type_id'] ?? null, fn ($q, $v) => $q->where('connection_type_id', $v))
            ->when($filters['pressure_rating_id'] ?? null, fn ($q, $v) => $q->where('pressure_rating_id', $v))
            ->when($filters['verification_level'] ?? null, fn ($q, $v) => $q->where('verification_level', $v))
            ->when($filters['verification_status'] ?? null, fn ($q, $v) => $q->where('verification_status', $v))
            ->when($filters['availability_status'] ?? null, fn ($q, $v) => $q->where('availability_status', $v))
            ->when($filters['market_scope'] ?? null, fn ($q, $v) => $q->where('market_scope', $v))
            ->when(isset($filters['approval_id']), fn ($q) => $q->whereHas('approvals', fn ($q) =>
                $q->where('approvals.id', $filters['approval_id'])))
            ->when(isset($filters['fire_protection']) && $filters['fire_protection'], fn ($q) =>
                // Fire-protection suitability = has a UL/FM/LPCB sprinkler-scope approval.
                $q->whereHas('approvals', fn ($q) => $q->whereIn('issuing_body', ['UL', 'FM', 'LPCB'])));
    }

    public function findByUuid(string $uuid): ProductVariant
    {
        return ProductVariant::where('uuid', $uuid)->firstOrFail();
    }
}
