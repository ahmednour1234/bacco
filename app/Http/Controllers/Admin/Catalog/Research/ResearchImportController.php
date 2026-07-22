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
        $logLines = $this->recentLogFor($import->id);

        return view('admin.catalog.research.imports.show', compact('import', 'report', 'logLines'));
    }

    /**
     * Best-effort tail of the Laravel log for lines mentioning this import, so
     * the admin can see *why* an import failed without shell access. Read-only
     * and defensive — never throws if the log is missing or huge.
     *
     * @return list<string>
     */
    private function recentLogFor(int $importId): array
    {
        $path = storage_path('logs/laravel.log');
        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        // Read only the tail of the file (last ~256 KB) to stay cheap.
        $size   = filesize($path);
        $offset = max(0, $size - 262144);
        $chunk  = (string) @file_get_contents($path, false, null, $offset);
        if ($chunk === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/', $chunk) ?: [];
        $hits  = array_filter($lines, fn ($l) =>
            str_contains($l, '"import_id":' . $importId)
            || str_contains($l, 'catalog import')
            || str_contains($l, 'Research import')
            || str_contains($l, 'ProcessCatalogResearchImportJob'));

        // Newest last; keep the last 8 relevant lines.
        return array_slice(array_values($hits), -8);
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
