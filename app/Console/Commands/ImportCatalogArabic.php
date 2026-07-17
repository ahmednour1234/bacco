<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Imports Arabic catalog translations from the "QIMTA v10" workbook (or a CSV
 * export of it). Rows are matched to catalog_products by qimta_code + sub_type
 * (the qimta_code repeats across sub-types, so sub_type disambiguates the row).
 *
 * The workbook header is Arabic. We map each Arabic header to its DB column:
 *   رمز قيمتا        → qimta_code            (match key)
 *   القسم           → division_ar
 *   الفئة           → category_ar           (→ catalog_categories.name_ar)
 *   وصف المنتج      → item_description_ar
 *   النوع الفرعي    → sub_type  (EN match)  + sub_type_ar (AR value, if distinct)
 *   اسم المنتج      → product_name_ar
 *
 * The data rows begin a few rows down (title + count banner precede the header),
 * so we auto-detect the header row by locating the "رمز قيمتا" cell.
 *
 * Empty translated cells are skipped (never overwrite with blank). Idempotent.
 *
 * Usage:
 *   php artisan catalog:import-arabic storage/app/qimta-v10.xlsx
 *   php artisan catalog:import-arabic storage/app/qimta-v10.csv --dry-run
 *   php artisan catalog:import-arabic file.xlsx --sheet="File 4"
 */
class ImportCatalogArabic extends Command
{
    protected $signature = 'catalog:import-arabic
                            {file : Path to the .xlsx or .csv file (absolute or relative to project root)}
                            {--sheet= : Worksheet name to read (defaults to the active sheet)}
                            {--dry-run : Parse and report without writing any changes}';

    protected $description = 'Backfill Arabic catalog fields from the QIMTA workbook (xlsx/csv), keyed by qimta_code + sub_type.';

    /**
     * Header aliases → canonical field. Both the Arabic workbook headers and the
     * plain English column names are accepted, so either export style works.
     */
    private const HEADER_ALIASES = [
        'qimta_code'          => ['qimta_code', 'رمز قيمتا', 'رمز كيمتا', 'code'],
        'division_ar'         => ['division_ar', 'القسم'],
        'category_ar'         => ['category_ar', 'الفئة'],
        'item_description_ar' => ['item_description_ar', 'وصف المنتج', 'الوصف'],
        'sub_type'            => ['sub_type', 'النوع الفرعي'],   // English value → match key
        'sub_type_ar'         => ['sub_type_ar'],                 // optional distinct AR value
        'product_name_ar'     => ['product_name_ar', 'اسم المنتج'],
    ];

