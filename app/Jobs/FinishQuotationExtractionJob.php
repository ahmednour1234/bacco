<?php

namespace App\Jobs;

use App\Enums\QuotationSourceTypeEnum;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Services\BoqValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Closes out an extraction once every part has been parsed.
 *
 * The parts run as independent jobs, so none of them can know it was the last
 * one. This runs after the whole batch finishes: it reports the final status,
 * and runs the validation gate over the complete set of rows rather than over
 * whatever one part happened to see.
 */
class FinishQuotationExtractionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** The validation gate is itself a chunked AI pass over every row. */
    public int $timeout = 3600;

    public int $tries = 1;

    /** Matches the interactive cap in the component. */
    private const MAX_QUESTIONS = 10;

    public function __construct(
        private int $quotationId,
        private string $ownerKey,
        private int $totalChunks,
    ) {}

    public function handle(): void
    {
        // Remove the slice directory whatever the outcome — each part deletes
        // its own file, but a part that never ran leaves one behind.
        Storage::disk('local')->deleteDirectory('boq-chunks/' . $this->quotationId);

        // The batch's finally() also fires when the user stops the run, so this
        // would otherwise overwrite their "stopped at N items" with a partial or
        // failed verdict. The rows are still theirs; leave the status alone.
        if (Cache::get($this->key('boq_ai_stopped_by_user'))) {
            Cache::forget($this->key('boq_ai_stopped_by_user'));
            return;
        }

        try {
            $quotation = QuotationRequest::find($this->quotationId);
            if (! $quotation) {
                $this->status('failed', 'Quotation not found.');
                return;
            }

            $count  = QuotationItem::where('quotation_request_id', $this->quotationId)->count();
            $failed = (int) Cache::get($this->key('boq_ai_chunks_failed'), 0);

            if ($count === 0) {
                $this->status('no_items', $failed > 0
                    ? "None of the {$this->totalChunks} parts could be read. Please check the file and try again."
                    : 'No BOQ items were found in this file. Please check it has supply products with quantities and units.');
                return;
            }

            $quotation->update(['source_type' => QuotationSourceTypeEnum::Api]);

            // Gate over the complete set: a per-part gate would ask about rows
            // in isolation and miss anything that only conflicts across parts.
            $this->runValidationGate();

            if ($failed > 0) {
                $this->status('partial', "Extracted {$count} items, but {$failed} of {$this->totalChunks} parts could not be read, so some rows are missing. Please re-upload to try again.");
                return;
            }

            $this->status('done', $this->totalChunks > 1
                ? "{$count} items extracted from the BOQ file, read in {$this->totalChunks} parts."
                : "{$count} items extracted successfully from the BOQ file.");
        } catch (\Throwable $e) {
            Log::error('FinishQuotationExtractionJob failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);

            $this->status('failed', 'Extraction finished with an error. Please try again.');
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->status('failed', 'Extraction stopped unexpectedly. Please try again.');
    }

    /**
     * Audit the extracted rows and cache any questions for the user.
     *
     * Never throws: the gate is advisory, so an AI outage must not fail an
     * otherwise-good extraction.
     */
    private function runValidationGate(): void
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

            $result    = app(BoqValidationService::class)->validate($items);
            $questions = array_slice($result['questions'] ?? [], 0, self::MAX_QUESTIONS);
        } catch (\Throwable $e) {
            Log::error('FinishQuotationExtractionJob: validation gate failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);
        }

        Cache::put($this->key('boq_ai_questions'), $questions, now()->addHours(12));
    }

    private function status(string $status, string $message): void
    {
        Cache::put($this->key('boq_ai_status'), $status, now()->addHours(12));
        Cache::put($this->key('boq_ai_message'), $message, now()->addHours(12));
    }

    private function key(string $prefix): string
    {
        return $prefix . '_' . $this->ownerKey;
    }
}
