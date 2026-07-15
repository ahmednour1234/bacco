<?php

namespace App\Livewire\Admin\Quotations;

use App\Jobs\FetchQuotationPricesJob;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowQuotation extends Component
{
    public string $uuid = '';

    public ?QuotationRequest $quotation = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $repricing = false;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->quotation = QuotationRequest::with([
            'client.clientProfile',
        ])->where('uuid', $uuid)->firstOrFail();

        $this->loadItems();
    }

    public function render()
    {
        return view('livewire.admin.quotations.show-quotation');
    }

    public function repriceMissingItems(): void
    {
        if (! $this->quotation) {
            return;
        }

        $missingCount = $this->missingPriceCount();
        if ($missingCount === 0) {
            $this->dispatch('toast', message: 'All selected items already have prices.', type: 'success');
            return;
        }

        $this->repricing = true;

        try {
            FetchQuotationPricesJob::dispatchSync(
                $this->quotation->id,
                $this->quotation->client_id,
                $this->quotation->uuid,
            );

            $this->quotation->refresh();
            $this->loadItems();

            $remaining = $this->missingPriceCount();
            $message = $remaining > 0
                ? "Repricing finished, but {$remaining} selected item(s) still have no price."
                : 'Repricing finished. All selected items are priced.';

            $this->dispatch('toast', message: $message, type: $remaining > 0 ? 'warning' : 'success');
        } catch (\Throwable $e) {
            Log::error('Admin ShowQuotation::repriceMissingItems failed.', [
                'quotation_id' => $this->quotation->id,
                'message'      => $e->getMessage(),
            ]);
            $this->dispatch('toast', message: 'Repricing failed. Please try again.', type: 'error');
        } finally {
            $this->repricing = false;
        }
    }

    public function missingPriceCount(): int
    {
        return collect($this->items)
            ->filter(fn($item) => ($item['selected'] ?? false)
                && ($item['status'] ?? '') !== 'rejected'
                && (! is_numeric($item['unit_price'] ?? null) || (float) ($item['unit_price'] ?? 0) <= 0))
            ->count();
    }

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
                'selected'             => (bool) $item->is_selected,
                'product_name'         => $item->product?->name ?? null,
                'validation_status'    => $item->validation_status,
                'suggested_unit'       => $item->suggested_unit,
                'validation_note'      => $item->validation_note,
            ])
            ->toArray();
    }
}
