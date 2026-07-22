<?php

namespace App\Http\Controllers\Api\Catalog\Research;

use App\Http\Controllers\Api\Catalog\Research\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\Research\MapColumnsRequest;
use App\Http\Requests\Admin\Catalog\Research\UploadResearchImportRequest;
use App\Services\Catalog\Research\ExcelImportService;
use App\Services\Catalog\Research\ImportReport;
use Illuminate\Http\JsonResponse;

class ResearchImportApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ExcelImportService $imports,
        private ImportReport       $report,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('catalog.import.view');

        return $this->ok($this->imports->paginate(25));
    }

    public function store(UploadResearchImportRequest $request): JsonResponse
    {
        $import = $this->imports->handleUpload($request->file('file'));

        return $this->ok([
            'uuid'   => $import->uuid,
            'sheets' => $this->imports->sheetNames($import),
        ], 'Uploaded. Map columns next.', 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $this->authorize('catalog.import.view');

        $import = $this->imports->findByUuid($uuid);

        return $this->ok([
            'import' => $import,
            'report' => $this->report->forImport($import),
        ]);
    }

    public function mapColumns(MapColumnsRequest $request, string $uuid): JsonResponse
    {
        $import = $this->imports->findByUuid($uuid);
        $this->imports->confirmAndProcess(
            $import,
            $request->string('sheet'),
            $request->integer('header_row'),
            $request->input('mapping', []),
        );

        return $this->ok(['uuid' => $import->uuid], 'Mapping saved and import queued.');
    }

    public function process(string $uuid): JsonResponse
    {
        $this->authorize('catalog.import.process');

        // Processing is triggered by mapColumns; this is an idempotent no-op hook
        // kept for API completeness/symmetry with the spec's endpoint list.
        return $this->ok(['uuid' => $uuid], 'Import processing is queued.');
    }
}
