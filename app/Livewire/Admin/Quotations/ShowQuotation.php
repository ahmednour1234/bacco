<?php

namespace App\Livewire\Admin\Quotations;

use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Livewire\Component;

class ShowQuotation extends Component
{
    public string $uuid = '';

    public ?QuotationRequest $quotation = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

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
            ])
            ->toArray();
    }
}
