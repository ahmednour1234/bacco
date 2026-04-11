<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\EngineeringStatusEnum;
use App\Enums\LogisticsStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\ActivityLog;
use App\Models\EngineeringUpdate;
use App\Models\LogisticsUpdate;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class ShowOrder extends Component
{
    public string $uuid           = '';
    public ?Order $order          = null;
    public array  $items          = [];
    public array  $steps          = [];
    public array  $statusLogs     = [];
    public string $newStatus      = '';
    public bool   $showStatusModal = false;

    // Engineering
    public array  $engUpdates      = [];
    public bool   $showEngModal    = false;
    public string $engStatus       = 'pending';
    public string $engNotes        = '';
    public ?int   $editingEngId    = null;
    public string $editingEngStatus = '';

    // Logistics
    public array  $logUpdates       = [];
    public bool   $showLogModal     = false;
    public string $logStatus        = 'pending';
    public string $logCarrier       = '';
    public string $logTracking      = '';
    public string $logNotes         = '';
    public ?int   $editingLogId     = null;
    public string $editingLogStatus = '';

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->order = Order::with([
            'items.product.brand',
            'items.unit',
            'quotationRequest',
            'client.clientProfile',
            'assignedEmployee',
            'projects',
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $this->newStatus = $this->order->status->value;
        $this->loadItems();
        $this->buildSteps();
        $this->loadStatusLogs();
        $this->loadEngUpdates();
        $this->loadLogUpdates();
    }

    public function render()
    {
        return view('livewire.admin.orders.show-order');
    }

    public function openStatusModal(): void
    {
        $this->newStatus      = $this->order->status->value;
        $this->showStatusModal = true;
    }

    public function updateStatus(): void
    {
        if (! in_array($this->newStatus, OrderStatusEnum::values())) {
            return;
        }

        $old = $this->order->status->value;

        if ($old === $this->newStatus) {
            $this->showStatusModal = false;
            return;
        }

        $this->order->update(['status' => $this->newStatus]);

        ActivityLog::create([
            'uuid'          => (string) Str::uuid(),
            'loggable_type' => Order::class,
            'loggable_id'   => $this->order->id,
            'user_id'       => Auth::id(),
            'action'        => 'status_changed',
            'description'   => "Status changed from {$old} to {$this->newStatus}",
            'old_values'    => ['status' => $old],
            'new_values'    => ['status' => $this->newStatus],
        ]);

        $this->order->refresh();
        $this->buildSteps();
        $this->loadStatusLogs();
        $this->showStatusModal = false;
        $this->dispatch('toast', message: 'Order status updated.', type: 'success');
    }

    public function openEngModal(): void
    {
        $this->engStatus    = 'pending';
        $this->engNotes     = '';
        $this->showEngModal = true;
    }

    public function saveEngUpdate(): void
    {
        $this->validate([
            'engStatus' => 'required|in:' . implode(',', EngineeringStatusEnum::values()),
            'engNotes'  => 'nullable|string|max:2000',
        ]);

        EngineeringUpdate::create([
            'uuid'       => (string) Str::uuid(),
            'order_id'   => $this->order->id,
            'updated_by' => Auth::id(),
            'status'     => $this->engStatus,
            'notes'      => $this->engNotes ?: null,
        ]);

        $this->showEngModal = false;
        $this->loadEngUpdates();
        $this->dispatch('toast', message: 'Engineering update added.', type: 'success');
    }

    public function deleteEngUpdate(int $id): void
    {
        EngineeringUpdate::where('id', $id)->delete();
        $this->loadEngUpdates();
        $this->dispatch('toast', message: 'Engineering update deleted.', type: 'success');
    }

    public function startEditEng(int $id, string $currentStatus): void
    {
        $this->editingEngId     = $id;
        $this->editingEngStatus = $currentStatus;
    }

    public function cancelEditEng(): void
    {
        $this->editingEngId     = null;
        $this->editingEngStatus = '';
    }

    public function updateEngStatus(): void
    {
        if (! $this->editingEngId || ! in_array($this->editingEngStatus, EngineeringStatusEnum::values())) {
            return;
        }

        EngineeringUpdate::where('id', $this->editingEngId)->update(['status' => $this->editingEngStatus]);
        $this->editingEngId     = null;
        $this->editingEngStatus = '';
        $this->loadEngUpdates();
        $this->dispatch('toast', message: 'Engineering status updated.', type: 'success');
    }

    // -------------------------------------------------------------------------

    public function openLogModal(): void
    {
        $this->logStatus   = 'pending';
        $this->logCarrier  = '';
        $this->logTracking = '';
        $this->logNotes    = '';
        $this->showLogModal = true;
    }

    public function saveLogUpdate(): void
    {
        $this->validate([
            'logStatus'   => 'required|in:' . implode(',', LogisticsStatusEnum::values()),
            'logCarrier'  => 'nullable|string|max:255',
            'logTracking' => 'nullable|string|max:255',
            'logNotes'    => 'nullable|string|max:2000',
        ]);

        LogisticsUpdate::create([
            'uuid'             => (string) Str::uuid(),
            'order_id'         => $this->order->id,
            'updated_by'       => Auth::id(),
            'status'           => $this->logStatus,
            'carrier'          => $this->logCarrier ?: null,
            'tracking_number'  => $this->logTracking ?: null,
            'notes'            => $this->logNotes ?: null,
        ]);

        $this->showLogModal = false;
        $this->loadLogUpdates();
        $this->dispatch('toast', message: 'Logistics update added.', type: 'success');
    }

    public function deleteLogUpdate(int $id): void
    {
        LogisticsUpdate::where('id', $id)->delete();
        $this->loadLogUpdates();
        $this->dispatch('toast', message: 'Logistics update deleted.', type: 'success');
    }

    public function startEditLog(int $id, string $currentStatus): void
    {
        $this->editingLogId     = $id;
        $this->editingLogStatus = $currentStatus;
    }

    public function cancelEditLog(): void
    {
        $this->editingLogId     = null;
        $this->editingLogStatus = '';
    }

    public function updateLogStatus(): void
    {
        if (! $this->editingLogId || ! in_array($this->editingLogStatus, LogisticsStatusEnum::values())) {
            return;
        }

        LogisticsUpdate::where('id', $this->editingLogId)->update(['status' => $this->editingLogStatus]);
        $this->editingLogId     = null;
        $this->editingLogStatus = '';
        $this->loadLogUpdates();
        $this->dispatch('toast', message: 'Logistics status updated.', type: 'success');
    }

    // -------------------------------------------------------------------------

    private function loadEngUpdates(): void
    {
        $this->engUpdates = EngineeringUpdate::with('updatedBy')
            ->where('order_id', $this->order->id)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id'     => $u->id,
                'status' => $u->status->value,
                'label'  => $u->status->label(),
                'notes'  => $u->notes ?? '',
                'user'   => $u->updatedBy?->name ?? 'System',
                'date'   => $u->created_at->format('d M Y, H:i'),
            ])
            ->toArray();
    }

    private function loadLogUpdates(): void
    {
        $this->logUpdates = LogisticsUpdate::with('updatedBy')
            ->where('order_id', $this->order->id)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id'       => $u->id,
                'status'   => $u->status->value,
                'label'    => $u->status->label(),
                'carrier'  => $u->carrier ?? '—',
                'tracking' => $u->tracking_number ?? '—',
                'notes'    => $u->notes ?? '',
                'user'     => $u->updatedBy?->name ?? 'System',
                'date'     => $u->created_at->format('d M Y, H:i'),
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------

    private function loadItems(): void
    {
        $this->items = $this->order
            ->items
            ->map(fn ($item) => [
                'id'          => $item->id,
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit?->name ?? '—',
                'brand'       => $item->product?->brand?->name ?? '—',
                'unit_price'  => (float) $item->unit_price,
                'discount'    => (float) $item->discount_pct,
                'total_price' => (float) $item->total_price,
                'vat_rate'    => (float) $item->vat_rate,
            ])
            ->toArray();
    }

    private function loadStatusLogs(): void
    {
        $this->statusLogs = ActivityLog::with('user')
            ->where('loggable_type', Order::class)
            ->where('loggable_id', $this->order->id)
            ->where('action', 'status_changed')
            ->latest()
            ->get()
            ->map(fn ($log) => [
                'old'  => $log->old_values['status'] ?? '—',
                'new'  => $log->new_values['status'] ?? '—',
                'user' => $log->user?->name ?? 'System',
                'date' => $log->created_at->format('d M Y, H:i'),
            ])
            ->toArray();
    }

    private function buildSteps(): void
    {
        $status = $this->order->status->value;

        // Ordered progression of statuses
        $progression = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $currentIdx  = array_search($status, $progression);

        $defs = [
            ['label' => 'Payment Approved'],
            ['label' => 'Profile Completed'],
            ['label' => 'Engineering Updates'],
            ['label' => 'Logistics'],
            ['label' => 'Delivered'],
            ['label' => 'Closed'],
        ];

        $this->steps = [];
        foreach ($defs as $i => $def) {
            if (in_array($status, ['completed', 'cancelled', 'refunded'])) {
                // All steps done when order is fully closed
                $state = 'completed';
            } elseif ($currentIdx === false) {
                $state = $i === 0 ? 'in_progress' : 'pending';
            } elseif ($i < $currentIdx) {
                $state = 'completed';
            } elseif ($i === $currentIdx) {
                $state = 'in_progress';
            } else {
                $state = 'pending';
            }

            $this->steps[] = array_merge($def, ['state' => $state]);
        }
    }
}
