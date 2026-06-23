<?php

namespace App\Services\Catalog;

use App\Models\QuotationRequest;
use App\Repositories\Catalog\CatalogCategoryRepository;
use App\Repositories\Catalog\CatalogProductRepository;
use App\Repositories\Catalog\CatalogRepository;
use Illuminate\Support\Str;

/**
 * Persists the selected items of a submitted quotation into the catalog
 * (catalog_products) so the system keeps a record of every product that has
 * appeared in a quotation.
 *
 * Items are written into a single, stable catalog named "Quotations".
 * Duplicates are avoided by CatalogProductRepository::bulkUpsert(), which
 * upserts on the unique key (qimta_code, product_name, size, unit).
 *
 * Note: catalog_products lives on the separate `catalog` DB connection, so
 * this runs independently of the quotation save (no shared transaction).
 */
class SaveQuotationProductsToCatalog
{
    /** Fixed destination catalog for quotation-sourced products. */
    private const CATALOG_NAME = 'Quotations';

    public function __construct(
        private CatalogRepository         $catalogRepo,
        private CatalogProductRepository  $productRepo,
        private CatalogCategoryRepository $categoryRepo,
    ) {}

    /**
     * @param  QuotationRequest  $quotation  the saved quotation (for metadata)
     * @param  array             $items      the quotation item rows (Livewire $items)
     * @return array{inserted:int, updated:int}
     */
    public function handle(QuotationRequest $quotation, array $items): array
    {
        // Only keep selected, non-rejected items. Different callers use either
        // 'is_selected' (CreateQuotation) or 'selected' (ShowQuotation).
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
                'source_file'      => 'Quotation ' . ($quotation->quotation_no ?? $quotation->id),
                'import_batch_id'  => null,
                'status'           => 'active',
                'raw_data'         => json_encode([
                    'source'              => 'quotation',
                    'quotation_id'        => $quotation->id,
                    'quotation_no'        => $quotation->quotation_no ?? null,
                    'unit_price'          => $row['unit_price'] ?? null,
                    'quantity'            => $row['quantity'] ?? null,
                ], JSON_UNESCAPED_UNICODE),
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
