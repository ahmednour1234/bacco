<?php

namespace App\Services\Catalog\Research;

use App\Models\Catalog\Research\CatalogImport;
use App\Repositories\Catalog\Research\CatalogImportRowRepository;

/**
 * Builds the post-import report required by the spec:
 * total / imported / duplicate / failed / missing-description / ready / review.
 * Pure read model — no side effects.
 */
class ImportReport
{
    public function __construct(private CatalogImportRowRepository $rowRepo) {}

    /** @return array<string,int> */
    public function forImport(CatalogImport $import): array
    {
        $counts = $this->rowRepo->statusCounts($import->id);

        return [
            'total_rows'              => (int) $import->total_rows,
            'imported_rows'           => (int) $import->imported_rows,
            'duplicate_rows'          => (int) $import->duplicate_rows,
            'failed_rows'             => (int) $import->failed_rows,
            'rows_missing_description'=> (int) ($counts['missing_description'] ?? 0),
            'rows_ready_for_research' => (int) ($counts['ready_for_research'] ?? 0),
            'rows_requiring_review'   => (int) ($counts['requires_review'] ?? 0),
        ];
    }
}
