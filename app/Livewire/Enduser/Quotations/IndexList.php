<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationRequestStatusEnum;
use App\Models\QuotationRequest;
use App\Services\Enduser\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $quotation_no = '';

    public string $status = '';

    public string $created_from = '';

    public string $created_to = '';

    public string $updated_from = '';

    public string $updated_to = '';

    public int $perPage = 5;

    protected array $allowedPerPage = [5, 10, 25, 50];

    // ── Address modal ──────────────────────────────────────────────────────────
    public bool   $showAddressModal      = false;
    public string $pendingQuotationUuid  = '';
    public string $addressType           = 'detailed'; // 'detailed' | 'national'

    // Detailed address
    public string $deliveryStreet      = '';
    public string $deliveryDistrict    = '';
    public string $deliveryCity        = '';
    public string $deliveryRegion      = '';
    public string $deliveryPostalCode  = '';
    public string $deliveryCountry     = 'SA';

    // National address (Saudi National Address)
    public string $nationalBuildingNo   = '';
    public string $nationalStreet       = '';
    public string $nationalDistrict     = '';
    public string $nationalCity         = '';
    public string $nationalPostalCode   = '';
    public string $nationalAdditionalNo = '';

    public function updating($name): void
    {
        if ($name !== 'page') {
            $this->resetPage();
        }
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function openAddressModal(string $uuid): void
    {
        $quotation = QuotationRequest::with(['items'])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if ($quotation->status->value !== 'tender') {
            $this->dispatch('toast', message: __('app.only_tender_can_convert'), type: 'error');
            return;
        }

        if ($quotation->isExpired()) {
            $this->dispatch('toast', message: __('app.expired_block_msg'), type: 'error');
            return;
        }

        if ($quotation->items->isEmpty()) {
            $this->dispatch('toast', message: __('app.no_items_in_quotation'), type: 'error');
            return;
        }

        $subtotal = $quotation->items
            ->filter(fn($i) => ($i->status->value ?? '') !== 'rejected' && is_numeric($i->unit_price))
            ->sum(fn($i) => (float) $i->unit_price * (float) $i->quantity);

        if ($subtotal <= 0) {
            $this->dispatch('toast', message: __('app.total_must_be_positive'), type: 'error');
            return;
        }

        $this->pendingQuotationUuid = $uuid;
        $this->showAddressModal     = true;
    }

    public function confirmConvertToOrder(): void
    {
        if (! $this->validateAddress()) {
            return;
        }

        $quotation = QuotationRequest::with(['items.unit'])
            ->where('uuid', $this->pendingQuotationUuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $selectedItems = $quotation->items->map(fn($i) => [
            'id'          => $i->id,
            'product_id'  => $i->product_id,
            'description' => (string) $i->description,
            'quantity'    => (float) $i->quantity,
            'unit_id'     => $i->unit_id,
            'unit_price'  => (float) ($i->unit_price ?? 0),
            'category'    => (string) ($i->category ?? ''),
            'brand'       => (string) ($i->brand ?? ''),
            'status'      => $i->status->value ?? 'pending',
            'selected'    => true,
        ])->values()->toArray();

        try {
            $order = app(OrderService::class)->createFromQuotation(
                $quotation,
                $selectedItems,
                $this->buildAddressData(),
            );
            $quotation->update(['status' => QuotationRequestStatusEnum::Submitted]);
            $this->redirect(route('enduser.orders.show', $order->uuid));
        } catch (\Throwable $e) {
            Log::error('IndexList::confirmConvertToOrder failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: __('app.order_create_failed'), type: 'error');
        }
    }

    private function validateAddress(): bool
    {
        if ($this->addressType === 'national') {
            if (! $this->nationalBuildingNo || ! $this->nationalStreet || ! $this->nationalDistrict || ! $this->nationalCity) {
                $this->dispatch('toast', message: __('app.address_fields_required'), type: 'error');
                return false;
            }
        } else {
            if (! $this->deliveryStreet || ! $this->deliveryCity) {
                $this->dispatch('toast', message: __('app.address_fields_required'), type: 'error');
                return false;
            }
        }
        return true;
    }

    private function buildAddressData(): array
    {
        if ($this->addressType === 'national') {
            return [
                'delivery_address_type' => 'national',
                'delivery_building_no'  => $this->nationalBuildingNo,
                'delivery_street'       => $this->nationalStreet,
                'delivery_district'     => $this->nationalDistrict,
                'delivery_city'         => $this->nationalCity,
                'delivery_postal_code'  => $this->nationalPostalCode,
                'delivery_additional_no'=> $this->nationalAdditionalNo,
                'delivery_country'      => 'SA',
            ];
        }

        return [
            'delivery_address_type' => 'detailed',
            'delivery_street'       => $this->deliveryStreet,
            'delivery_district'     => $this->deliveryDistrict,
            'delivery_city'         => $this->deliveryCity,
            'delivery_region'       => $this->deliveryRegion,
            'delivery_postal_code'  => $this->deliveryPostalCode,
            'delivery_country'      => $this->deliveryCountry ?: 'SA',
        ];
    }

    public function deleteQuotation(int $id): void
    {
        $quotation = QuotationRequest::where('id', $id)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if (! in_array($quotation->status->value, ['draft', 'tender'], true)) {
            $this->dispatch('toast', message: 'Only draft or tender quotations can be deleted.', type: 'error');
            return;
        }

        $quotation->delete();
        $this->dispatch('toast', message: 'Quotation deleted successfully.', type: 'success');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->quotation_no = '';
        $this->status = '';
        $this->created_from = '';
        $this->created_to = '';
        $this->updated_from = '';
        $this->updated_to = '';
        $this->perPage = 5;
        $this->resetPage();
    }

    public function render()
    {
        $clientId = Auth::id();

        $allQuotations = QuotationRequest::query()->where('client_id', $clientId);
        $stats = [
            'total' => (clone $allQuotations)->count(),
            'active' => (clone $allQuotations)->whereIn('status', ['submitted', 'in_review', 'quoted'])->count(),
            'completed' => (clone $allQuotations)->where('status', 'accepted')->count(),
        ];

        $query = QuotationRequest::query()
            ->with([
                'client.clientProfile',
                'items'  => fn($q) => $q->select('id', 'quotation_request_id', 'unit_price', 'quantity'),
                'orders' => fn($q) => $q->select('id', 'uuid', 'order_no', 'quotation_request_id'),
            ])
            ->where('client_id', $clientId)
            ->latest();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('quotation_no', 'like', '%' . $search . '%')
                    ->orWhere('project_name', 'like', '%' . $search . '%')
                    ->orWhereHas('client', fn($q) => $q->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('client.clientProfile', fn($q) => $q->where('company_name', 'like', '%' . $search . '%'));
            });
        }

        if ($this->quotation_no !== '') {
            $query->where('quotation_no', 'like', '%' . $this->quotation_no . '%');
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

        if ($this->updated_from !== '') {
            $query->whereDate('updated_at', '>=', $this->updated_from);
        }

        if ($this->updated_to !== '') {
            $query->whereDate('updated_at', '<=', $this->updated_to);
        }

        $quotations = $query->paginate($this->perPage);

        $hasActiveFilters = $this->search !== ''
            || $this->quotation_no !== ''
            || $this->status !== ''
            || $this->created_from !== ''
            || $this->created_to !== ''
            || $this->updated_from !== ''
            || $this->updated_to !== '';

        $statuses = QuotationRequestStatusEnum::cases();

        return view('livewire.enduser.quotations.index-list', compact('quotations', 'statuses', 'stats', 'hasActiveFilters'));
    }
}
