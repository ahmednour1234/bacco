<?php

namespace App\Livewire\Enduser\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $status       = '';
    public string $created_from = '';
    public string $created_to   = '';
    public int    $perPage      = 5;

    protected array $allowedPerPage = [5, 10, 25, 50];

    public function updating($name): void
    {
        if ($name !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->status       = '';
        $this->created_from = '';
        $this->created_to   = '';
        $this->perPage      = 5;
        $this->resetPage();
    }

    public function render()
    {
        $clientId = Auth::id();

        $allOrders = Order::query()->where('client_id', $clientId);

        $stats = [
            'total'     => (clone $allOrders)->count(),
            'active'    => (clone $allOrders)->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count(),
            'completed' => (clone $allOrders)->whereIn('status', ['delivered', 'completed'])->count(),
            'closed'    => (clone $allOrders)->whereIn('status', ['cancelled', 'refunded'])->count(),
        ];

        $query = Order::with([
            'quotationRequest',
            'client.clientProfile',
            'items',
        ])
            ->where('client_id', $clientId)
            ->latest();

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s): void {
                $q->where('order_no', 'like', '%' . $s . '%')
                  ->orWhereHas('quotationRequest', fn($q2) => $q2->where('project_name', 'like', '%' . $s . '%'));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->created_from !== '') {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_to !== '') {
            $query->whereDate('created_at', '<=', $this->created_to);
        }

        $orders   = $query->paginate($this->perPage);
        $statuses = OrderStatusEnum::cases();

        $hasActiveFilters = $this->search !== ''
            || $this->status !== ''
            || $this->created_from !== ''
            || $this->created_to !== '';

        return view('livewire.enduser.orders.index-list', compact('orders', 'stats', 'statuses', 'hasActiveFilters'));
    }
}
