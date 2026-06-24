<?php

namespace App\Imports;

use App\Models\Catalog\CatalogImport;
use App\Repositories\Catalog\CatalogCategoryRepository;
use App\Repositories\Catalog\CatalogImportRepository;
use App\Repositories\Catalog\CatalogProductRepository;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Streams a catalog Excel/CSV file row-by-row using PhpSpreadsheet directly
 * (no maatwebsite/excel dependency) and batch-upserts products in chunks so
 * very large files never load fully into memory.
 *
 * Heading row is row 4, data starts on row 5. Column names are matched by
 * header TEXT (English or Arabic), not by fixed column index.
 */
class CatalogProductsImport
{
    /** Fallback heading row (1-based) if auto-detection fails. */
    private const HEADING_ROW = 4;

    /** How many leading rows to scan when auto-detecting the heading row. */
    private const HEADING_SCAN_ROWS = 12;

    /** Rows upserted per batch. */
    private const CHUNK_SIZE = 1000;

    /** In-memory category cache: "catalogId|name" => category_id */
    private array $categoryCache = [];

    /**
     * Arabic (and alternative) header labels mapped to the canonical
     * snake_case field name used throughout the import.
     * Keys are trimmed exactly as they appear in the Excel heading cell.
     */
    private const HEADER_ALIASES = [
        // qimta_code
        'كود قمتا'            => 'qimta_code',
        'كود قمته'            => 'qimta_code',
        'رمز قيمتا'           => 'qimta_code',
        'رمز قيمته'           => 'qimta_code',
        'رمز قمتا'            => 'qimta_code',
        'رمز المنتج'          => 'qimta_code',
        'الكود'               => 'qimta_code',
        'الرمز'               => 'qimta_code',
        'كود المنتج'          => 'qimta_code',
        // division
        'القسم'               => 'division',
        'الشعبة'              => 'division',
        // category
        'الفئة'               => 'category',
        'التصنيف'             => 'category',
        'الصنف'               => 'category',
        // item_description
        'وصف الصنف'           => 'item_description',
        'وصف المنتج'          => 'item_description',
        'الوصف'               => 'item_description',
        // sub_type
        'النوع الفرعي'        => 'sub_type',
        'النوع الفرعى'        => 'sub_type',
        // product_name
        'اسم المنتج'          => 'product_name',
        'إسم المنتج'          => 'product_name',
        // type_of_material
        'نوع المادة'          => 'type_of_material',
        'نوع الخامة'          => 'type_of_material',
        // size
        'الحجم'               => 'size',
        'المقاس'              => 'size',
        // unit
        'الوحدة'              => 'unit',
        'وحدة القياس'         => 'unit',
        // lead_time
        'مدة التوريد'         => 'lead_time',
        'مدة التسليم'         => 'lead_time',
        'وقت التوريد'         => 'lead_time',
    ];

    public function __construct(
        private CatalogImport             $catalogImport,
        private CatalogImportRepository   $importRepo,
        private CatalogProductRepository  $productRepo,
        private CatalogCategoryRepository $categoryRepo,
    ) {}

