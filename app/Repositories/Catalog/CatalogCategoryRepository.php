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

        $slug = Str::slug($name) ?: Str::uuid();

        $row = DB::connection('catalog')
            ->table('catalog_categories')
            ->where('catalog_id', $catalogId)
            ->where('name', $name)
            ->first();

        if ($row) {
            $cache[$key] = $row->id;
            return $row->id;
        }

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
