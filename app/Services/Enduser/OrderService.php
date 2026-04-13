<?php

namespace App\Services\Enduser;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QuotationRequest;
use App\Repositories\Enduser\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly OrderRepository $repo) {}

    /**
     * Create an Order (and its items) from a QuotationRequest + selected items array.
     * Selected items are the rows the client checked on the show-quotation page.
     */
    public function createFromQuotation(QuotationRequest $quotation, array $selectedItems): Order
    {
        return DB::transaction(function () use ($quotation, $selectedItems) {

            $vatRate  = 0.15;
            $subtotal = collect($selectedItems)
                ->sum(fn($i) => (float) ($i['unit_price'] ?? 0) * (float) ($i['quantity'] ?? 0));
            $vatAmount  = round($subtotal * $vatRate, 2);
            $grandTotal = round($subtotal + $vatAmount, 2);

            $order = $this->repo->create([
                'uuid'                  => Str::uuid(),
                'order_no'              => $this->generateOrderNo(),
                'quotation_request_id'  => $quotation->id,
                'quotation_version_id'  => $quotation->latestVersion?->id ?? $this->ensureDummyVersionId($quotation),
                'client_id'             => $quotation->client_id,
                'project_id'            => $quotation->project_id,
                'status'                => 'pending',
                'total_amount'          => $subtotal,
                'vat_amount'            => $vatAmount,
                'grand_total'           => $grandTotal,
                'currency'              => 'SAR',
            ]);

            foreach ($selectedItems as $row) {
                $unitPrice  = (float) ($row['unit_price'] ?? 0);
                $quantity   = (float) ($row['quantity'] ?? 0);
                $totalPrice = round($unitPrice * $quantity, 2);

                OrderItem::create([
                    'uuid'        => Str::uuid(),
                    'order_id'    => $order->id,
                    'product_id'  => $row['product_id'] ?? null,
                    'description' => (string) ($row['description'] ?? ''),
                    'quantity'    => $quantity,
                    'unit_id'     => $row['unit_id'] ?? null,
                    'unit_price'  => $unitPrice,
                    'discount_pct'=> 0,
                    'total_price' => $totalPrice,
                    'vat_rate'    => $vatRate * 100,
                ]);
            }

            return $order;
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function generateOrderNo(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (Order::where('order_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * orders.quotation_version_id is a required FK.
     * If the quotation has no QuotationVersion yet (tender flow bypasses versioning)
     * we create a minimal placeholder version so the FK constraint is satisfied.
     */
    private function ensureDummyVersionId(QuotationRequest $quotation): int
    {
        $version = $quotation->versions()->firstOrCreate(
            ['version_number' => 1],
            [
                'uuid'        => Str::uuid(),
                'prepared_by' => $quotation->client_id,
                'status'      => 'draft',
                'notes'       => 'Auto-created on order submission',
            ]
        );

        return $version->id;
    }
}
