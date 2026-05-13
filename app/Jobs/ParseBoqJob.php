<?php

namespace App\Jobs;

use App\Models\Boq;
use App\Models\BoqItem;
use App\Services\QuotationAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParseBoqJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Seconds before the job is considered timed out. */
    public int $timeout = 300;

    /** Do not retry on failure — user can re-upload. */
    public int $tries = 1;

    public function __construct(
        private readonly int    $boqId,
        private readonly int    $userId,
        private readonly string $filePath,
        private readonly array  $context = [],
    ) {}

    public function handle(QuotationAiService $ai): void
    {
        $cacheKey    = 'boq_ai_status_' . $this->userId;
        $msgCacheKey = 'boq_ai_message_' . $this->userId;

        try {
            $absPath = Storage::disk('local')->path($this->filePath);

            if (! file_exists($absPath)) {
                Log::error('ParseBoqJob: File not found.', ['path' => $absPath]);
                Cache::put($msgCacheKey, 'File not found on server. Please try uploading again.', now()->addMinutes(30));
                Cache::put($cacheKey, 'failed', now()->addMinutes(30));
                return;
            }

            $result = $ai->parseBoq($absPath, $this->context);

            $boq = Boq::find($this->boqId);
            if (! $boq) {
                Cache::put($msgCacheKey, 'BOQ record not found.', now()->addMinutes(30));
                Cache::put($cacheKey, 'failed', now()->addMinutes(30));
                return;
            }

            if (! $result['success']) {
                $errorMsg = $result['error'] ?? 'Extraction failed.';
                Cache::put($msgCacheKey, $errorMsg, now()->addMinutes(30));
                Cache::put($cacheKey, 'failed', now()->addMinutes(30));
                Log::warning('ParseBoqJob: Extraction failed.', [
                    'boq_id' => $this->boqId,
                    'error'  => $errorMsg,
                ]);
                return;
            }

            if (empty($result['items'])) {
                Cache::put($msgCacheKey, 'No items could be found in the file. Please check the file or add items manually.', now()->addMinutes(30));
                Cache::put($cacheKey, 'no_items', now()->addMinutes(30));
                return;
            }

            // Wipe previous items and persist the freshly extracted ones.
            BoqItem::where('boq_id', $this->boqId)->delete();

            $count = 0;
            foreach ($result['items'] as $aiItem) {
                $item = array_merge([
                    'description'          => '',
                    'quantity'             => 1,
                    'unit_id'              => null,
                    'category'             => '',
                    'brand'                => '',
                    'status'               => 'pending',
                    'engineering_required' => false,
                    'confidence'           => null,
                    'unit_price'           => null,
                    'raw_data'             => null,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ], $aiItem);

                BoqItem::create([
                    'boq_id'               => $this->boqId,
                    'description'          => (string) ($item['description'] ?? ''),
                    'quantity'             => is_numeric($item['quantity']) ? (float) $item['quantity'] : 1,
                    'unit_id'              => $item['unit_id'] ?? null,
                    'category'             => (string) ($item['category'] ?? ''),
                    'brand'                => (string) ($item['brand'] ?? ''),
                    'status'               => $item['status'] ?? 'pending',
                    'engineering_required' => (bool) ($item['engineering_required'] ?? false),
                    'confidence'           => is_numeric($item['confidence'] ?? null) ? (float) $item['confidence'] : null,
                    'unit_price'           => is_numeric($item['unit_price'] ?? null) ? (float) $item['unit_price'] : null,
                    'raw_data'             => $item['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ]);
                $count++;
            }

            Cache::put($msgCacheKey, "Successfully extracted {$count} items from your file.", now()->addMinutes(30));
            Cache::put($cacheKey, 'done', now()->addMinutes(30));

        } catch (\Throwable $e) {
            Cache::put($msgCacheKey, $e->getMessage(), now()->addMinutes(30));
            Cache::put($cacheKey, 'failed', now()->addMinutes(30));
            Log::error('ParseBoqJob: Unexpected error.', [
                'boq_id'  => $this->boqId,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }
}
