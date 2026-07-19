<?php

namespace App\Jobs;

use App\Enums\QuotationSourceTypeEnum;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Services\BoqValidationService;
use App\Services\Pricing\ProductSpecEngine;
use App\Services\QuotationAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
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

    /**
     * A very large BOQ is parsed in sequential slices — a 20 000-row file splits
     * into roughly 28 AI calls — and then the validation gate makes another pass.
     * Two hours is generous, but the job only ever runs as long as it needs to,
     * and a premature timeout would discard a nearly-finished parse.
     */
    public int $timeout = 7200;

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
        Cache::put($this->key('boq_ai_started_at'), now()->timestamp, now()->addHours(12));

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

            // Defaults for the cache-hit path: only complete parses are ever
            // cached, so a hit is by definition not partial and streams nothing.
            $result     = [];
            $streamed   = 0;
            $chunkTotal = 0;

            if (is_array($items) && $items !== []) {
                Log::info('ExtractQuotationItemsJob: reusing cached extraction.', [
                    'quotation_id' => $this->quotationId,
                    'items'        => count($items),
                    'bytes'        => $size,
                ]);
            } elseif (($chunkCount = $this->splitToDisk($ai, $absPath)) > 0) {
                // Large file: hand each part to its own job so they can run in
                // parallel across workers instead of one job walking all of them.
                $this->dispatchChunks($chunkCount);
                return;
            } else {
                // A very large BOQ is parsed in slices. Report each one so the
                // polling UI shows progress rather than a frozen spinner.
                // part 0 is the announcement fired once the split is known, before
                // the first slice is sent. Record the split so the UI can show it
                // even while the first (slowest-feeling) call is still in flight.
                // Stages that happen before any split exists (local parse, hand
                // off to AI) — otherwise a large file shows a bare spinner for
                // minutes before the first part is even known.
                $ai->onStage(function (string $message): void {
                    $this->status('running', $message);
                });

                $ai->onChunkProgress(function (int $part, int $total) use (&$chunkTotal): void {
                    $chunkTotal = $total;
                    Cache::put($this->key('boq_ai_chunk_total'), $total, now()->addHours(12));
                    Cache::put($this->key('boq_ai_chunk_current'), $part, now()->addHours(12));

                    $this->status('running', $part === 0
                        ? "Large file — split into {$total} parts. Starting…"
                        : "Reading part {$part} of {$total}…");
                });

                // Write each slice's rows as they arrive so the table fills in
                // progressively instead of staying empty until the last chunk.
                // The first slice to yield rows clears any previous run's — keyed
                // on $streamed rather than on part 1, because if part 1 fails the
                // clear would never happen and stale rows would mix with new ones.
                $ai->onChunkItems(function (array $chunkItems, int $part, int $total) use (&$streamed): void {
                    if ($streamed === 0) {
                        QuotationItem::where('quotation_request_id', $this->quotationId)->delete();
                    }

                    $streamed += $this->writeItems($chunkItems);

                    Cache::put($this->key('boq_ai_chunk_current'), $part, now()->addHours(12));
                    Cache::put($this->key('boq_ai_partial_count'), $streamed, now()->addHours(12));
                    $this->status('running', "Part {$part} of {$total} done — {$streamed} items so far.");
                });

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

                // Only cache a complete parse. When some slices of a large BOQ
                // failed, the result is missing rows — caching it would serve the
                // same incomplete set for 30 days and make a retry pointless.
                $partial = (bool) ($result['partial'] ?? false);

                if ($cacheKey !== null && ! $partial) {
                    Cache::put($cacheKey, $items, self::CACHE_TTL_DAYS * 86400);
                }
            }

            // Rows streamed chunk-by-chunk are already in the table — rewriting
            // them would delete what the user can currently see and duplicate the
            // work. Only persist here when nothing was streamed (a cache hit, or
            // a small file parsed in a single call).
            $count = $streamed > 0 ? $streamed : $this->persistItems($items);

            $quotation->update(['source_type' => QuotationSourceTypeEnum::Api]);

            // Never let a partial extraction pass as complete: the user must know
            // rows are missing before they price or send the quotation.
            if (! empty($result['partial'])) {
                $failedChunks = (int) ($result['failed_chunks'] ?? 0);
                $totalChunks  = (int) ($result['total_chunks'] ?? 0);

                $this->runValidationGate($items);
                $this->status('partial', "Extracted {$count} items, but {$failedChunks} of {$totalChunks} parts of this file could not be read, so some rows are missing. Please re-upload to try again.");
                return;
            }

            // Run the validation gate here too. It makes a chunked AI call per
            // batch of rows, so on a large BOQ it is every bit as slow as the
            // extraction — running it from the poll request would reintroduce
            // the timeout. The questions are cached for the component to pick up.
            $this->runValidationGate($items);

            // Say whether the file was split, so "one call" is distinguishable
            // from "never reported" when checking what actually happened.
            $this->status('done', $chunkTotal > 1
                ? "{$count} items extracted from the BOQ file, read in {$chunkTotal} parts."
                : "{$count} items extracted successfully from the BOQ file.");

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

        Cache::put($this->key('boq_ai_questions'), $questions, now()->addHours(12));
    }

    /**
     * Split the file into parts, or return [] when it fits in a single call.
     *
     * Only spreadsheets take this route: PDFs and images are handled inside the
     * AI service, which has its own per-format extraction.
     *
     * @return array<int, string>
     */
    /**
     * Split the workbook, writing each slice straight to disk.
     *
     * Returns the number of slices, or 0 when the file needs no split. Nothing
     * is returned in memory: holding every slice as an array is a second full
     * copy of the document on top of PhpSpreadsheet's grids, which is what
     * exhausted 2 GB on a 55-part file.
     */
    private function splitToDisk(QuotationAiService $ai, string $absPath): int
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        if (! in_array($ext, ['xlsx', 'xlsm', 'xlsb', 'xls', 'csv'], true)) {
            return 0;
        }

        $ai->onStage(function (string $message): void {
            $this->status('running', $message);
        });

        @ini_set('memory_limit', '2048M');

        $dir  = $this->chunkDir();
        $disk = Storage::disk('local');
        $disk->deleteDirectory($dir);

        try {
            $count = $ai->chunkSpreadsheetToDisk(
                $absPath,
                function (int $part, string $chunk) use ($disk, $dir): void {
                    $disk->put($dir . '/' . $part . '.txt', $chunk);
                },
            );

            Log::info('ExtractQuotationItemsJob: split decision.', [
                'quotation_id' => $this->quotationId,
                'chunks'       => $count,
                'peak_mb'      => round(memory_get_peak_usage(true) / 1048576),
            ]);

            return $count;
        } catch (\Throwable $e) {
            // Fall back to the single-job path rather than failing the upload.
            // Logged loudly, not silently: that path re-reads the file and can
            // exhaust memory on exactly the files that land here.
            Log::error('ExtractQuotationItemsJob: could not split file, falling back to one job.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
                'peak_mb'      => round(memory_get_peak_usage(true) / 1048576),
            ]);

            $disk->deleteDirectory($dir);

            return 0;
        }
    }

    private function chunkDir(): string
    {
        return 'boq-chunks/' . $this->quotationId;
    }

    /**
     * Fan the parts out as a batch, with a finaliser that runs after them all.
     *
     * Rows are cleared once here — never inside the part jobs, which run
     * concurrently and would otherwise delete each other's work.
     *
     * @param  array<int, string>  $chunks
     */
    private function dispatchChunks(int $total): void
    {
        QuotationItem::where('quotation_request_id', $this->quotationId)->delete();

        Cache::put($this->key('boq_ai_chunk_total'), $total, now()->addHours(12));
        Cache::put($this->key('boq_ai_chunk_current'), 0, now()->addHours(12));
        Cache::put($this->key('boq_ai_chunks_done'), 0, now()->addHours(12));
        Cache::put($this->key('boq_ai_chunks_failed'), 0, now()->addHours(12));
        Cache::put($this->key('boq_ai_partial_count'), 0, now()->addHours(12));

        $this->status('running', "Large file — split into {$total} parts. Starting…");

        // The slices are already on disk; each job carries only its path.
        $dir  = $this->chunkDir();
        $jobs = [];

        for ($part = 1; $part <= $total; $part++) {
            $jobs[] = new ExtractQuotationChunkJob(
                $this->quotationId,
                $dir . '/' . $part . '.txt',
                $part,
                $total,
                $this->ownerKey,
                $this->projectName,
                $this->projectStatus,
            );
        }

        // Locals, not $this: a closure capturing $this drags the whole job —
        // and everything it holds — into the serialized batch.
        $quotationId = $this->quotationId;
        $ownerKey    = $this->ownerKey;

        $batch = Bus::batch($jobs)
            ->name("boq-extract-{$quotationId}")
            // One bad slice must not cancel the rest: partial rows plus an
            // honest "some parts could not be read" beats losing everything.
            ->allowFailures()
            ->finally(function () use ($quotationId, $ownerKey, $total): void {
                FinishQuotationExtractionJob::dispatch($quotationId, $ownerKey, $total);
            })
            ->dispatch();

        unset($jobs);

        // Recorded so the user can stop the run and keep whatever has been
        // extracted so far — cancelling the batch is the only way to stop the
        // parts that have not started yet.
        Cache::put($this->key('boq_ai_batch_id'), $batch->id, now()->addHours(12));

        Log::info('ExtractQuotationItemsJob: fanned out chunk jobs.', [
            'quotation_id' => $this->quotationId,
            'chunks'       => $total,
            'batch_id'     => $batch->id,
            // Logged because the earlier 2 GB exhaustion happened around here
            // and the cause was never confirmed. If it recurs this is the number
            // that says where.
            'peak_mb'      => round(memory_get_peak_usage(true) / 1048576),
        ]);
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

        return $this->writeItems($aiItems);
    }

    /**
     * Append rows to the quotation without clearing what is already there.
     *
     * Used both by persistItems() and by the per-chunk streaming callback, so a
     * slice's rows land in the table the moment that slice is parsed.
     *
     * @param  array<int, array<string, mixed>>  $aiItems
     * @return int  number of rows written
     */
    private function writeItems(array $aiItems): int
    {
        $written = 0;

        $engine = app(ProductSpecEngine::class);

        // Chunked so a several-thousand-row BOQ never builds one giant statement.
        foreach (array_chunk($aiItems, 500) as $chunk) {
            foreach ($chunk as $aiItem) {
                $description = (string) ($aiItem['description'] ?? '');
                $rawUnit     = (string) ($aiItem['unit'] ?? '');

                // The extractor copies whatever the sheet said, which is how a
                // printer ends up measured in "liter/day". Correct it against the
                // product family — deterministic, no AI call. Rows with no known
                // family are left exactly as extracted rather than guessed at.
                $fixedUnit = $engine->normalizeUnitFor($description, $rawUnit);

                QuotationItem::create([
                    'quotation_request_id' => $this->quotationId,
                    'description'          => $description,
                    'quantity'             => $this->resolveQuantity($aiItem['quantity'] ?? null),
                    'unit_id'              => $this->resolveUnitId(
                        $fixedUnit !== null ? null : ($aiItem['unit_id'] ?? null),
                        $fixedUnit ?? $rawUnit,
                    ),
                    'category'             => (string) ($aiItem['category'] ?? ''),
                    'brand'                => (string) ($aiItem['brand'] ?? ''),
                    'status'               => $aiItem['status'] ?? 'pending',
                    'engineering_required' => (bool) ($aiItem['engineering_required'] ?? false),
                    'confidence'           => is_numeric($aiItem['confidence'] ?? null) ? (float) $aiItem['confidence'] : null,
                    'unit_price'           => is_numeric($aiItem['unit_price'] ?? null) ? (float) $aiItem['unit_price'] : null,
                    'raw_data'             => $aiItem['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'price_status'         => 'pending',
                    // Selected by default: the totals only sum selected rows, so
                    // leaving these false made a freshly extracted quotation add
                    // up to zero. Manually added rows already default to true.
                    'is_selected'          => true,
                ]);
                $written++;
            }
        }

        return $written;
    }

    /**
     * A BOQ line always means at least one of something.
     *
     * is_numeric(0) is true, so a literal 0 from the AI passed straight through
     * the old `is_numeric(...) ? ... : 1` guard — the fallback only caught null.
     */
    private function resolveQuantity(mixed $value): float
    {
        $quantity = is_numeric($value) ? (float) $value : 0.0;

        return $quantity > 0 ? $quantity : 1.0;
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
        Cache::put($this->key('boq_ai_status'), $status, now()->addHours(12));
        Cache::put($this->key('boq_ai_message'), $message, now()->addHours(12));
    }

    private function key(string $prefix): string
    {
        return $prefix . '_' . $this->ownerKey;
    }
}
