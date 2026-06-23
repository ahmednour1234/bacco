<?php

namespace App\Services\Catalog;

use App\Models\Boq;
use App\Models\QuotationRequest;
use App\Repositories\Catalog\CatalogCategoryRepository;
use App\Repositories\Catalog\CatalogProductRepository;
use App\Repositories\Catalog\CatalogRepository;
use Illuminate\Support\Str;

/**
 * Persists the selected items of a submitted quotation OR BOQ into the catalog
 * (catalog_products) so the system keeps a record of every product that has
 * appeared in a quotation or a BOQ.
 *
 * Items are written into a single, stable catalog named "Quotations".
 * Duplicates are avoided by CatalogProductRepository::bulkUpsert(), which
 * upserts on the unique key (qimta_code, product_name, size, unit).
 *
 * Note: catalog_products lives on the separate `catalog` DB connection, so
 * this runs independently of the quotation/BOQ save (no shared transaction).
 */
class SaveQuotationProductsToCatalog
{
    /** Fixed destination catalog for quotation/BOQ-sourced products. */
    private const CATALOG_NAME = 'Quotations';

    public function __construct(
        private CatalogRepository         $catalogRepo,
        private CatalogProductRepository  $productRepo,
        private CatalogCategoryRepository $categoryRepo,
    ) {}

    /**
     * Save a quotation's selected items into the catalog.
     *
     * @param  QuotationRequest  $quotation  the saved quotation (for metadata)
     * @param  array             $items      the quotation item rows (Livewire $items)
     * @return array{inserted:int, updated:int}
     */
    public function handle(QuotationRequest $quotation, array $items): array
    {
        return $this->saveItems($items, 'quotation', [
            'quotation_id' => $quotation->id,
            'quotation_no' => $quotation->quotation_no ?? null,
        ], 'Quotation ' . ($quotation->quotation_no ?? $quotation->id));
    }

    /**
     * Save a BOQ's selected items into the catalog.
     *
     * @param  Boq    $boq    the saved BOQ (for metadata)
     * @param  array  $items  the BOQ item rows (Livewire $items)
     * @return array{inserted:int, updated:int}
     */
    public function handleBoq(Boq $boq, array $items): array
    {
        return $this->saveItems($items, 'boq', [
            'boq_id'   => $boq->id,
            'boq_uuid' => $boq->uuid ?? null,
        ], 'BOQ ' . ($boq->uuid ?? $boq->id));
    }

    /**
     * Shared implementation: filter, map and upsert item rows.
     *
     * @param  array   $items       raw item rows
     * @param  string  $source      'quotation' | 'boq' (stored in raw_data)
     * @param  array   $sourceMeta  extra metadata stored in raw_data
     * @param  string  $sourceFile  label stored in the source_file column
     * @return array{inserted:int, updated:int}
     */
    private function saveItems(array $items, string $source, array $sourceMeta, string $sourceFile): array
    {
        // Only keep selected, non-rejected items. Different callers use either
        // 'is_selected' (Create* components) or 'selected' (Show* components).
        $items = array_filter($items, function ($row) {
            $selected = ! empty($row['is_selected']) || ! empty($row['selected']);
            $status   = $row['status'] ?? '';
            return $selected
                && $status !== \App\Enums\QuotationItemStatusEnum::Rejected->value;
        });

        if ($items === []) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $catalog       = $this->catalogRepo->firstOrCreate(self::CATALOG_NAME);
        $catalogId     = $catalog->id;
        $now           = now()->toDateTimeString();
        $categoryCache = [];
        $rows          = [];

        foreach ($items as $row) {
            $productName = trim((string) ($row['description'] ?? ''));

            // A product must have a name to be cataloged.
            if ($productName === '') {
                continue;
            }

            $categoryName = trim((string) ($row['category'] ?? ''));
            $categoryId   = null;
            if ($categoryName !== '') {
                $categoryId = $this->categoryRepo->resolveByName($catalogId, $categoryName, $categoryCache);
            }

            $unit = trim((string) ($row['unit'] ?? ''));
            $size = trim((string) ($row['size'] ?? ''));

            // Generate a code when the item has none. It is DERIVED from the
            // product identity (name + size + unit) so re-submitting the same
            // product upserts the existing row instead of creating a duplicate.
            $qimtaCode = trim((string) ($row['qimta_code'] ?? ''));
            if ($qimtaCode === '') {
                $qimtaCode = 'QTN-' . strtoupper(substr(
                    md5(mb_strtolower($productName . '|' . $size . '|' . $unit)),
                    0,
                    12
                ));
            }

            $rows[] = [
                'uuid'             => (string) Str::uuid(),
                'catalog_id'       => $catalogId,
                'category_id'      => $categoryId,
                'qimta_code'       => $qimtaCode,
                'division'         => null,
                'item_description' => $productName,
                'sub_type'         => null,
                'product_name'     => $productName,
                'type_of_material' => trim((string) ($row['brand'] ?? '')) ?: null,
                'size'             => $size,
                'unit'             => $unit,
                'lead_time'        => null,
                'source_file'      => $sourceFile,
                'import_batch_id'  => null,
                'status'           => 'active',
                'raw_data'         => json_encode(array_merge([
                    'source'     => $source,
                    'unit_price' => $row['unit_price'] ?? null,
                    'quantity'   => $row['quantity'] ?? null,
                ], $sourceMeta), JSON_UNESCAPED_UNICODE),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if ($rows === []) {
            return ['inserted' => 0, 'updated' => 0];
        }

        return $this->productRepo->bulkUpsert($rows);
    }
}
