<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\CatalogImport;
use App\Models\Catalog\CatalogImportFailedRow;
use Illuminate\Support\Facades\DB;

class CatalogImportRepository
{
    public function create(array $data): CatalogImport
    {
        return CatalogImport::create($data);
    }

    public function find(int $id): CatalogImport
    {
        return CatalogImport::findOrFail($id);
    }

    public function paginate(int $perPage = 20)
    {
        return CatalogImport::with('catalog')
            ->latest()
            ->paginate($perPage);
    }

    public function markProcessing(CatalogImport $import): void
    {
        $import->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markCompleted(CatalogImport $import): void
    {
        $import->update([
            'status'      => 'completed',
            'finished_at' => now(),
            'total_rows'  => $import->processed_rows + $import->failed_rows,
        ]);
    }

    public function markFailed(CatalogImport $import, string $message): void
    {
        $import->update([
            'status'        => 'failed',
            'finished_at'   => now(),
            'error_message' => $message,
        ]);
    }

    /**
     * Atomic increment for progress counters — safe with concurrent chunk processing.
     */
    public function incrementProgress(int $importId, int $processed, int $inserted, int $updated, int $failed): void
    {
        DB::connection('catalog')->table('catalog_imports')
            ->where('id', $importId)
            ->update([
                'processed_rows' => DB::raw("processed_rows + {$processed}"),
                'inserted_rows'  => DB::raw("inserted_rows + {$inserted}"),
                'updated_rows'   => DB::raw("updated_rows + {$updated}"),
                'failed_rows'    => DB::raw("failed_rows + {$failed}"),
            ]);
    }

    public function saveFailedRows(int $importId, array $rows): void
    {
        if (empty($rows)) return;
        DB::connection('catalog')
            ->table('catalog_import_failed_rows')
            ->insert($rows);
    }

    public function failedRows(int $importId, int $perPage = 50)
    {
        return CatalogImportFailedRow::where('catalog_import_id', $importId)
            ->orderBy('row_number')
            ->paginate($perPage);
    }
}
