<?php

namespace App\Jobs;

use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\Unit;
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
 * Runs the AI extraction for an uploaded BOQ file in the background.
 *
 * Large BOQs (thousands of rows) take minutes to parse, which blows past the
 * request timeout when done inline — the request dies and the browser falls
 * back to a plain POST, producing a 405 on a GET-only route. Doing it here
 * keeps the upload request fast and lets the UI poll for progress.
 *
 * Progress is reported through the cache keys CreateBoq::checkAiStatus() reads:
 *   boq_ai_status_{owner}   pending|running|done|no_items|failed
 *   boq_ai_message_{owner}  human-readable result/error
 *   boq_ai_started_at_{owner}  unix ts, used for the stale-job timeout
 *
 * $owner is the user id, or the guest token when the BOQ came from /try.
 */
class ExtractBoqItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Big files legitimately take a while; keep well above the AI call itself. */
    public int $timeout = 1800;

    /** No retries — a second AI pass would double-charge and duplicate items. */
    public int $tries = 1;

    public function __construct(
        private int $boqId,
        private string $storedPath,
        private string $projectName,
        /** User id, or the guest token for the unauthenticated /try flow. */
        private string $ownerKey,
    ) {}

    public function handle(QuotationAiService $ai): void
    {
        $this->status('running', '');
        Cache::put($this->key('boq_ai_started_at'), now()->timestamp, now()->addHours(2));

        try {
            $absPath = Storage::disk('local')->path($this->storedPath);

            if (! is_file($absPath)) {
                throw new \RuntimeException("Uploaded BOQ file is missing: {$this->storedPath}");
            }

            $result = $ai->parseBoq($absPath, [
                'boq_id'       => $this->boqId,
                'project_name' => $this->projectName,
            ]);

            if (! ($result['success'] ?? false)) {
                $this->status('failed', $result['error'] ?? 'Extraction failed. Please try again.');
                return;
            }

            if (empty($result['items'])) {
                $this->status('no_items', 'No items found in the file. Please add items manually.');
                return;
            }

            $count = $this->persistItems($result['items']);

            $this->status('done', $count . ' items extracted from your file.');

        } catch (\Throwable $e) {
            Log::error('ExtractBoqItemsJob failed.', [
                'boq_id'  => $this->boqId,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->status('failed', 'Extraction failed. Please try uploading the file again.');
        }
    }

    /** Called by the queue when the job blows its timeout or dies hard. */
    public function failed(\Throwable $e): void
    {
        $this->status('failed', 'Extraction stopped unexpectedly. Please try again with a smaller file.');
    }

    /**
     * Replace the BOQ's items with the freshly extracted set.
     *
     * @param  array<int, array<string, mixed>>  $aiItems
     * @return int  number of rows written
     */
    private function persistItems(array $aiItems): int
    {
        BoqItem::where('boq_id', $this->boqId)->delete();

        $written = 0;

        // Chunked so a 5 000-row BOQ never builds one giant statement.
        foreach (array_chunk($aiItems, 500) as $chunk) {
            foreach ($chunk as $aiItem) {
                BoqItem::create([
                    'boq_id'               => $this->boqId,
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
                    'is_selected'          => true,
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