    /** Canonical field → catalog_products column to update. */
    private const PRODUCT_COLS = [
        'division_ar'         => 'division_ar',
        'item_description_ar' => 'item_description_ar',
        'product_name_ar'     => 'product_name_ar',
        'sub_type_ar'         => 'sub_type_ar',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $path = base_path($path);
        }
        if (! is_file($path)) {
            $this->error("File not found: {$this->argument('file')}");
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        try {
            $db = DB::connection('catalog');
            $db->getPdo();
        } catch (\Throwable $e) {
            $this->error('Catalog DB unavailable: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Only update columns that actually exist (spec columns need the July migration).
        $productCols = array_filter(
            self::PRODUCT_COLS,
            fn ($col) => Schema::connection('catalog')->hasColumn('catalog_products', $col)
        );

        [$rows, $err] = $this->readRows($path);
        if ($err !== null) {
            $this->error($err);
            return self::FAILURE;
        }
        if (empty($rows)) {
            $this->error('No data rows found (could not locate the "رمز قيمتا" / qimta_code header).');
            return self::FAILURE;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . 'Parsed ' . count($rows) . ' data rows. Matching by qimta_code + sub_type…');

        $stats = ['rows' => 0, 'blank_code' => 0, 'unmatched' => 0, 'products_updated' => 0, 'categories_updated' => 0];
        $catByCode = [];

        if (! $dryRun) {
            $db->beginTransaction();
        }

        try {
            foreach ($rows as $row) {
                $code = trim((string) ($row['qimta_code'] ?? ''));
                $stats['rows']++;
                if ($code === '') {
                    $stats['blank_code']++;
                    continue;
                }

                $subType = trim((string) ($row['sub_type'] ?? ''));

                // Build the product-row query: code always, sub_type when present.
                $q = $db->table('catalog_products')->where('qimta_code', $code);
                if ($subType !== '') {
                    $q->where('sub_type', $subType);
                }
                $matched = (clone $q)->count();
                if ($matched === 0) {
                    $stats['unmatched']++;
                    if ($stats['unmatched'] <= 30) {
                        $this->line("  <comment>no match:</comment> {$code}" . ($subType !== '' ? " / {$subType}" : ''));
                    }
                    continue;
                }

                // Non-empty translated cells only.
                $update = [];
                foreach ($productCols as $field => $dbCol) {
                    $val = trim((string) ($row[$field] ?? ''));
                    if ($val !== '') {
                        $update[$dbCol] = $val;
                    }
                }

                if (! empty($update)) {
                    $stats['products_updated'] += $dryRun ? $matched : (clone $q)->update($update);
                }

                $catAr = trim((string) ($row['category_ar'] ?? ''));
                if ($catAr !== '') {
                    $catByCode[$code] = $catAr; // last non-empty wins
                }
            }

            // Category names: resolve each code's category_id, set name_ar.
            foreach ($catByCode as $code => $catAr) {
                $catId = $db->table('catalog_products')->where('qimta_code', $code)->value('category_id');
                if (! $catId) {
                    continue;
                }
                $stats['categories_updated'] += $dryRun
                    ? 1
                    : $db->table('catalog_categories')->where('id', $catId)->update(['name_ar' => $catAr]);
            }

            if (! $dryRun) {
                $db->commit();
            }
        } catch (\Throwable $e) {
            if (! $dryRun) {
                $db->rollBack();
            }
            $this->error('Import failed, rolled back: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '') . 'Catalog Arabic import complete.');
        $this->table(['metric', 'count'], collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all());

        if ($stats['unmatched'] > 30) {
            $this->warn('… and ' . ($stats['unmatched'] - 30) . ' more unmatched rows not listed.');
        }

        return self::SUCCESS;
    }

    /**
     * Read the file (xlsx or csv), auto-detect the Arabic/English header row,
     * and return [rows, error]. Each row is an assoc array keyed by canonical
     * field names (see HEADER_ALIASES).
     */
    private function readRows(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        try {
            if ($ext === 'csv') {
                $grid = $this->readCsvGrid($path);
            } else {
                $grid = $this->readSpreadsheetGrid($path);
            }
        } catch (\Throwable $e) {
            return [[], 'Could not read file: ' . $e->getMessage()];
        }

        // Locate the header row: the first row containing a qimta_code alias.
        $headerIdx = null;
        $headerMap = [];
        foreach ($grid as $i => $cells) {
            $map = $this->matchHeader($cells);
            if (isset($map['qimta_code'])) {
                $headerIdx = $i;
                $headerMap = $map; // field => column index
                break;
            }
        }
        if ($headerIdx === null) {
            return [[], null]; // signals "header not found" to caller
        }

        $rows = [];
        foreach (array_slice($grid, $headerIdx + 1) as $cells) {
            if ($this->isEmptyRow($cells)) {
                continue;
            }
            $row = [];
            foreach ($headerMap as $field => $colIdx) {
                $row[$field] = isset($cells[$colIdx]) ? trim((string) $cells[$colIdx]) : '';
            }
            if (($row['qimta_code'] ?? '') !== '') {
                $rows[] = $row;
            }
        }

        return [$rows, null];
    }

    /** Map a header row's cells → [canonical field => column index]. */
    private function matchHeader(array $cells): array
    {
        $map = [];
        foreach ($cells as $idx => $cell) {
            $norm = $this->norm((string) $cell);
            if ($norm === '') {
                continue;
            }
            foreach (self::HEADER_ALIASES as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if ($this->norm($alias) === $norm) {
                        $map[$field] = $idx;
                        break 2;
                    }
                }
            }
        }
        return $map;
    }

    private function readSpreadsheetGrid(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $sheetName = $this->option('sheet');
        $sheet = $sheetName
            ? $spreadsheet->getSheetByName($sheetName)
            : $spreadsheet->getActiveSheet();

        if (! $sheet) {
            throw new \RuntimeException("Worksheet not found: {$sheetName}");
        }

        return $sheet->toArray(null, true, false, false);
    }

    private function readCsvGrid(string $path): array
    {
        $grid = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('cannot open CSV');
        }
        while (($cells = fgetcsv($handle)) !== false) {
            // Strip UTF-8 BOM from the first cell if present.
            if (!empty($cells) && isset($cells[0])) {
                $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cells[0]);
            }
            $grid[] = $cells;
        }
        fclose($handle);
        return $grid;
    }

    /** Normalise a header/label: strip BOM, collapse spaces, lower-case. */
    private function norm(string $s): string
    {
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s);
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return mb_strtolower($s);
    }

    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }
        return true;
    }
}
