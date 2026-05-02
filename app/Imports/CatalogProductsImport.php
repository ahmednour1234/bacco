<?php

namespace App\Imports;

use App\Models\Catalog\CatalogImport;
use App\Repositories\Catalog\CatalogCategoryRepository;
use App\Repositories\Catalog\CatalogImportRepository;
use App\Repositories\Catalog\CatalogProductRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CatalogProductsImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    /** In-memory category cache: "catalogId|name" => category_id */
    private array $categoryCache = [];

    public function __construct(
        private CatalogImport             $catalogImport,
        private CatalogImportRepository   $importRepo,
        private CatalogProductRepository  $productRepo,
        private CatalogCategoryRepository $categoryRepo,
    ) {}

    // Row 4 is the heading row in the Excel file
    public function headingRow(): int { return 4; }

    // Read 1 000 rows at a time — never loads the full file into memory
    public function chunkSize(): int { return 1000; }

    /**
     * Called once per chunk.
     * Normalises column names, resolves categories, batch-upserts products.
     */
    public function collection(Collection $rows): void
    {
        $now        = now()->toDateTimeString();
        $products   = [];
        $failedRows = [];
        $rowOffset  = 5; // data starts at row 5

        foreach ($rows as $index => $row) {
            $rowNumber = $rowOffset + $index;
            try {
                $r = $this->normalizeRow($row->toArray());

                // Skip completely empty rows
                if (empty(array_filter($r))) {
                    continue;
                }

                $categoryName = $r['category'] ?? null;
                $categoryId   = null;

                if ($categoryName) {
                    $categoryId = $this->categoryRepo->resolveByName(
                        $this->catalogImport->catalog_id,
                        $categoryName,
                        $this->categoryCache
                    );
                }

                $qimtaCode   = trim($r['qimta_code'] ?? '');
                $productName = trim($r['product_name'] ?? '');
                $size        = trim($r['size'] ?? '');
                $unit        = trim($r['unit'] ?? '');

                $products[] = [
                    'uuid'             => (string) Str::uuid(),
                    'catalog_id'       => $this->catalogImport->catalog_id,
                    'category_id'      => $categoryId,
                    'qimta_code'       => $qimtaCode,
                    'division'         => trim($r['division'] ?? '') ?: null,
                    'item_description' => trim($r['item_description'] ?? '') ?: null,
                    'sub_type'         => trim($r['sub_type'] ?? '') ?: null,
                    'product_name'     => $productName,
                    'type_of_material' => trim($r['type_of_material'] ?? '') ?: null,
                    'size'             => $size,
                    'unit'             => $unit,
                    'lead_time'        => trim($r['lead_time'] ?? '') ?: null,
                    'source_file'      => $this->catalogImport->file_name,
                    'import_batch_id'  => $this->catalogImport->id,
                    'status'           => 'active',
                    'raw_data'         => json_encode($r),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

            } catch (\Throwable $e) {
                $failedRows[] = [
                    'catalog_import_id' => $this->catalogImport->id,
                    'row_number'        => $rowNumber,
                    'row_data'          => json_encode($row->toArray()),
                    'error_message'     => $e->getMessage(),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }

        // ── Bulk upsert products ────────────────────────────────────────────
        $result   = $this->productRepo->bulkUpsert($products);
        $inserted = $result['inserted'];
        $updated  = $result['updated'];

        // ── Save failed rows ────────────────────────────────────────────────
        $this->importRepo->saveFailedRows($this->catalogImport->id, $failedRows);

        // ── Update progress counters atomically ────────────────────────────
        $processed = count($products) + count($failedRows);
        $this->importRepo->incrementProgress(
            $this->catalogImport->id,
            $processed,
            $inserted,
            $updated,
            count($failedRows)
        );
    }

    /**
     * Normalise Excel heading keys to snake_case regardless of original format.
     * e.g. "Sub-Type" → "sub_type", "Qimta Code" → "qimta_code"
     */
    private function normalizeRow(array $raw): array
    {
        $normalized = [];
        foreach ($raw as $key => $value) {
            $cleanKey              = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim((string) $key)));
            $cleanKey              = trim($cleanKey, '_');
            $normalized[$cleanKey] = $value;
        }
        return $normalized;
    }
}
