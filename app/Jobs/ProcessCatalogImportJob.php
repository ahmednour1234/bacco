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
use Maatwebsite\Excel\Facades\Excel;

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

            Excel::import(
                new CatalogProductsImport($import, $importRepo, $productRepo, $categoryRepo),
                $filePath
            );

            // Refresh to get final counts
            $import->refresh();
            $importRepo->markCompleted($import);

        } catch (\Throwable $e) {
            $importRepo->markFailed($import, $e->getMessage());
            throw $e; // re-throw so the queue logs it
        }
    }
}
