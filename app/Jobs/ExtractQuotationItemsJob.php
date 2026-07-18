<?php

namespace App\Jobs;

use App\Enums\QuotationSourceTypeEnum;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Services\BoqValidationService;
use App\Services\QuotationAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Runs the AI extraction for a quotation's uploaded BOQ file in the background.
 *
 * The quotation page previously parsed the file inline. On any real BOQ that
 * exceeds the request timeout: the Livewire request dies, the browser retries as
 * a plain POST, and the GET-only route answers 405. Extraction belongs on the
 * queue, with the UI polling for the result.
 *
 * Progress is reported through the same cache-key convention the BOQ flow uses,
 * so both pages share one polling contract:
 *   boq_ai_status_{owner}      pending|running|done|no_items|failed
 *   boq_ai_message_{owner}     human-readable result/error
 *   boq_ai_started_at_{owner}  unix ts, for the stale-job timeout
 */
class ExtractQuotationItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Large files legitimately take minutes; keep well above the AI call. */
    public int $timeout = 1800;

    /** No retries — a second AI pass would double-charge and duplicate items. */
    public int $tries = 1;

    /** Files at or above this size get their extraction cached (500 KB). */
    private const CACHE_MIN_BYTES = 512000;

    /** How long a cached extraction stays reusable. */
    private const CACHE_TTL_DAYS = 30;

    /** Cap the interactive gate so the user is never asked an endless queue. */
    private const MAX_QUESTIONS = 10;

    public function __construct(
        private int $quotationId,
        private string $storedPath,
        private string $projectName,
        private string $projectStatus,
        private string $ownerKey,
    ) {}

    public function handle(QuotationAiService $ai): void
    {
        $this->status('running', '');
        Cache::put($this->key('boq_ai_started_at'), now()->timestamp, now()->addHours(2));

        try {
            $quotation = QuotationRequest::find($this->quotationId);
            if (! $quotation) {
                $this->status('failed', 'Quotation not found.');
                return;
            }

            $absPath = Storage::disk('local')->path($this->storedPath);
            if (! is_file($absPath)) {
                throw new \RuntimeException("Uploaded BOQ file is missing: {$this->storedPath}");
            }

            // Reuse a previous parse of the same file. Keyed on content hash, so a
            // renamed copy still hits, and shared with the BOQ flow's cache.
            $cacheKey = null;
            $size     = @filesize($absPath) ?: 0;

            if ($size >= self::CACHE_MIN_BYTES) {
                $hash     = @hash_file('sha256', $absPath);
                $cacheKey = $hash ? 'boq_extraction_' . $hash : null;
            }

            $items = $cacheKey ? Cache::get($cacheKey) : null;

            if (is_array($items) && $items !== []) {
                Log::info('ExtractQuotationItemsJob: reusing cached extraction.', [
                    'quotation_id' => $this->quotationId,
                    'items'        => count($items),
                    'bytes'        => $size,
                ]);
            } else {
                $result = $ai->parseBoq($absPath, [
                    'quotation_id'   => $this->quotationId,
                    'project_name'   => $this->projectName,
                    'project_status' => $this->projectStatus,
                ]);

                if (! ($result['success'] ?? false)) {
                    $this->status('failed', $result['error'] ?? 'AI extraction failed.');
                    return;
                }

                if (empty($result['items'])) {
                    $rejected = count($result['rejected'] ?? []);
                    $this->status('no_items', $rejected > 0
                        ? "AI extracted {$rejected} rows but all were rejected as non-supply items (labour, headings, etc.). Please verify the file contains supply products with quantities."
                        : 'The AI service could not find any BOQ items in this file. Please check it has supply products with quantities and units.');
                    return;
                }

                $items = $result['items'];

                if ($cacheKey !== null) {
                    Cache::put($cacheKey, $items, self::CACHE_TTL_DAYS * 86400);
                }
            }

            $count = $this->persistItems($items);

            $quotation->update(['source_type' => QuotationSourceTypeEnum::Api]);

            // Run the validation gate here too. It makes a chunked AI call per
            // batch of rows, so on a large BOQ it is every bit as slow as the
            // extraction — running it from the poll request would reintroduce
            // the timeout. The questions are cached for the component to pick up.
            $this->runValidationGate($items);

            $this->status('done', $count . ' items extracted successfully from the BOQ file.');

        } catch (\Throwable $e) {
            Log::error('ExtractQuotationItemsJob failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
            ]);

            $this->status('failed', 'Extraction failed. Please try uploading the file again.');
        }
    }

    /**
     * Audit the extracted rows and cache the questions the user must resolve.
     *
     * Never throws: the gate is advisory, so a DeepSeek outage must not fail an
     * otherwise-good extraction. On failure an empty queue is cached, which the
     * component reads as "gate passed".
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function runValidationGate(array $items): void
    {
        $questions = [];

        try {
            $result    = app(BoqValidationService::class)->validate($items);
            $questions = array_slice($result['questions'] ?? [], 0, self::MAX_QUESTIONS);
        } catch (\Throwable $e) {
            Log::error('ExtractQuotationItemsJob: validation gate failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);
        }

        Cache::put($this->key('boq_ai_questions'), $questions, now()->addHours(2));
    }

    /** Called by the queue when the job blows its timeout or dies hard. */
    public function failed(\Throwable $e): void
    {
        $this->status('failed', 'Extraction stopped unexpectedly. Please try again with a smaller file.');
    }

    /**
     * Replace the quotation's items with the freshly extracted set.
     *
     * @param  array<int, array<string, mixed>>  $aiItems
     * @return int  number of rows written
     */
    private function persistItems(array $aiItems): int
    {
        QuotationItem::where('quotation_request_id', $this->quotationId)->delete();

        $written = 0;

        // Chunked so a several-thousand-row BOQ never builds one giant statement.
        foreach (array_chunk($aiItems, 500) as $chunk) {
            foreach ($chunk as $aiItem) {
                QuotationItem::create([
                    'quotation_request_id' => $this->quotationId,
                    'description'          => (string) ($aiItem['description'] ?? ''),
                    'quantity'             => is_numeric($aiItem['quantity'] ?? null) ? (float) $aiItem['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($aiItem['unit_id'] ?? null, $aiItem['unit'] ?? null),
                    'category'             => (string) ($aiItem['category'] ?? ''),
                    'brand'                => (string) ($aiItem['brand'] ?? ''),
                    'status'               => $aiItem['status'] ?? 'pending',
                    'engineering_required' => (bool) ($aiItem['engineering_required'] ?? false),
                    'confidence'           => is_numeric($aiItem['confidence'] ?? null) ? (float) $aiItem['confidence'] : null,
                    'unit_price'           => is_numeric($aiItem['unit_price'] ?? null) ? (float) $aiItem['unit_price'] : null,
                    'raw_data'             => $aiItem['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'price_status'         => 'pending',
                    'is_selected'          => false,
                ]);
                $written++;
            }
        }

        return $written;
    }

    private function resolveUnitId(?int $unitId, mixed $unitText): ?int
    {
        if ($unitId !== null) {
            return $unitId;
        }

        $label = trim((string) ($unitText ?? ''));
        if ($label === '') {
            return null;
        }

        return Unit::firstOrCreate(
            ['name' => $label],
            ['symbol' => mb_strtolower(mb_substr($label, 0, 20))]
        )->id;
    }

    private function status(string $status, string $message): void
    {
        Cache::put($this->key('boq_ai_status'), $status, now()->addHours(2));
        Cache::put($this->key('boq_ai_message'), $message, now()->addHours(2));
    }

    private function key(string $prefix): string
    {
        return $prefix . '_' . $this->ownerKey;
    }
}
