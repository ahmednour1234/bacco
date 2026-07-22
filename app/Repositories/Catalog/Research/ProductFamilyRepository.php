<?php

namespace App\Repositories\Catalog\Research;

use App\Models\Catalog\Research\ProductFamily;
use Illuminate\Support\Str;

class ProductFamilyRepository
{
    /**
     * Find-or-create a family for an imported row. Keyed by (division, normalized
     * name) so the same generic product across rows/files collapses into one
     * family instead of duplicating.
     */
    public function resolveForImport(array $attrs): ProductFamily
    {
        $normalized = $attrs['normalized_name'];

        $family = ProductFamily::where('normalized_name', $normalized)
            ->when(
                isset($attrs['division_id']),
                fn ($q) => $q->where('division_id', $attrs['division_id'])
            )
            ->first();

        if ($family) {
            return $family;
        }

        return ProductFamily::create(array_merge($attrs, [
            'slug' => $this->uniqueSlug($attrs['name'] ?? $normalized),
        ]));
    }

    public function find(int $id): ProductFamily
    {
        return ProductFamily::findOrFail($id);
    }

    public function findByUuid(string $uuid): ProductFamily
    {
        return ProductFamily::where('uuid', $uuid)->firstOrFail();
    }

    public function paginate(int $perPage = 20, array $filters = [])
    {
        return ProductFamily::query()
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('source_code', 'like', "%{$s}%"))
            ->when($filters['division_id'] ?? null, fn ($q, $d) => $q->where('division_id', $d))
            ->when($filters['research_status'] ?? null, fn ($q, $st) => $q->where('research_status', $st))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'family';
        $slug = $base;
        $i    = 1;

        while (ProductFamily::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
