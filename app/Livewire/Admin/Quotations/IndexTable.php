<?php

namespace App\Livewire\Admin\Quotations;

use App\Enums\QuotationRequestStatusEnum;
use App\Models\ClientProfile;
use App\Models\QuotationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $status      = '';
    public string $company     = '';
    public string $created_from = '';
    public string $created_to   = '';
    public int    $perPage     = 15;

    protected array $allowedPerPage = [15, 25, 50, 100];

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
        $this->company      = '';
        $this->created_from = '';
        $this->created_to   = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = QuotationRequest::with([
            'client.clientProfile',
            'items',
        ])->where('status', '!=', 'draft')->latest();

        // Search: quotation_no, company_name, client name, project_name
        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s): void {
                $q->where('quotation_no', 'like', '%' . $s . '%')
                  ->orWhere('project_name', 'like', '%' . $s . '%')
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', '%' . $s . '%'))
                  ->orWhereHas('client.clientProfile', fn($q2) => $q2->where('company_name', 'like', '%' . $s . '%'));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->company !== '') {
            $query->whereHas('client.clientProfile', fn($q) => $q->where('company_name', 'like', '%' . $this->company . '%'));
        }

        if ($this->created_from !== '') {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if ($this->created_to !== '') {
            $query->whereDate('created_at', '<=', $this->created_to);
        }

        $quotations = $query->paginate($this->perPage);
        $statuses   = QuotationRequestStatusEnum::cases();
        $total      = QuotationRequest::where('status', '!=', 'draft')->count();

        $hasActiveFilters = $this->search !== ''
            || $this->status !== ''
            || $this->company !== ''
            || $this->created_from !== ''
            || $this->created_to !== '';

        return view('livewire.admin.quotations.index-table', compact(
            'quotations', 'statuses', 'total', 'hasActiveFilters'
        ));
    }
}
