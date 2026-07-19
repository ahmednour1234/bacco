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

    public function __construct(
        private int $quotationId,
        private string $chunk,
        private int $part,
        private int $total,
        private string $ownerKey,
        private string $projectName,
        private string $projectStatus,
    ) {}

    public function handle(QuotationAiService $ai): void
    {
        try {
            $result = $ai->parseChunk($this->chunk, $this->part, $this->total, [
                'quotation_id'   => $this->quotationId,
                'project_name'   => $this->projectName,
                'project_status' => $this->projectStatus,
            ]);

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
            // Always advance, even on failure: the coordinator waits on this
            // counter, and a part that never reports would hang the whole run.
            Cache::increment($this->key('boq_ai_chunks_done'));
            Cache::put($this->key('boq_ai_chunk_current'), $this->doneCount(), now()->addHours(2));

            $this->status('running', sprintf(
                'Part %d of %d done — %d items so far.',
                $this->doneCount(),
                $this->total,
                (int) Cache::get($this->key('boq_ai_partial_count'), 0),
            ));
        }
    }

    /**
     * Called by the queue when this slice times out or dies hard.
     *
     * handle() catches its own throwables and always advances the counters in
     * its finally block, so this only ever runs when handle() did NOT complete —
     * a timeout kill or a fatal. Incrementing unconditionally here would
     * otherwise double-count a caught error and push done past total.
     */
    public function failed(\Throwable $e): void
    {
        $this->recordFailure('Part stopped unexpectedly.');
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
                'quantity'             => is_numeric($aiItem['quantity'] ?? null) ? (float) $aiItem['quantity'] : 1,
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
