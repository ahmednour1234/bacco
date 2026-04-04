<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatusEnum;
use App\Enums\QuotationProjectStatusEnum;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $status    = '';
    public string $type      = '';   // project_status from quotation_request
    public string $dateRange = '30'; // last N days: 7, 30, 90, or '' = all time
    public int    $perPage   = 10;

    public function updating($name): void
    {
        if ($name !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search    = '';
        $this->status    = '';
        $this->type      = '';
        $this->dateRange = '30';
        $this->resetPage();
    }

    public function render()
    {
        $allOrders = Order::query();

        $stats = [
            'total'       => (clone $allOrders)->count(),
            'engineering' => (clone $allOrders)->whereHas('projects.engineeringUpdates')->count(),
            'waiting'     => (clone $allOrders)->whereIn('status', ['pending', 'confirmed'])->count(),
            'logistics'   => (clone $allOrders)->whereHas('projects.logisticsUpdates')->count(),
            'delivered'   => (clone $allOrders)->where('status', 'delivered')->count(),
            'closed'      => (clone $allOrders)->whereIn('status', ['completed', 'cancelled', 'refunded'])->count(),
        ];

        $query = Order::with([
            'quotationRequest',
            'client.clientProfile',
            'items',
        ])->latest();

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s): void {
                $q->where('order_no', 'like', '%' . $s . '%')
                  ->orWhereHas('quotationRequest', fn($q2) => $q2->where('project_name', 'like', '%' . $s . '%'))
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', '%' . $s . '%'))
                  ->orWhereHas('client.clientProfile', fn($q2) => $q2->where('company_name', 'like', '%' . $s . '%'));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->type !== '') {
            $query->whereHas('quotationRequest', fn($q) => $q->where('project_status', $this->type));
        }

        if ($this->dateRange !== '') {
            $query->whereDate('created_at', '>=', now()->subDays((int) $this->dateRange)->toDateString());
        }

        $orders   = $query->paginate($this->perPage);
        $statuses = OrderStatusEnum::cases();
        $types    = QuotationProjectStatusEnum::cases();

        $hasActiveFilters = $this->search !== ''
            || $this->status !== ''
            || $this->type !== ''
            || $this->dateRange !== '30';

        return view('livewire.admin.orders.index-list', compact(
            'orders', 'stats', 'statuses', 'types', 'hasActiveFilters'
        ));
    }
}
