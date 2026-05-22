<?php

namespace App\Jobs;

use App\Enums\NotificationTypeEnum;
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

class FetchQuotationPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum seconds before the job times out.
     */
    public int $timeout = 300;

    public function __construct(
        private readonly int    $quotationId,
        private readonly int    $userId,
        private readonly string $quotationUuid,
    ) {}

    public function handle(PricingService $pricingService, NotificationService $notificationService, BoqCleaningService $boqCleaner): void
    {
        $quotation = QuotationRequest::find($this->quotationId);

        if (! $quotation) {
            return;
        }

        $items = QuotationItem::where('quotation_request_id', $this->quotationId)
            ->get()
            ->map(fn(QuotationItem $item) => [
                'id'          => $item->id,
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'category'    => (string) ($item->category ?? ''),
                'brand'       => (string) ($item->brand ?? ''),
                'unit_price'  => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'price_source' => $item->price_source,
            ])
            ->toArray();

        // Pre-flight guard: skip any item that should not reach the pricing engine.
        // This is a last-resort safety net for items that bypassed earlier filtering.
        $guardedItems = [];
        foreach ($items as $item) {
            $check = $boqCleaner->filterItem((string) $item['description']);
            if ($check['keep']) {
                $guardedItems[] = $item;
            } else {
                Log::warning('FetchQuotationPricesJob: Pricing guard caught non-supply item.', [
                    'quotation_id' => $this->quotationId,
                    'description'  => $item['description'],
                    'reason'       => $check['rejection_reason'],
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

            $total   = count($priced);
            $missing = $total - $gotPrices;

            $body = $missing > 0
                ? "تم تسعير {$gotPrices} عنصر. {$missing} عنصر لم يُسعَّر تلقائياً."
                : "تم تسعير جميع {$gotPrices} عنصر بنجاح.";

            $notificationService->send(
                title: 'عرض السعر جاهز للمراجعة',
                body: $body,
                type: NotificationTypeEnum::PricingComplete,
                recipientIds: [$this->userId],
                actionUrl: route('enduser.quotations.show', $this->quotationUuid),
            );

        } catch (\Throwable $e) {
            Log::error('FetchQuotationPricesJob failed.', [
                'quotation_id' => $this->quotationId,
                'message'      => $e->getMessage(),
            ]);

            $notificationService->send(
                title: 'فشل جلب الأسعار',
                body: 'حدث خطأ أثناء تسعير عرض السعر. يرجى المحاولة مرة أخرى.',
                type: NotificationTypeEnum::General,
                recipientIds: [$this->userId],
                actionUrl: route('enduser.quotations.show', $this->quotationUuid),
            );
        } finally {
            // Always mark the quotation so the UI polling can advance past the loading screen,
            // whether pricing succeeded fully, partially, or failed entirely.
            $quotation->update(['prices_fetched_at' => now()]);
        }
    }
}
