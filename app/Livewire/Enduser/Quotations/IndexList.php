<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationRequestStatusEnum;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;
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
