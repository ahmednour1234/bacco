<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\UploadCatalogRequest;
use App\Repositories\Catalog\CatalogRepository;
use App\Services\Catalog\CatalogImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogImportController extends Controller
{
    public function __construct(
        private CatalogImportService $importService,
        private CatalogRepository    $catalogRepo,
    ) {}

    public function index(): View
    {
        $imports  = $this->importService->paginate(20);
        $catalogs = $this->catalogRepo->all();

        return view('admin.catalog.imports.index', compact('imports', 'catalogs'));
    }

    public function create(): View
    {
        $catalogs = $this->catalogRepo->all();
        return view('admin.catalog.imports.create', compact('catalogs'));
    }

    public function store(UploadCatalogRequest $request): RedirectResponse
    {
        $catalogId = $request->filled('catalog_id') ? (int) $request->catalog_id : null;
        foreach ($request->file('files') as $file) {
            $this->importService->handleUpload($file, $catalogId);
        }

        return redirect()
            ->route('admin.catalog.imports.index')
            ->with('success', count($request->file('files')) . ' file(s) queued for import.');
    }

    public function show(int $id): View
    {
        $import = $this->importService->find($id);
        return view('admin.catalog.imports.show', compact('import'));
    }

    public function failedRows(int $id): View
    {
        $import     = $this->importService->find($id);
        $failedRows = $this->importService->failedRows($id);
        return view('admin.catalog.imports.failed-rows', compact('import', 'failedRows'));
    }

    /**
     * Start a queue worker in the background to process pending jobs.
     * Uses proc_open/popen on Windows, proc_open on Linux (avoids exec() which is disabled on many hosts).
     */
    public function runQueue(): RedirectResponse
    {
        $php     = PHP_BINARY;
        $artisan = base_path('artisan');
        $cmd     = escapeshellarg($php) . ' ' . escapeshellarg($artisan)
                 . ' queue:work --stop-when-empty --timeout=10800 --memory=512';

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B ' . $cmd . ' > NUL 2>&1', 'r'));
        } else {
            // proc_open is available even when exec/shell_exec are disabled
            $descriptors = [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']];
            $proc = @proc_open($cmd . ' &', $descriptors, $pipes);
            if (is_resource($proc)) {
                proc_close($proc);
            }
        }

        return redirect()
            ->route('admin.catalog.imports.index')
            ->with('success', 'Queue worker started — pending jobs are now being processed.');
    }

    /**
     * Poll endpoint for AJAX progress refresh.
     */
    public function progress(int $id): \Illuminate\Http\JsonResponse
    {
        $import = $this->importService->find($id);

        return response()->json([
            'status'         => $import->status,
            'total_rows'     => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'inserted_rows'  => $import->inserted_rows,
            'updated_rows'   => $import->updated_rows,
            'failed_rows'    => $import->failed_rows,
            'percent'        => $import->progressPercent(),
            'finished_at'    => $import->finished_at?->toDateTimeString(),
        ]);
    }
}
