<?php

namespace App\Jobs\Catalog\Research;

use App\Models\Catalog\Research\ProductFamily;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Optional scheduled refresh: re-runs research for stale families (not checked
 * within source_refresh_days), incomplete families, or families whose products
 * were marked discontinued. Dispatched by the scheduler command.
 */
class RefreshProductCatalogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(private int $familyId) {}

    public function handle(ResearchPlanService $plan): void
    {
        $family = ProductFamily::find($this->familyId);
        if (! $family || ! $family->research_status->canStart()) {
            return;
        }

        try {
            $plan->start($family);
        } catch (\Throwable $e) {
            Log::info('RefreshProductCatalogJob skipped a family.', [
                'family'  => $this->familyId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
