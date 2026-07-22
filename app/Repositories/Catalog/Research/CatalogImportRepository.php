<?php

namespace App\Repositories\Catalog\Research;

use App\Enums\Catalog\Research\CatalogImportStatusEnum;
use App\Models\Catalog\Research\CatalogImport;
use Illuminate\Support\Facades\DB;

/**
 * Data access for research Excel imports (table research_catalog_imports).
 * Separate from the pricing App\Repositories\Catalog\CatalogImportRepository.
 */
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

    public function findByUuid(string $uuid): CatalogImport
    {
        return CatalogImport::where('uuid', $uuid)->firstOrFail();
    }

    public function paginate(int $perPage = 20)
    {
        return CatalogImport::latest()->paginate($perPage);
    }

    public function update(CatalogImport $import, array $data): CatalogImport
    {
        $import->update($data);

        return $import;
    }

    public function markProcessing(CatalogImport $import): void
    {
        $import->update([
            'status'     => CatalogImportStatusEnum::Processing,
            'started_at' => now(),
        ]);
    }

    public function markCompleted(CatalogImport $import, bool $partial = false): void
    {
        $import->update([
            'status'       => $partial
                ? CatalogImportStatusEnum::PartiallyCompleted
                : CatalogImportStatusEnum::Completed,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the import failed. The imports table has no error column by spec, so
     * the human-readable reason is logged by the caller; status drives the UI.
     */
    public function markFailed(CatalogImport $import): void
    {
        $import->update([
            'status'       => CatalogImportStatusEnum::Failed,
            'completed_at' => now(),
        ]);
    }

    /** Atomic counter bump — safe under concurrent chunk processing. */
    public function incrementCounters(int $importId, int $imported, int $duplicate, int $failed): void
    {
        DB::connection('catalog')->table('research_catalog_imports')
            ->where('id', $importId)
            ->update([
                'imported_rows'  => DB::raw("imported_rows + {$imported}"),
                'duplicate_rows' => DB::raw("duplicate_rows + {$duplicate}"),
                'failed_rows'    => DB::raw("failed_rows + {$failed}"),
            ]);
    }
}
