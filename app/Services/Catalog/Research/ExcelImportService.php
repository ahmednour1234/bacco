<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\CatalogImportStatusEnum;
use App\Jobs\Catalog\Research\ProcessCatalogResearchImportJob;
use App\Models\Catalog\Research\CatalogImport;
use App\Repositories\Catalog\Research\CatalogImportRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Orchestrates the upload → preview → mapping → process flow for research
 * imports. The original file is always stored and never deleted. Product
 * variants are NOT created here — importing only produces Product Families and
 * raw source rows; discovery of real variants happens later via research.
 */
class ExcelImportService
{
    public function __construct(
        private CatalogImportRepository $importRepo,
        private ExcelReaderService      $reader,
        private ColumnMappingService    $mappingService,
    ) {}

    private function disk(): string
    {
        return config('catalog_research.storage.disk', 'local');
    }

    private function folder(): string
    {
        return config('catalog_research.storage.folder', 'imports/catalog-research');
    }

    /** Store the upload, read sheet count, create the import record. */
    public function handleUpload(UploadedFile $file): CatalogImport
    {
        $path = $file->store($this->folder(), $this->disk());
        $abs  = Storage::disk($this->disk())->path($path);

        // Count sheets up-front (cheap; header/data read happens later).
        $sheets = 0;
        try {
            $sheets = count($this->reader->sheetNames($abs));
        } catch (\Throwable) {
            // Leave at 0; mapping step will surface a read error if truly broken.
        }

        return $this->importRepo->create([
            'original_file_name' => $file->getClientOriginalName(),
            'stored_file_path'   => $path,
            'file_type'          => strtolower($file->getClientOriginalExtension()),
            'file_size'          => $file->getSize(),
            'sheets_count'       => $sheets,
            'status'             => CatalogImportStatusEnum::MappingRequired,
            'uploaded_by'        => Auth::id(),
        ]);
    }

    public function absolutePath(CatalogImport $import): string
    {
        return Storage::disk($this->disk())->path($import->stored_file_path);
    }

    /** @return list<string> */
    public function sheetNames(CatalogImport $import): array
    {
        return $this->reader->sheetNames($this->absolutePath($import));
    }

    /** Best-guess header row for a sheet (Qimta files have a title banner on top). */
    public function detectHeaderRow(CatalogImport $import, ?string $sheet = null): int
    {
        return $this->reader->detectHeaderRow($this->absolutePath($import), $sheet);
    }

    /**
     * Preview the first $limit rows for the mapping UI.
     *
     * @return array{sheet:string, headers:list<string>, rows:list<list<string>>}
     */
    public function preview(CatalogImport $import, ?string $sheet = null, int $limit = 20, int $headerRow = 1): array
    {
        return $this->reader->preview($this->absolutePath($import), $sheet, $limit, $headerRow);
    }

    /**
     * Confirm mapping and queue processing. Guards against re-processing an
     * import that is already running or finished.
     */
    public function confirmAndProcess(CatalogImport $import, string $sheet, int $headerRow, array $mapping): CatalogImport
    {
        $headers = $this->reader->preview($this->absolutePath($import), $sheet, 1, $headerRow)['headers'];
        $clean   = $this->mappingService->sanitize($mapping, $headers);

        $import = $this->mappingService->save($import, $sheet, $headerRow, $clean);

        if (in_array($import->status, [
            CatalogImportStatusEnum::Processing,
            CatalogImportStatusEnum::Completed,
        ], true)) {
            return $import;
        }

        $this->importRepo->update($import, ['status' => CatalogImportStatusEnum::Uploaded]);

        ProcessCatalogResearchImportJob::dispatch($import->id)
            ->onQueue(config('catalog_research.queue', 'catalog-research'));

        return $import;
    }

    /**
     * Reset a finished/failed import and re-dispatch its processing job. Clears
     * previously-imported rows (raw source rows for this import only) and the
     * counters so the second pass starts clean. Re-uses the saved mapping.
     */
    public function reprocess(CatalogImport $import): CatalogImport
    {
        \Illuminate\Support\Facades\DB::connection('catalog')
            ->table('catalog_import_rows')
            ->where('catalog_import_id', $import->id)
            ->delete();

        $this->importRepo->update($import, [
            'status'         => CatalogImportStatusEnum::Uploaded,
            'total_rows'     => 0,
            'imported_rows'  => 0,
            'duplicate_rows' => 0,
            'failed_rows'    => 0,
            'started_at'     => null,
            'completed_at'   => null,
        ]);

        ProcessCatalogResearchImportJob::dispatch($import->id)
            ->onQueue(config('catalog_research.queue', 'default'));

        return $import;
    }

    public function paginate(int $perPage = 20)
    {
        return $this->importRepo->paginate($perPage);
    }

    public function findByUuid(string $uuid): CatalogImport
    {
        return $this->importRepo->findByUuid($uuid);
    }
}
