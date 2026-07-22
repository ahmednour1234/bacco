<?php

namespace App\Jobs\Catalog\Pricing;

use App\Models\Boq;
use App\Models\BoqItem;
use App\Services\Catalog\Pricing\BoqMatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Matches every line of a BOQ against the catalog.
 *
 * Runs on the queue because a large BOQ means hundreds of catalog searches, and
 * a single unmatched line must never abort the rest — a partially matched BOQ
 * is still useful, an aborted one is not.
 */
class MatchBoqItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries   = 1;

    public function __construct(
        public readonly int $boqId,
        public readonly bool $onlyUnmatched = false,
    ) {}

    public function handle(BoqMatchingService $matcher): void
    {
        $boq = Boq::find($this->boqId);

        if (! $boq) {
            return;
        }

        $matched = 0;
        $none    = 0;
        $failed  = 0;

        BoqItem::query()
            ->where('boq_id', $this->boqId)
            ->when($this->onlyUnmatched, fn ($q) => $q->whereNotExists(function ($sub) {
                // Skip lines that already have candidates, so a re-run only
                // fills gaps instead of redoing the whole BOQ.
                $sub->selectRaw('1')
                    ->from(config('database.connections.catalog.database') . '.boq_variant_matches as m')
                    ->whereColumn('m.boq_item_id', 'boq_items.id');
            }))
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($matcher, &$matched, &$none, &$failed) {
                foreach ($items as $item) {
                    try {
                        $count = $matcher->matchItem($item);
                        $count > 0 ? $matched++ : $none++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('BOQ item match failed.', [
                            'boq_item_id' => $item->id,
                            'message'     => $e->getMessage(),
                        ]);
                    }
                }
            });

        Log::info('BOQ matching finished.', [
            'boq_id'    => $this->boqId,
            'matched'   => $matched,
            'no_match'  => $none,
            'failed'    => $failed,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('MatchBoqItemsJob died.', [
            'boq_id'  => $this->boqId,
            'message' => $e->getMessage(),
        ]);
    }
}
