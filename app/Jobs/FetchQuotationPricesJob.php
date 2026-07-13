<?php

namespace App\Jobs;

use App\Enums\NotificationTypeEnum;
use App\Mail\QuotationPricedMail;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Services\BoqCleaningService;
use App\Services\NotificationService;
use App\Services\PricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FetchQuotationPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum seconds before the job times out.
     * Generous timeout to allow parallel DeepSeek chunks to all complete.
     */
    public int $timeout = 180;

    public function __construct(
        private readonly int    $quotationId,
        private readonly ?int   $userId,
        private readonly string $quotationUuid,
    ) {}

    public function handle(PricingService $pricingService, NotificationService $notificationService, BoqCleaningService $boqCleaner): void
    {
        $quotation = QuotationRequest::with('client')->find($this->quotationId);

        if (! $quotation) {
            return;
        }

        $items = QuotationItem::where('quotation_request_id', $this->quotationId)
            ->where('is_selected', true)
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
        $guardedItems = [];
        foreach ($items as $item) {
            $blockers = $boqCleaner->pricingGate([
                'description' => (string) $item['description'],
                'unit'        => (string) ($item['unit'] ?? ''),
                'quantity'    => $item['quantity'] ?? null,
            ]);

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

        try {
            $priced    = $pricingService->fetchPrices($guardedItems);
            $gotPrices = 0;

            foreach ($priced as $row) {
                if (! empty($row['id']) && isset($row['unit_price']) && $row['unit_price'] > 0) {
                    QuotationItem::where('id', $row['id'])->update([
                        'unit_price'   => $row['unit_price'],
                        'price_source' => $row['price_source'] ?? null,
                        'price_status' => 'pending',
                    ]);
                    $gotPrices++;
                }
            }

            // Also delete any guarded-out items (filtered before pricing)
            $guardedIds = array_column($guardedItems, 'id');
            $skippedIds = array_diff(array_column($items, 'id'), $guardedIds);
            if (! empty($skippedIds)) {
                QuotationItem::whereIn('id', $skippedIds)->delete();
            }

            $remainingUnpriced = QuotationItem::where('quotation_request_id', $this->quotationId)
                ->where('is_selected', true)
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
        }
    }
}
