<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\CatalogProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CatalogProductRepository
{
    /**
     * Bulk upsert a batch of products into the catalog_products table.
     * Uses MySQL INSERT ... ON DUPLICATE KEY UPDATE for maximum throughput.
     * The unique key is (qimta_code, product_name, size, unit).
     */
    public function bulkUpsert(array $rows): array
    {
        if (empty($rows)) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $countBefore = DB::connection('catalog')
            ->table('catalog_products')
            ->count();

        DB::connection('catalog')->table('catalog_products')->upsert(
            $rows,
            ['qimta_code', 'product_name', 'size', 'unit'],     // conflict columns (unique key)
            [                                                     // columns to update on conflict
                'catalog_id', 'category_id', 'division',
                'item_description', 'sub_type', 'type_of_material',
                'lead_time', 'source_file', 'import_batch_id',
                'raw_data', 'updated_at',
            ]
        );

        $countAfter = DB::connection('catalog')
            ->table('catalog_products')
            ->count();

        $inserted = max(0, $countAfter - $countBefore);
        $updated  = count($rows) - $inserted;

        return ['inserted' => $inserted, 'updated' => max(0, $updated)];
    }

    /**
     * Filter + paginate products.
     */
    public function filter(array $filters, int $perPage = 30): LengthAwarePaginator
    {
        $q = CatalogProduct::with('category')
            ->when($filters['catalog_id'] ?? null, fn($q, $v) => $q->where('catalog_id', $v))
            ->when($filters['category_id'] ?? null, fn($q, $v) => $q->where('category_id', $v))
            ->when($filters['division'] ?? null, fn($q, $v) => $q->where('division', $v))
            ->when($filters['sub_type'] ?? null, fn($q, $v) => $q->where('sub_type', $v))
            ->when($filters['unit'] ?? null, fn($q, $v) => $q->where('unit', $v))
            ->when($filters['type_of_material'] ?? null, fn($q, $v) => $q->where('type_of_material', $v))
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where(function ($q2) use ($s) {
                    $q2->where('product_name', 'like', "%{$s}%")
                       ->orWhere('item_description', 'like', "%{$s}%")
                       ->orWhere('qimta_code', 'like', "%{$s}%");
                });
            });

        return $q->orderBy('qimta_code')->paginate($perPage)->withQueryString();
    }

    /**
     * Distinct filter values for dropdowns.
     */
    public function distinctValues(string $column, ?int $catalogId = null): array
    {
        return CatalogProduct::when($catalogId, fn($q) => $q->where('catalog_id', $catalogId))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->toArray();
    }
}
