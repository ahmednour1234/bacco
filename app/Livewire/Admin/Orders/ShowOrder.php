<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\EngineeringStatusEnum;
use App\Enums\LogisticsStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Models\ActivityLog;
use App\Models\EngineeringUpdate;
use App\Models\LogisticsUpdate;
use App\Models\Order;
use App\Services\NotificationService;
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
    public array  $engUpdates       = [];
    public bool   $showEngModal     = false;
    public string $engStatus        = 'pending';
    public string $engNotes         = '';
    public ?int   $engOrderItemId   = null;
    public string $engOrderItemDesc = '';
    public ?int   $editingEngId     = null;
    public string $editingEngStatus = '';

    // Logistics
    public array  $logUpdates       = [];
    public bool   $showLogModal     = false;
    public string $logStatus        = 'pending';
    public string $logCarrier       = '';
    public string $logTracking      = '';
    public string $logNotes         = '';
    public ?int   $logOrderItemId   = null;
    public string $logOrderItemDesc = '';
    public ?int   $editingLogId     = null;
    public string $editingLogStatus = '';

    // Item Logs Modal
    public bool   $showItemLogsModal  = false;
    public string $itemLogsDesc       = '';
    public array  $itemEngLogs        = [];
    public array  $itemLogLogs        = [];

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

        // Notify the client about the status change
        $statusLabel = OrderStatusEnum::tryFrom($this->newStatus)?->label() ?? $this->newStatus;
        app(NotificationService::class)->send(
            title: 'Order Status Updated',
            body: 'Your order ' . $this->order->order_no . ' status has been changed to: ' . $statusLabel . '.',
            type: NotificationTypeEnum::OrderUpdated,
            recipientIds: [$this->order->client_id],
            actionUrl: route('enduser.orders.show', $this->order->uuid),
        );
    }

    public function openItemLogs(int $itemId): void
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        $this->itemLogsDesc = $item ? $item['description'] : '';
        $this->itemEngLogs  = collect($this->engUpdates)->where('item_id', $itemId)->values()->toArray();
        $this->itemLogLogs  = collect($this->logUpdates)->where('item_id', $itemId)->values()->toArray();
        $this->showItemLogsModal = true;
    }

    public function openEngModal(int $itemId): void
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        $this->engOrderItemId   = $itemId;
        $this->engOrderItemDesc = $item ? \Illuminate\Support\Str::limit($item['description'], 60) : '';
        $this->engStatus        = 'pending';
        $this->engNotes         = '';
        $this->showEngModal     = true;
    }

    public function saveEngUpdate(): void
    {
        $this->validate([
            'engStatus' => 'required|in:' . implode(',', EngineeringStatusEnum::values()),
            'engNotes'  => 'nullable|string|max:2000',
        ]);

        EngineeringUpdate::create([
            'uuid'          => (string) Str::uuid(),
            'order_id'      => $this->order->id,
            'order_item_id' => $this->engOrderItemId,
            'updated_by'    => Auth::id(),
            'status'        => $this->engStatus,
            'notes'         => $this->engNotes ?: null,
        ]);

        $this->showEngModal = false;
        $this->loadEngUpdates();
        $this->loadItems();
        $this->dispatch('toast', message: 'Engineering update added.', type: 'success');

        // Notify the client about the engineering update
        app(NotificationService::class)->send(
            title: 'Engineering Update on Your Order',
            body: 'An engineering update was added to order ' . $this->order->order_no . ($this->engNotes ? ': ' . $this->engNotes : '.'),
            type: NotificationTypeEnum::OrderUpdated,
            recipientIds: [$this->order->client_id],
            actionUrl: route('enduser.orders.show', $this->order->uuid),
        );
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
        $this->loadItems();
        $this->dispatch('toast', message: 'Engineering status updated.', type: 'success');
    }

    // -------------------------------------------------------------------------

    public function openLogModal(int $itemId): void
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        $this->logOrderItemId   = $itemId;
        $this->logOrderItemDesc = $item ? \Illuminate\Support\Str::limit($item['description'], 60) : '';
        $this->logStatus        = 'pending';
        $this->logCarrier       = '';
        $this->logTracking      = '';
        $this->logNotes         = '';
        $this->showLogModal     = true;
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
            'uuid'            => (string) Str::uuid(),
            'order_id'        => $this->order->id,
            'order_item_id'   => $this->logOrderItemId,
            'updated_by'      => Auth::id(),
            'status'          => $this->logStatus,
            'carrier'         => $this->logCarrier ?: null,
            'tracking_number' => $this->logTracking ?: null,
            'notes'           => $this->logNotes ?: null,
        ]);

        $this->showLogModal = false;
        $this->loadLogUpdates();
        $this->loadItems();
        $this->dispatch('toast', message: 'Logistics update added.', type: 'success');

        // Notify the client about the shipping/logistics update
        $trackingInfo = $this->logTracking ? ' — Tracking: ' . $this->logTracking : '';
        app(NotificationService::class)->send(
            title: 'Shipping Update on Your Order',
            body: 'A logistics update was added to order ' . $this->order->order_no . $trackingInfo . ($this->logNotes ? ': ' . $this->logNotes : '.'),
            type: NotificationTypeEnum::OrderUpdated,
            recipientIds: [$this->order->client_id],
            actionUrl: route('enduser.orders.show', $this->order->uuid),
        );
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
        $this->loadItems();
        $this->dispatch('toast', message: 'Logistics status updated.', type: 'success');
    }

    // -------------------------------------------------------------------------

    private function loadEngUpdates(): void
    {
        $this->engUpdates = EngineeringUpdate::with('updatedBy', 'orderItem')
            ->where('order_id', $this->order->id)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'status'    => $u->status->value,
                'label'     => $u->status->label(),
                'notes'     => $u->notes ?? '',
                'user'      => $u->updatedBy?->name ?? 'System',
                'date'      => $u->created_at->format('d M Y, H:i'),
                'item_id'   => $u->order_item_id,
                'item_desc' => $u->orderItem?->description
                                ? \Illuminate\Support\Str::limit($u->orderItem->description, 50)
                                : null,
            ])
            ->toArray();
    }

    private function loadLogUpdates(): void
    {
        $this->logUpdates = LogisticsUpdate::with('updatedBy', 'orderItem')
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
                'item_id'  => $u->order_item_id,
                'item_desc'=> $u->orderItem?->description
                               ? \Illuminate\Support\Str::limit($u->orderItem->description, 50)
                               : null,
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------

    private function loadItems(): void
    {
        // Get last engineering status per order item
        $lastEngStatuses = EngineeringUpdate::where('order_id', $this->order->id)
            ->selectRaw('order_item_id, MAX(id) as latest_id')
            ->groupBy('order_item_id')
            ->pluck('latest_id', 'order_item_id');

        $engRecords = $lastEngStatuses->isNotEmpty()
            ? EngineeringUpdate::whereIn('id', $lastEngStatuses->values())->get()->keyBy('order_item_id')
            : collect();

        // Get last logistics status per order item
        $lastLogStatuses = LogisticsUpdate::where('order_id', $this->order->id)
            ->selectRaw('order_item_id, MAX(id) as latest_id')
            ->groupBy('order_item_id')
            ->pluck('latest_id', 'order_item_id');

        $logRecords = $lastLogStatuses->isNotEmpty()
            ? LogisticsUpdate::whereIn('id', $lastLogStatuses->values())->get()->keyBy('order_item_id')
            : collect();

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
                'eng_status'  => $engRecords->get($item->id)?->status?->value,
                'eng_label'   => $engRecords->get($item->id)?->status?->label(),
                'log_status'  => $logRecords->get($item->id)?->status?->value,
                'log_label'   => $logRecords->get($item->id)?->status?->label(),
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
        $isClosed = $status === 'closed';

        $this->steps = [
            [
                'label' => __('app.status_open'),
                'state' => 'completed',
            ],
            [
                'label' => __('app.status_closed'),
                'state' => $isClosed ? 'completed' : 'pending',
            ],
        ];
    }
}
