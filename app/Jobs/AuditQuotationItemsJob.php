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

    /**
     * Bounded well below the extraction's window.
     *
     * The audit is advisory — it only ever produces optional questions. Letting
     * it run for an hour would hold a worker that the parts of the next upload
     * are waiting for, to produce something the user can proceed without.
     */
    public int $timeout = 600;

    public int $tries = 1;

    /** Matches the interactive cap in the component. */
    private const MAX_QUESTIONS = 10;

    /**
     * Above this many rows the audit is skipped entirely.
     *
     * The gate is a chunked AI call per batch of rows, so a 5 000-row quotation
     * is hundreds of sequential calls — minutes of worker time and real cost, to
     * ask at most ten questions. Past this size the questions stop being worth
     * what they take.
     */
    private const MAX_AUDITABLE_ROWS = 1500;

    public function __construct(
        private int $quotationId,
        private string $ownerKey,
    ) {}

    public function handle(): void
    {
        $questions = [];

        try {
            $rowCount = QuotationItem::where('quotation_request_id', $this->quotationId)->count();

            // Skipped rather than run slowly on a huge quotation. The gate makes
            // an AI call per batch of rows, so thousands of rows means hundreds
            // of sequential calls — minutes of a worker, and real cost, for at
            // most ten optional questions. An empty question set is a valid
            // outcome: the user proceeds exactly as if the gate found nothing.
            if ($rowCount > self::MAX_AUDITABLE_ROWS) {
                Log::info('AuditQuotationItemsJob: skipped, quotation too large to audit.', [
                    'quotation_id' => $this->quotationId,
                    'rows'         => $rowCount,
                    'limit'        => self::MAX_AUDITABLE_ROWS,
                ]);

                Cache::put('boq_ai_questions_' . $this->ownerKey, [], now()->addHours(12));
                return;
            }

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

        Cache::put('boq_ai_questions_' . $this->ownerKey, $questions, now()->addHours(12));
    }

    /**
     * Called when the job times out or dies before handle() can catch it.
     *
     * handle() swallows its own errors, so this only fires on a hard kill — a
     * timeout, or a failure during deserialization. Writing an empty question
     * set matters: the page waits on this key, and leaving it unset would stall
     * a flow whose rows are already extracted and perfectly usable.
     */
    public function failed(\Throwable $e): void
    {
        Log::warning('AuditQuotationItemsJob failed; continuing without questions.', [
            'quotation_id' => $this->quotationId,
            'message'      => $e->getMessage(),
        ]);

        Cache::put('boq_ai_questions_' . $this->ownerKey, [], now()->addHours(12));
    }
}
