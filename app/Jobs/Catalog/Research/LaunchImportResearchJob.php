<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ImportRowStatusEnum;
use App\Models\Catalog\Research\CatalogImportRow;
use App\Models\Catalog\Research\ProductFamily;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Fans out research for every "ready for research" Product Family produced by
 * one import. Runs on the queue so the HTTP request returns instantly; each
 * family then flows through the staged research pipeline (discover
 * manufacturers → series → variants → verify → sources), producing real,
 * source-backed products — never invented ones.
 *
 * Idempotent: families already researched/queued are skipped (ResearchPlanService
 * guards against duplicate concurrent runs per family).
 */
class LaunchImportResearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries   = 1;

    public function __construct(private int $importId) {}

    public function handle(ResearchPlanService $plan): void
    {
        $started = 0;
        $skipped = 0;

        // Distinct families for this import that are ready to research.
        $familyIds = CatalogImportRow::query()
            ->where('catalog_import_id', $this->importId)
            ->where('import_status', ImportRowStatusEnum::ReadyForResearch->value)
            ->whereNotNull('product_family_id')
            ->distinct()
            ->pluck('product_family_id');

        foreach ($familyIds as $familyId) {
            $family = ProductFamily::find($familyId);
            if (! $family || ! $family->research_status->canStart()) {
                $skipped++;
                continue;
            }

            try {
                $plan->start($family);
                $started++;
            } catch (\Throwable $e) {
                // Already-running / lock contention → just skip, don't abort the batch.
                $skipped++;
                Log::info('Skipped starting research for a family.', [
                    'family_id' => $familyId,
                    'message'   => $e->getMessage(),
                ]);
            }
        }

        Log::info('Import research launch complete.', [
            'import_id' => $this->importId,
            'started'   => $started,
            'skipped'   => $skipped,
        ]);
    }
}
