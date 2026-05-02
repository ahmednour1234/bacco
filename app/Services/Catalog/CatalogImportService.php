<?php

namespace App\Services\Catalog;

use App\Jobs\ProcessCatalogImportJob;
use App\Models\Catalog\CatalogImport;
use App\Repositories\Catalog\CatalogImportRepository;
use App\Repositories\Catalog\CatalogRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CatalogImportService
{
    public function __construct(
        private CatalogImportRepository $importRepo,
        private CatalogRepository       $catalogRepo,
    ) {}

    /**
     * Store the uploaded file, create an import record, and dispatch the job.
     * If $catalogId is null, a catalog is automatically created from the filename.
     */
    public function handleUpload(UploadedFile $file, ?int $catalogId = null): CatalogImport
    {
        // Auto-create catalog from filename if not provided
        if (!$catalogId) {
            $catalogName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $catalog     = $this->catalogRepo->firstOrCreate($catalogName);
            $catalogId   = $catalog->id;
        }

        // Store file: storage/app/imports/catalog/{filename}
        $stored = $file->store('imports/catalog', 'local');

        $import = $this->importRepo->create([
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $stored,
            'catalog_id'  => $catalogId,
            'status'      => 'pending',
            'uploaded_by' => Auth::id(),
        ]);

        ProcessCatalogImportJob::dispatch($import->id);

        return $import;
    }

    public function paginate(int $perPage = 20)
    {
        return $this->importRepo->paginate($perPage);
    }

    public function find(int $id): CatalogImport
    {
        return $this->importRepo->find($id);
    }

    public function failedRows(int $importId, int $perPage = 50)
    {
        return $this->importRepo->failedRows($importId, $perPage);
    }
}
