<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\Research\MapColumnsRequest;
use App\Http\Requests\Admin\Catalog\Research\UploadResearchImportRequest;
use App\Services\Catalog\Research\ColumnMappingService;
use App\Services\Catalog\Research\ExcelImportService;
use App\Services\Catalog\Research\ImportReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin screens for the research Excel import workflow:
 * list → upload → sheet select → column mapping → preview → process → report.
 * Guarded by the `employee` route middleware plus per-action Gate abilities.
 */
class ResearchImportController extends Controller
{
    public function __construct(
        private ExcelImportService   $importService,
        private ColumnMappingService $mappingService,
        private ImportReport         $report,
    ) {}

    public function index(): View
    {
        $this->authorize('catalog.import.view');

        $imports = $this->importService->paginate(20);

        return view('admin.catalog.research.imports.index', compact('imports'));
    }

    public function create(): View
    {
        $this->authorize('catalog.import.create');

        return view('admin.catalog.research.imports.create');
    }

    public function store(UploadResearchImportRequest $request): RedirectResponse
    {
        $import = $this->importService->handleUpload($request->file('file'));

        return redirect()
            ->route('admin.catalog.research.imports.map', $import->uuid)
            ->with('success', __('app.file_uploaded_map_columns'));
    }

    /** Sheet selection + column mapping + preview screen. */
    public function map(string $uuid): View
    {
        $this->authorize('catalog.import.process');

        $import     = $this->importService->findByUuid($uuid);
        $sheetNames = $this->importService->sheetNames($import);
        $current    = request('sheet') ?: ($import->column_mapping['sheet'] ?? ($sheetNames[0] ?? null));
        $headerRow  = (int) request('header_row', $import->column_mapping['header_row'] ?? 1);

        $preview = $current
            ? $this->importService->preview($import, $current, 20, $headerRow)
            : ['sheet' => null, 'headers' => [], 'rows' => []];

        return view('admin.catalog.research.imports.map', [
            'import'       => $import,
            'sheetNames'   => $sheetNames,
            'currentSheet' => $current,
            'headerRow'    => $headerRow,
            'preview'      => $preview,
            'targetFields' => $this->mappingService->targetFields(),
            'savedMapping' => $import->column_mapping['map'] ?? [],
        ]);
    }

    public function process(MapColumnsRequest $request, string $uuid): RedirectResponse
    {
        $import = $this->importService->findByUuid($uuid);

        $this->importService->confirmAndProcess(
            $import,
            $request->string('sheet'),
            $request->integer('header_row'),
            $request->input('mapping', []),
        );

        return redirect()
            ->route('admin.catalog.research.imports.show', $import->uuid)
            ->with('success', __('app.import_queued'));
    }

    public function show(string $uuid): View
    {
        $this->authorize('catalog.import.view');

        $import = $this->importService->findByUuid($uuid);
        $report = $this->report->forImport($import);

        return view('admin.catalog.research.imports.show', compact('import', 'report'));
    }

    /**
     * Re-queue a failed/finished import to run again (e.g. after fixing the
     * queue worker). Clears its counters and re-dispatches the job.
     */
    public function reprocess(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.import.process');

        $import = $this->importService->findByUuid($uuid);
        $this->importService->reprocess($import);

        return redirect()
            ->route('admin.catalog.research.imports.show', $import->uuid)
            ->with('success', __('app.import_queued'));
    }

    /**
     * Kick off a background queue worker (default queue) so queued imports and
     * research jobs are processed without shell access. Mirrors the existing
     * catalog module's "Run Queue" action.
     */
    public function runQueue(): RedirectResponse
    {
        $this->authorize('catalog.import.process');

        $php     = PHP_BINARY;
        $artisan = base_path('artisan');
        $cmd     = escapeshellarg($php) . ' ' . escapeshellarg($artisan)
                 . ' queue:work --stop-when-empty --timeout=10800 --memory=512';

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B ' . $cmd . ' > NUL 2>&1', 'r'));
        } else {
            $descriptors = [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']];
            $proc = @proc_open($cmd . ' &', $descriptors, $pipes);
            if (is_resource($proc)) {
                proc_close($proc);
            }
        }

        return back()->with('success', __('app.queue_started'));
    }
}
