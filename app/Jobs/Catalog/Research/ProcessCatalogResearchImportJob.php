<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ImportRowStatusEnum;
use App\Models\Catalog\Research\CatalogImport;
use App\Repositories\Catalog\Research\CatalogImportRepository;
use App\Repositories\Catalog\Research\CatalogImportRowRepository;
use App\Repositories\Catalog\Research\LookupRepository;
use App\Repositories\Catalog\Research\ProductFamilyRepository;
use App\Services\Catalog\Research\ExcelReaderService;
use App\Services\Catalog\Research\NormalizationEngine;
use App\Services\Catalog\Research\RowNormalizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Reads a research import file row-by-row and turns each row into:
 *   - one raw CatalogImportRow (original values preserved, hashed for dedup)
 *   - one Product Family (find-or-create; NOT variants — those come from research)
 *   - manufacturer links on that family
 *
 * No product variants and no cartesian combinations are ever created here.
 * Progress is written per chunk so partial work survives a crash.
 */
class ProcessCatalogResearchImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Large workbooks may take a while; never retry (partial progress kept). */
    public int $timeout = 7200;
    public int $tries   = 1;

    /** Rows buffered before a bulk insert. */
    private const CHUNK = 500;

    public function __construct(private int $importId) {}

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessCatalogResearchImportJob died.', [
            'import_id' => $this->importId,
            'message'   => $e->getMessage(),
        ]);

        try {
            $repo   = app(CatalogImportRepository::class);
            $import = $repo->find($this->importId);
            if ($import->status->value === 'processing') {
                $repo->markFailed($import);
            }
        } catch (\Throwable $inner) {
            Log::error('Could not mark research import failed.', ['message' => $inner->getMessage()]);
        }
    }

    public function handle(
        CatalogImportRepository    $importRepo,
        CatalogImportRowRepository $rowRepo,
        ProductFamilyRepository    $familyRepo,
        LookupRepository           $lookups,
        ExcelReaderService         $reader,
        RowNormalizationService    $normalizer,
        NormalizationEngine        $engine,
    ): void {
        $import = $importRepo->find($this->importId);

        if (in_array($import->status->value, ['processing', 'completed'], true)) {
            return;
        }

        $mappingConfig = $import->column_mapping ?? [];
        $sheet         = $mappingConfig['sheet'] ?? null;
        $headerRow     = (int) ($mappingConfig['header_row'] ?? 1);
        $map           = $mappingConfig['map'] ?? [];

        if (! $sheet || empty($map)) {
            Log::warning('Research import has no usable mapping.', ['import_id' => $import->id]);
            $importRepo->markFailed($import);

            return;
        }

        $importRepo->markProcessing($import);

        // Resolve the stored file across the disk root and the Laravel 11+
        // private path, so a mismatch in filesystem config never silently
        // yields a zero-row "Failed" import.
        $absPath = $this->resolveFilePath($import->stored_file_path);
        if ($absPath === null) {
            Log::error('Research import file not found.', [
                'import_id' => $import->id,
                'stored'    => $import->stored_file_path,
            ]);
            $importRepo->markFailed($import);

            return;
        }

        $seenHashes = $rowRepo->existingHashes($import->id);
        $buffer     = [];
        $total = $imported = $duplicate = $failed = 0;

        $flush = function () use (&$buffer, $rowRepo, $import, &$imported, &$duplicate, &$failed, $importRepo) {
            if ($buffer === []) {
                return;
            }
            $rowRepo->insertMany($buffer);
            $importRepo->incrementCounters($import->id, $imported, $duplicate, $failed);
            $imported = $duplicate = $failed = 0;
            $buffer   = [];
        };

        try {
            $reader->eachRow($absPath, $sheet, $headerRow, function (array $rawRow, int $excelRow) use (
            &$buffer, &$total, &$imported, &$duplicate, &$failed, &$seenHashes,
            $import, $normalizer, $engine, $familyRepo, $lookups, $map, $flush
        ) {
            $total++;

            try {
                $hash = $engine->rowHash($rawRow);

                // Duplicate within this file → record as duplicate, no family.
                // Store the raw row (never discarded) under a row-unique hash so
                // the (import_id, row_hash) unique index still holds; the true
                // hash lives in normalized_row for traceability.
                if (isset($seenHashes[$hash])) {
                    $buffer[] = $this->buildRow($import->id, $excelRow, $import->column_mapping['sheet'] ?? null, $rawRow, $hash . '-dup' . $excelRow, ImportRowStatusEnum::Duplicate->value, ['normalized' => ['duplicate_of_hash' => $hash], 'fields' => []], null);
                    $duplicate++;
                    return;
                }
                $seenHashes[$hash] = true;

                $parsed = $normalizer->normalize($rawRow, $map);

                // Missing item description → keep the raw row, flag it, no family.
                if ($parsed['item_description'] === '') {
                    $buffer[] = $this->buildRow($import->id, $excelRow, $import->column_mapping['sheet'] ?? null, $rawRow, $hash, ImportRowStatusEnum::MissingDescription->value, $parsed, null);
                    $failed++;
                    return;
                }

                $familyId = $this->makeFamily($parsed, $import, $familyRepo, $lookups);

                $status = empty($parsed['manufacturers'])
                    ? ImportRowStatusEnum::RequiresReview->value  // no manufacturers to research yet
                    : ImportRowStatusEnum::ReadyForResearch->value;

                $buffer[] = $this->buildRow($import->id, $excelRow, $import->column_mapping['sheet'] ?? null, $rawRow, $hash, $status, $parsed, $familyId);
                $imported++;
            } catch (\Throwable $e) {
                $buffer[] = $this->buildRow($import->id, $excelRow, $import->column_mapping['sheet'] ?? null, $rawRow, $engine->rowHash($rawRow) . '-err' . $excelRow, ImportRowStatusEnum::Failed->value, null, null, $e->getMessage());
                $failed++;
            }

            if (count($buffer) >= self::CHUNK) {
                $flush();
            }
            });

            $flush();
        } catch (\Throwable $e) {
            // Persist whatever rows were buffered, then fail cleanly with a log —
            // never let a read error kill the worker with the import stuck.
            $flush();
            Log::error('Research import processing failed mid-read.', [
                'import_id' => $import->id,
                'sheet'     => $sheet,
                'message'   => $e->getMessage(),
            ]);
            $importRepo->update($import, ['total_rows' => $total]);
            $importRepo->markFailed($import);

            return;
        }

        $importRepo->update($import, ['total_rows' => $total]);
        $importRepo->markCompleted($import, partial: false);
    }

    /**
     * Resolve the stored file to an absolute path, tolerating differences in the
     * configured disk root and the Laravel 11+ private storage location.
     */
    private function resolveFilePath(string $stored): ?string
    {
        $disk = config('catalog_research.storage.disk', 'local');

        $candidates = [
            Storage::disk($disk)->path($stored),
            storage_path('app/' . $stored),
            storage_path('app/private/' . $stored),
            storage_path('app/public/' . $stored),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Find-or-create the Product Family for a parsed row and attach its
     * manufacturers. Wrapped in a transaction for consistency. Never creates
     * variants.
     */
    private function makeFamily(
        array $parsed,
        CatalogImport $import,
        ProductFamilyRepository $familyRepo,
        LookupRepository $lookups,
    ): int {
        return DB::connection('catalog')->transaction(function () use ($parsed, $import, $familyRepo, $lookups) {
            $fields = $parsed['fields'];

            $divisionId = null;
            if (! empty($fields['division'])) {
                $divisionId = $lookups->division($fields['division'])->id;
            }

            $categoryId = null;
            if ($divisionId && ! empty($fields['category'])) {
                $categoryId = $lookups->category($divisionId, $fields['category'])->id;
            }

            $unitId = ! empty($fields['unit']) ? optional($lookups->unit($fields['unit']))->id : null;

            $family = $familyRepo->resolveForImport([
                'source_code'     => $fields['qimta_code'] ?? null,
                'division_id'     => $divisionId,
                'category_id'     => $categoryId,
                'name'            => $parsed['item_description'],
                'normalized_name' => $parsed['normalized']['normalized_name'],
                'default_unit_id' => $unitId,
                'created_by'      => $import->uploaded_by,
            ]);

            // Link manufacturers (multi-value → pivot rows, tagged by region).
            foreach ($parsed['manufacturers'] as $mfg) {
                $manufacturer = $lookups->manufacturer($mfg['name'], $mfg['type']);
                if (! $manufacturer) {
                    continue;
                }
                $family->manufacturers()->syncWithoutDetaching([
                    $manufacturer->id => ['source_type' => 'imported_from_excel'],
                ]);
            }

            return $family->id;
        });
    }

    /** Build a raw import-row array for bulk insert. */
    private function buildRow(
        int $importId,
        int $excelRow,
        ?string $sheet,
        array $rawRow,
        string $hash,
        string $status,
        ?array $parsed,
        ?int $familyId,
        ?string $error = null,
    ): array {
        $fields = $parsed['fields'] ?? [];

        return [
            'catalog_import_id'    => $importId,
            'sheet_name'           => $sheet,
            'excel_row_number'     => $excelRow,
            'source_code'          => $fields['qimta_code'] ?? null,
            'division_raw'         => $fields['division'] ?? null,
            'category_raw'         => $fields['category'] ?? null,
            'item_description_raw' => $fields['item_description'] ?? null,
            'material_raw'         => $fields['type_of_material'] ?? null,
            'manufacturer_raw'     => $this->joinManufacturerRaw($parsed),
            'connection_raw'       => $fields['connection_type'] ?? null,
            'size_raw'             => $fields['size'] ?? null,
            'pressure_raw'         => $fields['pressure_rating'] ?? null,
            'standard_raw'         => $fields['standard_code'] ?? null,
            'approval_raw'         => $fields['approval'] ?? null,
            'unit_raw'             => $fields['unit'] ?? null,
            'original_row'         => json_encode($rawRow, JSON_UNESCAPED_UNICODE),
            'normalized_row'       => $parsed ? json_encode($parsed['normalized'], JSON_UNESCAPED_UNICODE) : null,
            'row_hash'             => $hash,
            'import_status'        => $status,
            'error_message'        => $error,
            'product_family_id'    => $familyId,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }

    private function joinManufacturerRaw(?array $parsed): ?string
    {
        if (! $parsed || empty($parsed['manufacturers'])) {
            return null;
        }

        return implode(', ', array_map(fn ($m) => $m['name'], $parsed['manufacturers']));
    }
}
