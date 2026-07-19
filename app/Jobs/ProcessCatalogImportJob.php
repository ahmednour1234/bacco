<?php

namespace App\Jobs;

use App\Imports\CatalogProductsImport;
use App\Repositories\Catalog\CatalogCategoryRepository;
use App\Repositories\Catalog\CatalogImportRepository;
use App\Repositories\Catalog\CatalogProductRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCatalogImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Allow up to 3 hours for very large files (700 000+ rows).
     * Adjust based on your server capacity.
     */
    public int $timeout = 10800;

    /**
     * Do not retry on failure — partial progress is preserved per chunk.
     */
    public int $tries = 1;

    public function __construct(private int $catalogImportId) {}

    /**
     * Called by the queue when the job times out or dies hard.
     *
     * A timeout kills the worker outright, so the catch in handle() never runs
     * and the import is left marked 'processing'. That status is also the guard
     * against re-processing, so the record would be stuck permanently — unable
     * to finish and unable to be retried by the user.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('ProcessCatalogImportJob died.', [
            'catalog_import_id' => $this->catalogImportId,
            'message'           => $e->getMessage(),
        ]);

        // Never throw from here: an exception inside failed() would mask the
        // original failure. find() is findOrFail(), so a deleted record throws.
        try {
            $importRepo = app(CatalogImportRepository::class);
            $import     = $importRepo->find($this->catalogImportId);

            if ($import->status === 'processing') {
                $importRepo->markFailed($import, 'Import stopped unexpectedly. Please try again.');
            }
        } catch (\Throwable $inner) {
            Log::error('ProcessCatalogImportJob: could not mark the import failed.', [
                'catalog_import_id' => $this->catalogImportId,
                'message'           => $inner->getMessage(),
            ]);
        }
    }

    public function handle(
        CatalogImportRepository   $importRepo,
        CatalogProductRepository  $productRepo,
        CatalogCategoryRepository $categoryRepo,
    ): void {
        $import = $importRepo->find($this->catalogImportId);

        // Guard: skip if already processing / completed
        if (in_array($import->status, ['processing', 'completed'])) {
            return;
        }

        $importRepo->markProcessing($import);

        try {
            // Support both old path (storage/app/) and Laravel 11+ private path (storage/app/private/)
            $filePath = storage_path('app/' . $import->file_path);
            if (!file_exists($filePath)) {
                $filePath = storage_path('app/private/' . $import->file_path);
            }

            if (!file_exists($filePath)) {
                throw new \RuntimeException("Import file not found: {$filePath}");
            }

            (new CatalogProductsImport($import, $importRepo, $productRepo, $categoryRepo))
                ->import($filePath);

            // Refresh to get final counts
            $import->refresh();
            $importRepo->markCompleted($import);
            // Bust the catalog stats cache so new counts appear immediately
            \App\Services\CatalogStats::flush();

        } catch (\Throwable $e) {
            $importRepo->markFailed($import, $e->getMessage());
            throw $e; // re-throw so the queue logs it
        }
    }
}
