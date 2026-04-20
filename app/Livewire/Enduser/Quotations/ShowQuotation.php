<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationRequestStatusEnum;
use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Enums\NotificationTypeEnum;
use App\Enums\UserTypeEnum;
use App\Repositories\Enduser\OrderRepository;
use App\Services\Enduser\OrderService;
use App\Services\NotificationService;
use App\Services\PricingService;
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

    // Product picker state
    public ?int   $openPickerItemId = null;
    public string $productSearch    = '';
    public array  $productResults   = [];

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->quotation = QuotationRequest::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $this->loadItems();

        // Auto-trigger pricing if any items still lack a unit price
        $this->fetchingPrices = collect($this->items)->contains(fn($i) => empty($i['unit_price']));
    }

    // -------------------------------------------------------------------------
    // Pricing
    // -------------------------------------------------------------------------

    /**
     * Called automatically from the blade (x-init) when fetchingPrices is true.
     * Looks up the products table first; falls back to Gemini for unmatched items.
     * Persists prices to the DB so subsequent page loads are instant.
     */
    public function fetchPrices(): void
    {
        if (! $this->fetchingPrices) {
            return;
        }

        try {
            $priced = app(PricingService::class)->fetchPrices($this->items);

            // Persist back to DB
            foreach ($priced as $index => $row) {
                if (! empty($row['id']) && isset($row['unit_price']) && $row['unit_price'] > 0) {
                    QuotationItem::where('id', $row['id'])->update([
                        'unit_price'   => $row['unit_price'],
                        'price_source' => $row['price_source'] ?? null,
                        'price_status' => 'pending',
                    ]);
                }
            }

            $this->items = $priced;

            $gotPrices = collect($priced)->filter(fn($i) => ! empty($i['unit_price']))->count();
            $missing   = count($priced) - $gotPrices;

            if ($missing > 0) {
                $this->dispatch('toast', message: "{$gotPrices} item(s) priced. {$missing} item(s) could not be priced.", type: 'warning');
            } else {
                $this->dispatch('toast', message: "All {$gotPrices} item(s) priced successfully.", type: 'success');
            }

        } catch (\Throwable $e) {
            Log::error('ShowQuotation::fetchPrices failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Pricing fetch failed. Please try again.', type: 'error');
        } finally {
            $this->fetchingPrices = false;
        }
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

    public function toggleSelected(int $itemId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $newState = ! ($item['selected'] ?? false);
                $this->items[$index]['selected'] = $newState;
                QuotationItem::where('id', $itemId)->update(['is_selected' => $newState]);
                break;
            }
        }
    }

    public function updateQuantity(int $itemId, string $newQty): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $qty = filter_var($newQty, FILTER_VALIDATE_FLOAT);

        if ($qty === false || $qty <= 0) {
            $this->dispatch('toast', message: 'Quantity must be a positive number.', type: 'error');
            return;
        }

        QuotationItem::where('id', $itemId)->update(['quantity' => $qty]);

        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $this->items[$index]['quantity'] = $qty;
                break;
            }
        }
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
        if (! $this->canEdit()) {
            return;
        }

        // Reset prices first so PricingService treats them as unpriced
        $ids = array_column($this->items, 'id');
        QuotationItem::whereIn('id', $ids)->update([
            'unit_price'   => null,
            'price_source' => null,
        ]);

        foreach ($this->items as $index => $_) {
            $this->items[$index]['unit_price']   = null;
            $this->items[$index]['price_source'] = null;
        }

        $this->fetchingPrices = true;
        $this->dispatch('refetchPrices');
    }

    private function canEdit(): bool
    {
        return $this->quotation && in_array(
            $this->quotation->status->value,
            ['tender', 'draft'],
            true
        );
    }

    // -------------------------------------------------------------------------
    // Submit for approval → create order → redirect
    // -------------------------------------------------------------------------

    public function submitForApproval(): void
    {
        if (! $this->quotation) {
            return;
        }

        try {
            $selectedItems = array_values(array_filter($this->items, fn($i) => $i['selected'] ?? false));

            if (empty($selectedItems)) {
                $this->dispatch('toast', message: 'Please select at least one item before submitting.', type: 'error');
                return;
            }

            $subtotal = collect($selectedItems)
                ->filter(fn($i) => ($i['status'] ?? '') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
                ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));

            if ($subtotal <= 0) {
                $this->dispatch('toast', message: 'Total amount must be greater than 0 before submitting.', type: 'error');
                return;
            }

            $order = app(OrderService::class)->createFromQuotation($this->quotation, $selectedItems);

            $this->quotation->update(['status' => QuotationRequestStatusEnum::Submitted]);

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
            $this->dispatch('toast', message: 'Failed to submit quotation. Please try again.', type: 'error');
        }
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
