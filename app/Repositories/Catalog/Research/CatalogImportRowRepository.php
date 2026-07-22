<?php

namespace App\Repositories\Catalog\Research;

use App\Models\Catalog\Research\CatalogImportRow;
use Illuminate\Support\Facades\DB;

class CatalogImportRowRepository
{
    /** Bulk insert already-built row arrays on the catalog connection. */
    public function insertMany(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        DB::connection('catalog')->table('catalog_import_rows')->insert($rows);
    }

    /**
     * Row hashes already stored for this import — used to skip duplicates
     * (the unique (catalog_import_id, row_hash) index is the hard guard, this
     * is the cheap pre-check).
     *
     * @return array<string,true>
     */
    public function existingHashes(int $importId): array
    {
        return DB::connection('catalog')->table('catalog_import_rows')
            ->where('catalog_import_id', $importId)
            ->pluck('row_hash')
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    public function paginateForImport(int $importId, int $perPage = 50, ?string $status = null)
    {
        return CatalogImportRow::where('catalog_import_id', $importId)
            ->when($status, fn ($q) => $q->where('import_status', $status))
            ->orderBy('excel_row_number')
            ->paginate($perPage);
    }

    /**
     * Per-status counts for the import report.
     *
     * @return array<string,int>
     */
    public function statusCounts(int $importId): array
    {
        return CatalogImportRow::where('catalog_import_id', $importId)
            ->selectRaw('import_status, COUNT(*) as aggregate')
            ->groupBy('import_status')
            ->pluck('aggregate', 'import_status')
            ->all();
    }

    public function readyForResearch(int $importId, int $chunk = 500, ?callable $handler = null): void
    {
        CatalogImportRow::where('catalog_import_id', $importId)
            ->where('import_status', 'ready_for_research')
            ->chunkById($chunk, function ($rows) use ($handler) {
                if ($handler) {
                    foreach ($rows as $row) {
                        $handler($row);
                    }
                }
            });
    }
}
