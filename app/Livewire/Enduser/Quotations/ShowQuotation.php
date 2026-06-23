<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationRequestStatusEnum;
use App\Jobs\FetchQuotationPricesJob;
use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Enums\NotificationTypeEnum;
use App\Enums\UserTypeEnum;
use App\Services\Catalog\SaveQuotationProductsToCatalog;
use App\Services\Enduser\OrderService;
use App\Services\NotificationService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowQuotation extends Component
{
    public string $uuid = '';

    public ?QuotationRequest $quotation = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $fetchingPrices = false;

    public int $initialStep = 3;

    /** True while a background pricing job has been dispatched for this session. */
    public bool $pricingQueued = false;

    /** ISO-8601 timestamp of when the pricing job was dispatched (used by polling). */
    public ?string $pricingJobStartedAt = null;

    /** True when the quotation's prices are older than the allowed validity period. */
    public bool $isExpired = false;

    // Product picker state
    public ?int   $openPickerItemId = null;
    public string $productSearch    = '';
    public array  $productResults   = [];

    // ── Address modal ──────────────────────────────────────────────────────────
    public bool   $showAddressModal    = false;
    public string $addressType         = 'detailed'; // 'detailed' | 'national'

    // Detailed address
    public string $deliveryStreet      = '';
    public string $deliveryDistrict    = '';
    public string $deliveryCity        = '';
    public string $deliveryRegion      = '';
    public string $deliveryPostalCode  = '';
    public string $deliveryCountry     = 'SA';

    // National address (Saudi National Address)
    public string $nationalBuildingNo   = '';
    public string $nationalStreet       = '';
    public string $nationalDistrict     = '';
    public string $nationalCity         = '';
    public string $nationalPostalCode   = '';
    public string $nationalAdditionalNo = '';


    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->quotation = QuotationRequest::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $this->loadItems();

        $this->isExpired = $this->quotation->isExpired();

        // Restore the step the user should land on (flashed by create/submit flow)
        $this->initialStep = (int) session('quotation_initial_step', 3);

        // Auto-trigger pricing if any items still lack a unit price (and not expired)
        $needsPricing = ! $this->isExpired && collect($this->items)->contains(
            fn($i) => ! empty($i['selected']) && ($i['status'] ?? '') !== 'rejected' && empty($i['unit_price'])
        );

        if ($needsPricing) {
            $this->pricingQueued = true;
        }
    }

    // -------------------------------------------------------------------------
    // Pricing
    // -------------------------------------------------------------------------

    /**
     * Called via wire:init after first render — dispatches the pricing job asynchronously.
     */
    public function fetchPricesOnInit(): void
    {
        if (! $this->pricingQueued || ! $this->quotation) {
            return;
        }

        $this->pricingJobStartedAt = now()->toIso8601String();
        FetchQuotationPricesJob::dispatch($this->quotation->id, Auth::id(), $this->uuid);
    }

    /**
     * Called by wire:poll — checks if the background pricing job has finished.
     */
    public function checkPricingStatus(): void
    {
        if (! $this->pricingQueued || ! $this->pricingJobStartedAt || ! $this->quotation) {
            return;
        }

        $fresh = QuotationRequest::find($this->quotation->id);
        if (! $fresh) {
            return;
        }

        $startedAt = Carbon::parse($this->pricingJobStartedAt);

        // The job updates prices_fetched_at in its finally block when done
        if ($fresh->prices_fetched_at && $fresh->prices_fetched_at->isAfter($startedAt)) {
            $this->quotation           = $fresh;
            $this->loadItems();
            $this->isExpired           = false;
            $this->pricingQueued       = false;
            $this->pricingJobStartedAt = null;

            $pricedItems = collect($this->items)
                ->filter(fn($i) => ! empty($i['selected']) && ($i['status'] ?? '') !== 'rejected');
            $gotPrices = $pricedItems->filter(fn($i) => ! empty($i['unit_price']))->count();
            $total     = $pricedItems->count();
            $missing   = $total - $gotPrices;
            $message   = $missing > 0
                ? "تم تسعير {$gotPrices} عنصر. {$missing} عنصر لم يُسعَّر تلقائياً."
                : "تم تسعير جميع {$gotPrices} عنصر بنجاح.";

            $this->dispatch('toast', message: $message, type: 'success');
        }
    }

    public function dismissPricingBanner(): void
    {
        $this->pricingQueued = false;
    }

    // -------------------------------------------------------------------------
    // Product picker
    // -------------------------------------------------------------------------

    public function openPicker(int $itemId): void
    {
        $this->openPickerItemId = $itemId;
        $this->productSearch    = '';
        $this->productResults   = [];
    }

    public function closePicker(): void
    {
        $this->openPickerItemId = null;
        $this->productSearch    = '';
        $this->productResults   = [];
    }

    public function updatedProductSearch(): void
    {
        if (strlen(trim($this->productSearch)) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Product::with('unit')
            ->where('active', true)
            ->where('name', 'like', '%' . $this->productSearch . '%')
            ->limit(10)
            ->get()
            ->map(fn(Product $p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'unit_price' => (float) $p->unit_price,
                'unit_name'  => $p->unit?->name ?? '',
                'unit_id'    => $p->unit_id,
            ])
            ->toArray();
    }

    public function selectProduct(int $itemId, int $productId): void
    {
        $product = Product::with('unit')->find($productId);
        if (! $product) {
            return;
        }

        QuotationItem::where('id', $itemId)->update([
            'product_id'   => $productId,
            'unit_price'   => $product->unit_price,
            'unit_id'      => $product->unit_id,
            'price_source' => 'products',
            'price_status' => 'pending',
            'is_selected'  => true,
        ]);

        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $this->items[$index]['unit_price']   = (float) $product->unit_price;
                $this->items[$index]['unit']         = $product->unit?->name ?? $item['unit'];
                $this->items[$index]['unit_id']      = $product->unit_id;
                $this->items[$index]['product_id']   = $productId;
                $this->items[$index]['price_source'] = 'products';
                $this->items[$index]['selected']     = true;
                break;
            }
        }

        $this->closePicker();
        $this->dispatch('toast', message: 'Product selected. Price updated.', type: 'success');
    }



    // -------------------------------------------------------------------------
    // Edit actions (only while not yet Submitted)
    // -------------------------------------------------------------------------

    public function removeProduct(int $itemId): void
    {
        if (! $this->canEdit()) {
            return;
        }

        QuotationItem::where('id', $itemId)->update([
            'product_id'   => null,
            'unit_price'   => null,
            'price_source' => null,
            'price_status' => 'pending',
            'is_selected'  => false,
        ]);

        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $this->items[$index]['product_id']   = null;
                $this->items[$index]['unit_price']   = null;
                $this->items[$index]['price_source'] = null;
                $this->items[$index]['selected']     = false;
                break;
            }
        }

        $this->dispatch('toast', message: 'Product removed from item.', type: 'warning');
    }

    public function removeAllProducts(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $ids = array_column($this->items, 'id');

        QuotationItem::whereIn('id', $ids)->update([
            'product_id'   => null,
            'unit_price'   => null,
            'price_source' => null,
            'price_status' => 'pending',
            'is_selected'  => false,
        ]);

        foreach ($this->items as $index => $_) {
            $this->items[$index]['product_id']   = null;
            $this->items[$index]['unit_price']   = null;
            $this->items[$index]['price_source'] = null;
            $this->items[$index]['selected']     = false;
        }

        $this->dispatch('toast', message: 'All product selections cleared.', type: 'warning');
    }

    public function refetchPrices(): void
    {
        // Allow re-fetch even when expired — that's how the user renews the quotation
        if (! $this->quotation || ! in_array($this->quotation->status->value, ['tender', 'draft'], true)) {
            return;
        }

        $ids = collect($this->items)
            ->filter(fn($i) => ! empty($i['selected']) && ($i['status'] ?? '') !== 'rejected')
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids)) {
            $this->dispatch('toast', message: __('app.select_item_pricing_required'), type: 'error');
            return;
        }

        // Reset selected prices first so PricingService treats them as unpriced.
        QuotationItem::whereIn('id', $ids)->update([
            'unit_price'   => null,
            'price_source' => null,
            'price_status' => 'pending',
        ]);

        foreach ($this->items as $index => $item) {
            if (! empty($item['selected']) && ($item['status'] ?? '') !== 'rejected') {
                $this->items[$index]['unit_price']   = null;
                $this->items[$index]['price_source'] = null;
            }
        }

        $this->pricingQueued       = true;
        $this->pricingJobStartedAt = now()->toIso8601String();
        FetchQuotationPricesJob::dispatch($this->quotation->id, Auth::id(), $this->uuid);
    }

    private function runPricingSync(): void
    {
        set_time_limit(300); // Prevent 60-second fatal timeout during AI price fetching
        try {
            $dbItems = QuotationItem::where('quotation_request_id', $this->quotation->id)
                ->where('is_selected', true)
                ->where('status', '!=', 'rejected')
                ->get()
                ->map(fn(QuotationItem $item) => [
                    'id'           => $item->id,
                    'description'  => (string) $item->description,
                    'quantity'     => (float) $item->quantity,
                    'category'     => (string) ($item->category ?? ''),
                    'brand'        => (string) ($item->brand ?? ''),
                    'unit_price'   => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                    'price_source' => $item->price_source,
                ])
                ->toArray();

            $priced    = app(PricingService::class)->fetchPrices($dbItems);
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

            // Stamp prices_fetched_at to reset the 10-day expiry clock
            $this->quotation->update(['prices_fetched_at' => now()]);
            $this->isExpired = false;

            $this->loadItems();
            $this->pricingQueued = false;

            $total   = count($priced);
            $missing = $total - $gotPrices;
            $message = $missing > 0
                ? "تم تسعير {$gotPrices} عنصر. {$missing} عنصر لم يُسعَّر تلقائياً."
                : "تم تسعير جميع {$gotPrices} عنصر بنجاح.";

            $this->dispatch('toast', message: $message, type: 'success');

        } catch (\Throwable $e) {
            Log::error('ShowQuotation::runPricingSync failed.', ['message' => $e->getMessage()]);
            $this->pricingQueued = false;
            $this->dispatch('toast', message: 'فشل جلب الأسعار. يرجى المحاولة مرة أخرى.', type: 'error');
        }
    }

    private function canEdit(): bool
    {
        return $this->quotation
            && ! $this->isExpired
            && in_array($this->quotation->status->value, ['tender', 'draft'], true);
    }

    // -------------------------------------------------------------------------
    // Submit for approval → create order → redirect
    // -------------------------------------------------------------------------

    public function openAddressModal(): void
    {
        if (! $this->quotation) {
            return;
        }

        if ($this->isExpired) {
            $this->dispatch('toast', message: __('app.expired_block_msg'), type: 'error');
            return;
        }

        $subtotal = collect(array_values($this->items))
            ->filter(fn($i) => ! empty($i['selected']) && ($i['status'] ?? '') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
            ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));

        if ($subtotal <= 0) {
            $this->dispatch('toast', message: __('app.total_must_be_positive'), type: 'error');
            return;
        }

        $this->showAddressModal = true;
    }

    public function submitForApproval(): void
    {
        if (! $this->quotation) {
            return;
        }

        if ($this->isExpired) {
            $this->dispatch('toast', message: __('app.expired_block_msg'), type: 'error');
            return;
        }

        if (! $this->validateAddress()) {
            return;
        }

        try {
            $selectedItems = collect($this->items)
                ->filter(fn($i) => ! empty($i['selected']) && ($i['status'] ?? '') !== 'rejected')
                ->values()
                ->all();

            $subtotal = collect($selectedItems)
                ->filter(fn($i) => is_numeric($i['unit_price'] ?? null))
                ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));

            if ($subtotal <= 0) {
                $this->dispatch('toast', message: __('app.all_items_zero_price'), type: 'error');
                return;
            }

            $order = app(OrderService::class)->createFromQuotation(
                $this->quotation,
                $selectedItems,
                $this->buildAddressData(),
            );

            $this->quotation->update(['status' => QuotationRequestStatusEnum::Submitted]);

            // Keep a record of the quotation's (now priced) products in the
            // catalog. The catalog is on a separate DB connection, so a failure
            // here must not abort the submission — log and continue.
            try {
                app(SaveQuotationProductsToCatalog::class)->handle($this->quotation, $selectedItems);
            } catch (\Throwable $e) {
                Log::error('Failed to save quotation products to catalog', [
                    'quotation_id' => $this->quotation->id,
                    'error'        => $e->getMessage(),
                ]);
            }

            // Notify all admins about the new order
            app(NotificationService::class)->sendToUserType(
                title: 'New Order Created',
                body: 'Order ' . $order->order_no . ' was placed by ' . (Auth::user()->name ?? 'a client') . ' — total: ' . number_format($order->grand_total, 2) . ' SAR.',
                type: NotificationTypeEnum::OrderCreated,
                userType: UserTypeEnum::Admin,
                actionUrl: route('admin.orders.show', $order->uuid),
            );

            $this->redirect(route('enduser.orders.show', $order->uuid));

        } catch (\Throwable $e) {
            Log::error('ShowQuotation::submitForApproval failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: __('app.order_create_failed'), type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // Address helpers (shared with IndexList pattern)
    // -------------------------------------------------------------------------

    private function validateAddress(): bool
    {
        if ($this->addressType === 'national') {
            if (! $this->nationalBuildingNo || ! $this->nationalStreet || ! $this->nationalDistrict || ! $this->nationalCity) {
                $this->dispatch('toast', message: __('app.address_fields_required'), type: 'error');
                return false;
            }
        } else {
            if (! $this->deliveryStreet || ! $this->deliveryCity) {
                $this->dispatch('toast', message: __('app.address_fields_required'), type: 'error');
                return false;
            }
        }
        return true;
    }

    private function buildAddressData(): array
    {
        if ($this->addressType === 'national') {
            return [
                'delivery_address_type' => 'national',
                'delivery_building_no'  => $this->nationalBuildingNo,
                'delivery_street'       => $this->nationalStreet,
                'delivery_district'     => $this->nationalDistrict,
                'delivery_city'         => $this->nationalCity,
                'delivery_postal_code'  => $this->nationalPostalCode,
                'delivery_additional_no'=> $this->nationalAdditionalNo,
                'delivery_country'      => 'SA',
            ];
        }

        return [
            'delivery_address_type' => 'detailed',
            'delivery_street'       => $this->deliveryStreet,
            'delivery_district'     => $this->deliveryDistrict,
            'delivery_city'         => $this->deliveryCity,
            'delivery_region'       => $this->deliveryRegion,
            'delivery_postal_code'  => $this->deliveryPostalCode,
            'delivery_country'      => $this->deliveryCountry ?: 'SA',
        ];
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.enduser.quotations.show-quotation');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function loadItems(): void
    {
        $this->items = $this->quotation
            ->items()
            ->with('product')
            ->get()
            ->map(fn(QuotationItem $item) => [
                'id'                   => $item->id,
                'description'          => (string) $item->description,
                'quantity'             => (float) $item->quantity,
                'unit'                 => $item->unit?->name ?? '',
                'unit_id'              => $item->unit_id,
                'product_id'           => $item->product_id,
                'category'             => (string) ($item->category ?? ''),
                'brand'                => (string) ($item->brand ?? ''),
                'status'               => $item->status->value ?? 'pending',
                'engineering_required' => (bool) $item->engineering_required,
                'unit_price'           => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'price_source'         => $item->price_source,
                'price_status'         => $item->price_status ?? 'pending',
                'selected'             => (bool) $item->is_selected,
                'product_name'         => $item->product?->name ?? null,
            ])
            ->toArray();
    }
}