    /**
     * Read the file and import it. Loads the spreadsheet once, then walks the
     * rows in CHUNK_SIZE batches, upserting each batch before moving on.
     */
    public function import(string $filePath): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        // For CSVs PhpSpreadsheet defaults to comma + UTF-8; keep Arabic intact.
        if ($reader instanceof CsvReader) {
            $reader->setInputEncoding(CsvReader::GUESS_ENCODING);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $highestRow  = $sheet->getHighestDataRow();
        $highestCol  = $sheet->getHighestDataColumn();

        // Auto-detect which row holds the column headers (different templates put
        // it on row 3 or row 4), then build the header map from it.
        [$headingRow, $headers] = $this->detectHeaders($sheet, $highestCol);
        $firstDataRow = $headingRow + 1;

        // Walk data rows in chunks so memory stays flat.
        $batch = [];
        for ($row = $firstDataRow; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray(
                "A{$row}:{$highestCol}{$row}",
                null,
                false,
                false,
                false
            )[0] ?? [];

            $batch[] = ['row' => $row, 'cells' => $cells];

            if (count($batch) >= self::CHUNK_SIZE) {
                $this->processChunk($batch, $headers);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->processChunk($batch, $headers);
        }

        // Free memory.
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * Normalise, resolve categories and batch-upsert one chunk of rows.
     *
     * @param  array<int, array{row:int, cells:array}>  $rows
     * @param  array<int, string>                       $headers  colIndex => field
     */
    private function processChunk(array $rows, array $headers): void
    {
        $now        = now()->toDateTimeString();
        $products   = [];
        $failedRows = [];

        foreach ($rows as $entry) {
            $rowNumber = $entry['row'];
            try {
                $r = $this->mapRow($entry['cells'], $headers);

                // Skip completely empty rows
                if (empty(array_filter($r, fn($v) => trim((string) $v) !== ''))) {
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
                    'raw_data'         => json_encode($r, JSON_UNESCAPED_UNICODE),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

            } catch (\Throwable $e) {
                $failedRows[] = [
                    'catalog_import_id' => $this->catalogImport->id,
                    'row_number'        => $rowNumber,
                    'row_data'          => json_encode($entry['cells'], JSON_UNESCAPED_UNICODE),
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
     * Scan the first few rows and pick the one that looks most like a header
     * row (the most cells resolving to known fields). Returns [rowNumber, map]
     * where map is colIndex => canonical field name.
     *
     * Falls back to HEADING_ROW when nothing recognisable is found.
     *
     * @return array{0:int, 1:array<int,string>}
     */
    private function detectHeaders($sheet, string $highestCol): array
    {
        $bestRow     = self::HEADING_ROW;
        $bestMap     = [];
        $bestScore   = 0;

        for ($row = 1; $row <= self::HEADING_SCAN_ROWS; $row++) {
            $cells = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}", null, false, false, false)[0] ?? [];

            $map = [];
            foreach ($cells as $colIndex => $headerCell) {
                $field = $this->resolveHeader((string) $headerCell);
                if ($field !== null) {
                    $map[$colIndex] = $field;
                }
            }

            // Score = number of DISTINCT recognised fields on this row.
            $score = count(array_unique($map));

            // A real header row maps at least the core identifying columns.
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow   = $row;
                $bestMap   = $map;
            }
        }

        // Need at least 2 recognised columns to trust auto-detection; otherwise
        // fall back to the default heading row with whatever it resolves.
        if ($bestScore < 2) {
            $cells = $sheet->rangeToArray(
                "A" . self::HEADING_ROW . ":{$highestCol}" . self::HEADING_ROW,
                null, false, false, false
            )[0] ?? [];
            $bestMap = [];
            foreach ($cells as $colIndex => $headerCell) {
                $field = $this->resolveHeader((string) $headerCell);
                if ($field !== null) {
                    $bestMap[$colIndex] = $field;
                }
            }
            $bestRow = self::HEADING_ROW;
        }

        return [$bestRow, $bestMap];
    }

    /**
     * Build an associative [field => value] row from the raw cell array using
     * the column header map.
     *
     * @param  array               $cells    raw cell values for the row
     * @param  array<int, string>  $headers  colIndex => canonical field name
     */
    private function mapRow(array $cells, array $headers): array
    {
        $mapped = [];
        foreach ($headers as $colIndex => $field) {
            $mapped[$field] = isset($cells[$colIndex]) ? (string) $cells[$colIndex] : '';
        }
        return $mapped;
    }

    /**
     * Resolve a raw header cell to a canonical snake_case field name.
     * Arabic / alternative headers map via HEADER_ALIASES; everything else is
     * normalised (lowercased, non-alphanumerics → underscore). Returns null
     * for headers that don't resolve to a usable field name.
     */
    private function resolveHeader(string $rawKey): ?string
    {
        $rawKey = trim($rawKey);

        if ($rawKey === '') {
            return null;
        }

        if (isset(self::HEADER_ALIASES[$rawKey])) {
            return self::HEADER_ALIASES[$rawKey];
        }

        $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $rawKey));
        $cleanKey = trim($cleanKey, '_');

        return $cleanKey !== '' ? $cleanKey : null;
    }
}
