<?php

namespace App\Jobs;

use App\Enums\NotificationTypeEnum;
use App\Mail\QuotationPricedMail;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Services\Pricing\ProductSpecEngine;
use App\Services\BoqCleaningService;
use App\Services\NotificationService;
use App\Services\PriceVerificationService;
use App\Services\PricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FetchQuotationPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum seconds before the job times out.
     * Generous timeout to allow parallel DeepSeek chunks to all complete.
     */
    /**
     * Generous, because this job makes three AI passes over every line — spec
     * qualification, pricing, then price verification — each chunked and pooled.
     * A large BOQ legitimately runs for several minutes.
     */
    public int $timeout = 1800;

    /**
     * Never retry.
     *
     * Without this the job inherits the queue's unlimited retries, so a failed
     * pricing run is re-attempted forever — and every attempt is a full round of
     * paid AI calls over the whole quotation. A failure here is reported to the
     * user, who can re-run pricing deliberately.
     */
    public int $tries = 1;

    /**
     * Treat a timeout as a final failure, not a retry.
     *
     * A job killed mid-run — by the timeout, or by the memory limit — is
     * re-reserved by the worker, which then reports MaxAttemptsExceeded. That
     * message hid the real cause here for several runs: the log said "attempted
     * too many times" while the actual error was memory exhaustion inside
     * Guzzle's response buffer.
     */
    public bool $failOnTimeout = true;

    /**
     * Rows priced per pass.
     *
     * The job used to load every row, gate them, then hand the whole set to the
     * pricing service — which itself holds up to 8 concurrent AI responses. On a
     * large quotation that combination exhausted the 128 MB limit inside
     * Guzzle's response buffer, and the retries that followed reported only
     * "attempted too many times", hiding the real cause.
     *
     * Pricing in slices keeps peak memory flat regardless of quotation size.
     */
    private const PRICING_BATCH = 300;

    public function __construct(
        private readonly int    $quotationId,
        private readonly ?int   $userId,
        private readonly string $quotationUuid,
    ) {}

    public function handle(PricingService $pricingService, NotificationService $notificationService, BoqCleaningService $boqCleaner, PriceVerificationService $priceVerifier): void
    {
        $quotation = QuotationRequest::with('client')->find($this->quotationId);

        if (! $quotation) {
            return;
        }

        $items = QuotationItem::where('quotation_request_id', $this->quotationId)
            ->where('status', '!=', 'rejected')
            ->with('unit')
            ->get()
            ->map(fn(QuotationItem $item) => [
                'id'          => $item->id,
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'category'    => (string) ($item->category ?? ''),
                'brand'       => (string) ($item->brand ?? ''),
                'unit'        => (string) ($item->unit?->name ?? $item->unit?->symbol ?? ''),
                'unit_price'  => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'price_source' => $item->price_source,
            ])
            ->toArray();

        // Final pricing gate — the last line of defense before the pricing engine.
        // Re-validates each row (classifier + unit + quantity) so anything that
        // leaked past extraction (a discipline heading, total, placeholder, or a
        // row with no real unit/qty) is never priced. Nothing is silently dropped:
        // every block is logged with its reasons for the audit trail.
        $engine       = app(ProductSpecEngine::class);
        $guardedItems = [];
        $recovered    = 0;

        foreach ($items as $item) {
            // A blank unit is a gap in the sheet, not evidence that the row is
            // not a product. "Parapet cap straight (W=750mm)" and "Single RJ45
            // outlet for BMS" were both being blocked — and then deleted — purely
            // because their unit cell was empty. Ask the product family first.
            if (trim((string) ($item['unit'] ?? '')) === '') {
                $inferred = $engine->normalizeUnitFor((string) $item['description'], '');

                if ($inferred !== null && $inferred !== '') {
                    $item['unit'] = $inferred;
                    $recovered++;

                    QuotationItem::where('id', $item['id'])->update([
                        'unit_id' => $this->resolveUnitId($inferred),
                    ]);
                }
            }

            $blockers = $boqCleaner->pricingGate([
                'description' => (string) $item['description'],
                'unit'        => (string) ($item['unit'] ?? ''),
                'quantity'    => $item['quantity'] ?? null,
            ]);

            // A row blocked *only* for a missing unit is still a real product —
            // it just cannot be measured yet. Keep it and let it come back
            // unpriced, rather than deleting work the user uploaded.
            if ($blockers === ['missing unit']) {
                Log::info('FetchQuotationPricesJob: kept a row with no unit.', [
                    'quotation_id' => $this->quotationId,
                    'description'  => $item['description'],
                ]);
                continue;
            }

            if ($blockers === []) {
                $guardedItems[] = $item;
            } else {
                Log::warning('FetchQuotationPricesJob: Pricing gate blocked a non-product row.', [
                    'quotation_id' => $this->quotationId,
                    'description'  => $item['description'],
                    'reasons'      => $blockers,
                ]);
            }
        }

        if ($recovered > 0) {
            Log::info('FetchQuotationPricesJob: recovered units from the product catalog.', [
                'quotation_id' => $this->quotationId,
                'recovered'    => $recovered,
            ]);
        }

        try {
            // Report progress so a large quotation shows movement instead of one
            // long silent wait. Mirrors the extraction job's cache contract.
            $ownerKey = (string) ($this->userId ?? $this->quotationUuid);
            $pricingService->onProgress(function (int $done, int $total) use ($ownerKey): void {
                Cache::put(
                    'boq_pricing_message_' . $ownerKey,
                    "Pricing items… batch {$done} of {$total}.",
                    now()->addHours(2),
                );
            });

            // Priced in slices. Handing the whole set over at once meant every
            // row, every AI response, and Guzzle's buffers were live together —
            // which is what exhausted 128 MB and left only "attempted too many
            // times" in the log. Each slice is released before the next begins.
            $gotPrices    = 0;
            $sliceCount   = (int) ceil(count($guardedItems) / self::PRICING_BATCH);
            $sliceIndex   = 0;

            foreach (array_chunk($guardedItems, self::PRICING_BATCH) as $slice) {
                $sliceIndex++;

                Cache::put(
                    'boq_pricing_message_' . $ownerKey,
                    "Pricing items… batch {$sliceIndex} of {$sliceCount}.",
                    now()->addHours(2),
                );

                $priced = $pricingService->fetchPrices($slice);

                // Second pass — independently re-check each price against Saudi
                // market / supplier rates via the AI before anything is shown to
                // the client. The verdict + verified price are persisted so the
                // audit trail is intact.
                $priced = $priceVerifier->verify($priced);

                $gotPrices += $this->persistPrices($priced);

                // Released before the next slice: holding these is the whole
                // reason the job ran out of memory.
                unset($priced, $slice);
                gc_collect_cycles();

                Log::info('FetchQuotationPricesJob: slice priced.', [
                    'quotation_id' => $this->quotationId,
                    'slice'        => $sliceIndex . '/' . $sliceCount,
                    'peak_mb'      => round(memory_get_peak_usage(true) / 1048576),
                ]);
            }

            // Also delete any guarded-out items (filtered before pricing)
            $guardedIds = array_column($guardedItems, 'id');
            $skippedIds = array_diff(array_column($items, 'id'), $guardedIds);
            if (! empty($skippedIds)) {
                QuotationItem::whereIn('id', $skippedIds)->delete();
            }

            $remainingUnpriced = QuotationItem::where('quotation_request_id', $this->quotationId)
                ->where('status', '!=', 'rejected')
                ->where(function ($query) {
                    $query->whereNull('unit_price')
                        ->orWhere('unit_price', '<=', 0);
                })
                ->count();
            $removed = count($skippedIds);
            $body    = $removed > 0
                ? "تم تسعير {$gotPrices} عنصر وحذف {$removed} عنصر لم يُسعَّر تلقائياً."
                : "تم تسعير جميع {$gotPrices} عنصر بنجاح.";

            if ($remainingUnpriced > 0) {
                $body = "Priced {$gotPrices} item(s). {$remainingUnpriced} selected item(s) still need pricing review.";
            }

            $notificationService->send(
                title: 'عرض السعر جاهز للمراجعة',
                body: $body,
                type: NotificationTypeEnum::PricingComplete,
                recipientIds: $this->userId ? [$this->userId] : [],
                actionUrl: route('enduser.quotations.show', $this->quotationUuid),
            );

            if ($remainingUnpriced === 0 && $quotation->client?->email) {
                try {
                    Mail::to($quotation->client->email)->send(new QuotationPricedMail(
                        quotation: $quotation,
                        actionUrl: route('enduser.quotations.show', $this->quotationUuid),
                    ));
                } catch (\Throwable $mailException) {
                    Log::error('FetchQuotationPricesJob: quotation priced email failed.', [
                        'quotation_id' => $this->quotationId,
                        'client_id'    => $quotation->client_id,
                        'email'        => $quotation->client->email,
                        'message'      => $mailException->getMessage(),
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('FetchQuotationPricesJob failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);

            $notificationService->send(
                title: 'فشل جلب الأسعار',
                body: 'حدث خطأ أثناء تسعير عرض السعر. يرجى المحاولة مرة أخرى.',
                type: NotificationTypeEnum::General,
                recipientIds: $this->userId ? [$this->userId] : [],
                actionUrl: route('enduser.quotations.show', $this->quotationUuid),
            );
        } finally {
            // Always mark the quotation so the UI polling can advance past the loading screen,
            // whether pricing succeeded fully, partially, or failed entirely.
            $quotation->update(['prices_fetched_at' => now()]);
            Cache::forget('boq_pricing_message_' . (string) ($this->userId ?? $this->quotationUuid));
        }
    }

    /**
     * Called by the queue when the job times out or dies hard.
     *
     * A timeout kills the worker process outright, so neither the catch nor the
     * finally block above runs. Without this the quotation keeps a null
     * prices_fetched_at and the page polls a spinner forever.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('FetchQuotationPricesJob died.', [
            'quotation_id' => $this->quotationId,
            'message'      => $e->getMessage(),
        ]);

        QuotationRequest::where('id', $this->quotationId)
            ->whereNull('prices_fetched_at')
            ->update(['prices_fetched_at' => now()]);

        Cache::forget('boq_pricing_message_' . (string) ($this->userId ?? $this->quotationUuid));
    }

    /**
     * Write one slice's prices back to the rows.
     *
     * @param  array<int, array<string, mixed>>  $priced
     * @return int  how many rows received a price
     */
    private function persistPrices(array $priced): int
    {
        $written = 0;

        foreach ($priced as $row) {
            if (empty($row['id']) || ! isset($row['unit_price']) || $row['unit_price'] <= 0) {
                continue;
            }

            $original = (float) $row['unit_price'];
            $verdict  = $row['price_verdict'] ?? null;
            $verified = isset($row['verified_price']) && is_numeric($row['verified_price']) && $row['verified_price'] > 0
                ? (float) $row['verified_price']
                : $original;

            // The price the client sees is the AI-verified one:
            //  - confirmed → same as the original estimate
            //  - corrected → the corrected market price replaces it
            //  - flagged / unverified → keep the original, but mark it for review
            $finalPrice  = in_array($verdict, ['confirmed', 'corrected'], true) ? $verified : $original;
            $priceStatus = ($verdict === 'flagged') ? 'needs_review' : 'pending';

            QuotationItem::where('id', $row['id'])->update([
                'unit_price'              => $finalPrice,
                'price_source'            => $row['price_source'] ?? null,
                'verified_price'          => $verified,
                'price_verdict'           => $verdict,
                'price_verification_note' => $row['price_verification_note'] ?? null,
                'price_verified_at'       => $verdict !== null ? now() : null,
                'price_status'            => $priceStatus,
            ]);

            $written++;
        }

        return $written;
    }

    /**
     * Resolve a unit label to its id, creating the unit if it is new.
     *
     * Used when a unit is recovered from the product catalog for a row whose
     * sheet cell was blank, so the correction is persisted rather than living
     * only for the duration of this run.
     */
    private function resolveUnitId(string $label): ?int
    {
        $label = trim($label);

        if ($label === '') {
            return null;
        }

        return Unit::firstOrCreate(
            ['name' => $label],
            ['symbol' => mb_strtolower(mb_substr($label, 0, 20))]
        )->id;
    }
}
