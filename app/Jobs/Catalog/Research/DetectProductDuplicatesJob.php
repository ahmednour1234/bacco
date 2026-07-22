<?php

namespace App\Jobs\Catalog\Research;

use App\Services\Catalog\Research\DeduplicationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scans a family's variants for likely duplicates and records candidate pairs in
 * the duplicate review queue. Never auto-merges variants that differ by SKU —
 * those always go to human review (DeduplicationEngine, Phase 5).
 */
class DetectProductDuplicatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(private int $familyId) {}

    public function handle(DeduplicationEngine $engine): void
    {
        $engine->scanFamily($this->familyId);
    }
}
