<?php

namespace App\Jobs;

use App\Models\QuotationItem;
use App\Services\BoqValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Runs the BOQ validation gate over a quotation's rows.
 *
 * The gate is a chunked AI pass, far too slow for a web request, so it always
 * runs here. Used when the user stops an extraction early — the rows they kept
 * still go through pricing, so they still need auditing.
 *
 * Writes only the questions cache, never the extraction status, so it can never
 * overwrite the message the user was left with.
 */
class AuditQuotationItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    /** Matches the interactive cap in the component. */
    private const MAX_QUESTIONS = 10;

    public function __construct(
        private int $quotationId,
        private string $ownerKey,
    ) {}

    public function handle(): void
    {
        $questions = [];

        try {
            $items = QuotationItem::where('quotation_request_id', $this->quotationId)
                ->with('unit')
                ->get()
                ->map(fn(QuotationItem $item) => [
                    'description' => (string) $item->description,
                    'quantity'    => (float) $item->quantity,
                    'unit'        => (string) ($item->unit?->name ?? ''),
                    'category'    => (string) ($item->category ?? ''),
                    'brand'       => (string) ($item->brand ?? ''),
                ])
                ->toArray();

            if ($items !== []) {
                $result    = app(BoqValidationService::class)->validate($items);
                $questions = array_slice($result['questions'] ?? [], 0, self::MAX_QUESTIONS);
            }
        } catch (\Throwable $e) {
            // Advisory only: an AI outage must not cost the user their rows.
            Log::error('AuditQuotationItemsJob failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);
        }

        Cache::put('boq_ai_questions_' . $this->ownerKey, $questions, now()->addHours(2));
    }
}
