<?php

namespace App\Jobs\Catalog\Research;

use App\Models\Catalog\Research\SourceDocument;
use App\Services\Catalog\Research\SourceVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Verifies a single source document's URL/domain against the manufacturer's
 * official domain and marks it accordingly. Runs on the queue so a slow HTTP
 * check never blocks a request. The actual checks live in
 * SourceVerificationService (Phase 5).
 */
class VerifyProductSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(private int $sourceDocumentId) {}

    public function handle(SourceVerificationService $verifier): void
    {
        $source = SourceDocument::find($this->sourceDocumentId);
        if (! $source) {
            return;
        }

        $verifier->verifySource($source);
    }
}
