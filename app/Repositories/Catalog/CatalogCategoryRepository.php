<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\CatalogCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogCategoryRepository
{
    /**
     * Resolve a category by name within a catalog.
     * Uses an in-memory cache array to minimize repeated DB hits inside loops.
     */
    public function resolveByName(int $catalogId, string $name, array &$cache): int
    {
        $key = $catalogId . '|' . strtolower(trim($name));

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $row = DB::connection('catalog')
            ->table('catalog_categories')
            ->where('catalog_id', $catalogId)
            ->where('name', $name)
            ->first();

        if ($row) {
            $cache[$key] = $row->id;
            return $row->id;
        }

        $slug = $this->uniqueSlug($catalogId, $name, $name, '');

        $id = DB::connection('catalog')->table('catalog_categories')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'catalog_id' => $catalogId,
            'name'       => $name,
            'slug'       => $slug,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cache[$key] = $id;
        return $id;
    }

    /**
     * Resolve a bilingual category within a catalog, storing BOTH the English
     * (name) and Arabic (name_ar) labels on a single row.
     *
     * Matching priority (so the same concept is one row regardless of which
     * language the file used):
     *   1. existing row whose English name matches $nameEn, OR
     *   2. existing row whose Arabic name matches $nameAr.
     * When found, any missing language on that row is back-filled.
     * Otherwise a new row is created with whatever languages are available.
     */
    public function resolveBilingual(int $catalogId, string $nameEn, string $nameAr, array &$cache): int
    {
        $nameEn = trim($nameEn);
        $nameAr = trim($nameAr);

        $cacheKey = $catalogId . '|' . strtolower($nameEn) . '|' . $nameAr;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $query = DB::connection('catalog')->table('catalog_categories')->where('catalog_id', $catalogId);
        $query->where(function ($q) use ($nameEn, $nameAr) {
            if ($nameEn !== '') {
                $q->orWhere('name', $nameEn);
            }
            if ($nameAr !== '') {
                $q->orWhere('name_ar', $nameAr);
            }
        });
        $row = $query->first();

        if ($row) {
            // Back-fill a missing language if this upload provides it.
            $updates = [];
            if ($nameEn !== '' && (empty($row->name) || $row->name !== $nameEn)) {
                // only fill when the stored name looks empty/placeholder
                if (empty($row->name)) {
                    $updates['name'] = $nameEn;
                }
            }
            if ($nameAr !== '' && empty($row->name_ar)) {
                $updates['name_ar'] = $nameAr;
            }
            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::connection('catalog')->table('catalog_categories')->where('id', $row->id)->update($updates);
            }

            $cache[$cacheKey] = $row->id;
            return $row->id;
        }

        // Create a new bilingual category. `name` is NOT NULL, so fall back to
        // the Arabic label when no English name was provided.
        $name = $nameEn !== '' ? $nameEn : $nameAr;
        $slug = $this->uniqueSlug($catalogId, $name, $nameEn, $nameAr);

        $id = DB::connection('catalog')->table('catalog_categories')->insertGetId([
            'uuid'       => (string) Str::uuid(),
            'catalog_id' => $catalogId,
            'name'       => $name,
            'name_ar'    => $nameAr ?: null,
            'slug'       => $slug,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cache[$cacheKey] = $id;
        return $id;
    }

    /**
     * Build a slug that is unique within the catalog.
     *
     * Str::slug() transliterates Arabic poorly — many distinct Arabic names
     * collapse to the same (or an empty) slug, which violates the
     * (catalog_id, slug) unique index. We therefore base the slug on the full
     * bilingual name and append a short deterministic hash, then ensure no
     * existing row already uses it.
     */
    private function uniqueSlug(int $catalogId, string $name, string $nameEn, string $nameAr): string
    {
        $base = Str::slug($name);

        // Deterministic suffix from BOTH languages so the same concept always
        // maps to the same slug (idempotent re-imports), while different names
        // get different slugs even when transliteration collides.
        $hash = substr(md5(mb_strtolower($nameEn . '|' . $nameAr)), 0, 8);

        $slug = $base !== '' ? ($base . '-' . $hash) : ('cat-' . $hash);

        // Extremely unlikely, but guarantee uniqueness against existing rows.
        $candidate = $slug;
        $i = 1;
        while (
            DB::connection('catalog')->table('catalog_categories')
                ->where('catalog_id', $catalogId)
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    public function forCatalog(int $catalogId)
    {
        return CatalogCategory::where('catalog_id', $catalogId)
            ->orderBy('name')
            ->get();
    }

    public function paginate(int $catalogId, int $perPage = 30)
    {
        return CatalogCategory::where('catalog_id', $catalogId)
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
