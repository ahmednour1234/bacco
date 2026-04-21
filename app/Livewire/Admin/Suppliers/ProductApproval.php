<?php

namespace App\Livewire\Admin\Suppliers;

use App\Enums\NotificationTypeEnum;
use App\Enums\UserTypeEnum;
use App\Models\SupplierProduct;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductApproval extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $status   = 'pending';
    public int    $perPage  = 15;

    // Rejection modal
    public bool    $showRejectModal  = false;
    public ?int    $rejectingId      = null;
    public string  $rejectionReason  = '';

    protected array $allowedPerPage = [15, 25, 50];

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedStatus(): void  { $this->resetPage(); }

    public function approve(int $id): void
    {
        $sp = SupplierProduct::with('product', 'supplier')->findOrFail($id);

        $sp->update([
            'approval_status' => 'approved',
            'approved_at'     => now(),
            'approved_by'     => Auth::id(),
            'rejection_reason'=> null,
        ]);

        // Activate the product
        $sp->product?->update(['active' => true]);

        // Notify the supplier
        app(NotificationService::class)->send(
            title: 'Product Approved',
            body: 'Your product "' . $sp->product->name . '" has been approved by the admin.',
            type: NotificationTypeEnum::ProductApproved,
            recipientIds: [$sp->supplier_id],
            actionUrl: route('supplier.products.index'),
        );
    }

    public function openRejectModal(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'max:500'],
        ]);

        $sp = SupplierProduct::with('product', 'supplier')->findOrFail($this->rejectingId);

        $sp->update([
            'approval_status'  => 'rejected',
            'approved_at'      => now(),
            'approved_by'      => Auth::id(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        // Notify the supplier
        app(NotificationService::class)->send(
            title: 'Product Rejected',
            body: 'Your product "' . $sp->product->name . '" was rejected. Reason: ' . $this->rejectionReason,
            type: NotificationTypeEnum::ProductRejected,
            recipientIds: [$sp->supplier_id],
            actionUrl: route('supplier.products.index'),
        );

        $this->showRejectModal = false;
        $this->rejectingId     = null;
        $this->rejectionReason = '';
    }

    public function setMargin(int $id, float $margin): void
    {
        $sp = SupplierProduct::with('product')->findOrFail($id);
        $sp->product?->update(['margin_percentage' => $margin]);
    }

    public function render()
    {
        $query = SupplierProduct::with(['product', 'supplier', 'approvedBy'])
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', $term))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', $term));
            });
        }

        if (in_array($this->status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $this->status);
        }

        $products = $query->paginate($this->perPage);

        $pendingCount = SupplierProduct::where('approval_status', 'pending')->count();
        $approvedCount = SupplierProduct::where('approval_status', 'approved')->count();
        $rejectedCount = SupplierProduct::where('approval_status', 'rejected')->count();
        $totalCount = SupplierProduct::count();

        return view('livewire.admin.suppliers.product-approval', compact('products', 'pendingCount', 'approvedCount', 'rejectedCount', 'totalCount'));
    }
}
