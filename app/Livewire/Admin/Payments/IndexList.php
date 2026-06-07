<?php

namespace App\Livewire\Admin\Payments;

use App\Enums\NotificationTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Review modal
    public bool $showModal = false;
    public ?Payment $reviewing = null;
    public string $rejectReason = '';

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function openReview(int $paymentId): void
    {
        $this->reviewing = Payment::with(['order.client', 'uploadedDocuments', 'client'])
            ->findOrFail($paymentId);
        $this->rejectReason = '';
        $this->showModal = true;
    }

    public function approve(): void
    {
        if (! $this->reviewing) return;

        $this->reviewing->update([
            'status'      => PaymentStatusEnum::Approved->value,
            'reviewed_by' => Auth::id(),
        ]);

        $ar = app()->getLocale() === 'ar';

        app(NotificationService::class)->send(
            title: $ar ? 'تم تأكيد دفعتك' : 'Payment Confirmed',
            body: $ar
                ? "تم قبول إيصال التحويل الخاص بالطلب رقم {$this->reviewing->order->order_no}"
                : "Your bank transfer for order {$this->reviewing->order->order_no} has been confirmed.",
            type: NotificationTypeEnum::PaymentApproved,
            recipientIds: [$this->reviewing->client_id],
            actionUrl: route('enduser.payments.index'),
        );

        $this->showModal = false;
        $this->reviewing = null;
        $this->dispatch('toast', message: $ar ? 'تم قبول الدفعة' : 'Payment approved', type: 'success');
    }

    public function reject(): void
    {
        if (! $this->reviewing) return;

        $this->validate(['rejectReason' => ['required', 'string', 'min:5']]);

        $this->reviewing->update([
            'status'      => PaymentStatusEnum::Rejected->value,
            'reviewed_by' => Auth::id(),
            'notes'       => $this->rejectReason,
        ]);

        $ar = app()->getLocale() === 'ar';

        app(NotificationService::class)->send(
            title: $ar ? 'تم رفض إيصال الدفع' : 'Payment Receipt Rejected',
            body: $ar
                ? "تم رفض إيصال التحويل للطلب {$this->reviewing->order->order_no}. السبب: {$this->rejectReason}"
                : "Your transfer receipt for order {$this->reviewing->order->order_no} was rejected. Reason: {$this->rejectReason}",
            type: NotificationTypeEnum::PaymentRejected,
            recipientIds: [$this->reviewing->client_id],
            actionUrl: route('enduser.payments.index'),
        );

        $this->showModal = false;
        $this->reviewing = null;
        $this->dispatch('toast', message: $ar ? 'تم رفض الدفعة' : 'Payment rejected', type: 'error');
    }

    public function render()
    {
        $query = Payment::with(['order', 'client', 'uploadedDocuments', 'reviewer'])
            ->latest();

        if ($this->search) {
            $query->whereHas('order', fn($q) => $q->where('order_no', 'like', "%{$this->search}%"))
                  ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('reference_number', 'like', "%{$this->search}%");
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $payments = $query->paginate(20);

        $pendingCount = Payment::where('status', PaymentStatusEnum::Submitted->value)->count();

        return view('livewire.admin.payments.index-list', compact('payments', 'pendingCount'));
    }
}
