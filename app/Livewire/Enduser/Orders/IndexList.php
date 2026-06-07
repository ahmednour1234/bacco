<?php

namespace App\Livewire\Enduser\Orders;

use App\Enums\EnduserOrderStatusEnum;
use App\Enums\PaymentStatusEnum;
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
            'total'        => (clone $allOrders)->count(),
            'open_unpaid'  => $this->applyEnduserStatusFilter((clone $allOrders), EnduserOrderStatusEnum::OpenUnpaid->value)->count(),
            'under_review' => $this->applyEnduserStatusFilter((clone $allOrders), EnduserOrderStatusEnum::OpenReceiptUnderReview->value)->count(),
            'confirmed'    => $this->applyEnduserStatusFilter((clone $allOrders), EnduserOrderStatusEnum::OpenPaymentConfirmed->value)->count(),
            'closed'       => $this->applyEnduserStatusFilter((clone $allOrders), EnduserOrderStatusEnum::Closed->value)->count(),
        ];

        $query = Order::with([
            'quotationRequest',
            'client.clientProfile',
            'items',
            'payments',
            'latestPayment',
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
            $this->applyEnduserStatusFilter($query, $this->status);
        }

        if ($this->created_from !== '') {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_to !== '') {
            $query->whereDate('created_at', '<=', $this->created_to);
        }

        $orders   = $query->paginate($this->perPage);
        $statuses = EnduserOrderStatusEnum::cases();

        $hasActiveFilters = $this->search !== ''
            || $this->status !== ''
            || $this->created_from !== ''
            || $this->created_to !== '';

        return view('livewire.enduser.orders.index-list', compact('orders', 'stats', 'statuses', 'hasActiveFilters'));
    }

    private function applyEnduserStatusFilter($query, string $status)
    {
        return match (EnduserOrderStatusEnum::tryFrom($status)) {
            EnduserOrderStatusEnum::OpenUnpaid => $query
                ->where('status', 'open')
                ->whereDoesntHave('payments', fn ($q) => $q->where('status', PaymentStatusEnum::Approved->value))
                ->where(function ($q): void {
                    $q->whereDoesntHave('latestPayment')
                        ->orWhereHas('latestPayment', fn ($q2) => $q2->where('status', '!=', PaymentStatusEnum::Submitted->value));
                }),

            EnduserOrderStatusEnum::OpenReceiptUnderReview => $query
                ->where('status', 'open')
                ->whereDoesntHave('payments', fn ($q) => $q->where('status', PaymentStatusEnum::Approved->value))
                ->whereHas('latestPayment', fn ($q) => $q->where('status', PaymentStatusEnum::Submitted->value)),

            EnduserOrderStatusEnum::OpenPaymentConfirmed => $query
                ->where('status', 'open')
                ->whereHas('payments', fn ($q) => $q->where('status', PaymentStatusEnum::Approved->value)),

            EnduserOrderStatusEnum::Closed => $query->where('status', 'closed'),

            default => $query,
        };
    }
}
