<?php

namespace App\Jobs;

use App\Models\QuotationItem;
use App\Models\Unit;
use App\Services\Pricing\ProductSpecEngine;
use App\Services\QuotationAiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Parses one slice of a large BOQ.
 *
 * A big file is split into parts, and each part becomes one of these. With more
 * than one queue worker they run concurrently, so a 29-part file takes about as
 * long as its slowest part rather than the sum of all of them. With a single
 * worker they simply run in sequence — same result, same cost, no worse than
 * before.
 *
 * Each job writes its own rows as soon as its slice returns, so the table fills
 * in progressively no matter how the parts are scheduled. A failed part loses
 * only its own rows: the counters below still advance so the run can finish and
 * report honestly how much is missing.
 */
class ExtractQuotationChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** One slice is a single AI call; it never needs the whole-file budget. */
    public int $timeout = 600;

    /** No retries — a second pass would double-charge and duplicate rows. */
    public int $tries = 1;

    /**
     * @param  string  $chunkPath  Slice text on the local disk, not the text
     *                             itself: 55 jobs each carrying ~60 KB were
     *                             serialized together and exhausted 2 GB.
     */
    public function __construct(
        private int $quotationId,
        private string $chunkPath,
        private int $part,
        private int $total,
        private string $ownerKey,
        private string $projectName,
        private string $projectStatus,
    ) {}

    public function handle(QuotationAiService $ai): void
    {
        // The user stopped the run. Checked before the AI call, not after, so a
        // cancelled batch stops costing money immediately instead of paying for
        // every part already queued.
        if ($this->batch()?->cancelled()) {
            // Still clean up: a cancelled run leaves as many slice files behind
            // as it had parts left.
            Storage::disk('local')->delete($this->chunkPath);
            return;
        }

        try {
            if (! Storage::disk('local')->exists($this->chunkPath)) {
                $this->recordFailure("Slice file is missing: {$this->chunkPath}");
                return;
            }

            $chunk = (string) Storage::disk('local')->get($this->chunkPath);

            $result = $ai->parseChunk($chunk, $this->part, $this->total, [
                'quotation_id'   => $this->quotationId,
                'project_name'   => $this->projectName,
                'project_status' => $this->projectStatus,
            ]);

            // Released before the rows are written: the slice is no longer
            // needed and holding it doubles this job's footprint.
            unset($chunk);

            if (! ($result['success'] ?? false)) {
                $this->recordFailure($result['error'] ?? 'Chunk parsing failed.');
                return;
            }

            $written = $this->writeItems($result['items'] ?? []);

            Cache::increment($this->key('boq_ai_partial_count'), $written);
        } catch (\Throwable $e) {
            Log::error('ExtractQuotationChunkJob failed.', [
                'quotation_id' => $this->quotationId,
                'part'         => $this->part,
                'message'      => $e->getMessage(),
            ]);

            $this->recordFailure($e->getMessage());
        } finally {
            // This slice will not be read again, whatever happened to it.
            Storage::disk('local')->delete($this->chunkPath);

            // Always advance, even on failure: the coordinator waits on this
            // counter, and a part that never reports would hang the whole run.
            $this->markCounted();

            // Clamped: the counter is shared across concurrent jobs, and showing
            // a number larger than the total reads as a broken page.
            $done = min($this->doneCount(), $this->total);
            Cache::put($this->key('boq_ai_chunk_current'), $done, now()->addHours(12));

            // Failures are surfaced live: 63 parts yielding 122 items looks the
            // same as 63 parts working fine unless the failure count is visible.
            $failed = (int) Cache::get($this->key('boq_ai_chunks_failed'), 0);

            $this->status('running', sprintf(
                'Part %d of %d done — %d items so far.%s',
                $done,
                $this->total,
                (int) Cache::get($this->key('boq_ai_partial_count'), 0),
                $failed > 0 ? " ({$failed} parts could not be read)" : '',
            ));
        }
    }

    /**
     * Called by the queue when this slice times out or dies hard.
     *
     * Counts only if handle() never reached its finally block. An earlier
     * version assumed failed() and finally were mutually exclusive — they are
     * not: a `return` inside try still runs finally, so a chunk that failed its
     * AI call counted once there and once here, and the progress counter ran
     * past the total ("part 65 of 63").
     */
    public function failed(\Throwable $e): void
    {
        if (Cache::get($this->key('boq_ai_chunk_counted_' . $this->part))) {
            return;
        }

        $this->recordFailure('Part stopped unexpectedly.');
        $this->markCounted();
    }

    /**
     * Advance the done counter for this part, at most once.
     *
     * The per-part marker is what makes it idempotent: whichever of finally or
     * failed() gets there first wins, and the other becomes a no-op.
     */
    private function markCounted(): void
    {
        $marker = $this->key('boq_ai_chunk_counted_' . $this->part);

        if (Cache::get($marker)) {
            return;
        }

        Cache::put($marker, true, now()->addHours(12));
        Cache::increment($this->key('boq_ai_chunks_done'));
    }

    private function recordFailure(string $reason): void
    {
        Cache::increment($this->key('boq_ai_chunks_failed'));

        Log::warning('ExtractQuotationChunkJob: part produced no rows.', [
            'quotation_id' => $this->quotationId,
            'part'         => $this->part,
            'of'           => $this->total,
            'reason'       => $reason,
        ]);
    }

    private function doneCount(): int
    {
        return (int) Cache::get($this->key('boq_ai_chunks_done'), 0);
    }

    /**
     * Append this slice's rows.
     *
     * Never clears: other parts are writing to the same quotation, possibly at
     * the same moment, so deleting here would destroy a sibling's work. The
     * coordinator clears once, before any part is dispatched.
     *
     * @param  array<int, array<string, mixed>>  $aiItems
     */
    private function writeItems(array $aiItems): int
    {
        if ($aiItems === []) {
            return 0;
        }

        $engine  = app(ProductSpecEngine::class);
        $written = 0;

        foreach ($aiItems as $aiItem) {
            $description = (string) ($aiItem['description'] ?? '');
            $rawUnit     = (string) ($aiItem['unit'] ?? '');
            $fixedUnit   = $engine->normalizeUnitFor($description, $rawUnit);

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
                'is_selected'          => true,
            ]);
            $written++;
        }

        return $written;
    }

    /**
     * A BOQ line always means at least one of something.
     *
     * is_numeric(0) is true, so a literal 0 from the AI passed straight through
     * the old `is_numeric(...) ? ... : 1` guard — the fallback only caught null.
     * A zero-quantity row prices to nothing and reads as broken, so treat any
     * non-positive value as the unknown it is and fall back to 1.
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
