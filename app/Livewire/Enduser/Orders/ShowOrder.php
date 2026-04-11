<?php

namespace App\Livewire\Enduser\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrder extends Component
{
    public string $uuid = '';
    public ?Order $order = null;
    public array  $items = [];

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->order = Order::with([
            'items.product.brand',
            'items.unit',
            'quotationRequest',
            'client.clientProfile',
            'logisticsUpdates',
            'engineeringUpdates',
        ])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $this->loadItems();
    }

    public function render()
    {
        return view('livewire.enduser.orders.show-order');
    }

    private function loadItems(): void
    {
        $this->items = $this->order
            ->items
            ->map(fn($item) => [
                'id'          => $item->id,
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit?->name ?? '—',
                'brand'       => $item->product?->brand?->name ?? '—',
                'unit_price'  => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'vat_rate'    => (float) $item->vat_rate,
            ])
            ->toArray();
    }
}
