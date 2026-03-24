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

    public int $perPage = 10;

    protected array $allowedPerPage = [10, 50, 25, 5];

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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->quotation_no = '';
        $this->status = '';
        $this->created_from = '';
        $this->created_to = '';
        $this->updated_from = '';
        $this->updated_to = '';
        $this->perPage = 10;
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
            ->where('client_id', $clientId)
            ->latest();

        if ($this->search !== '') {
            $query->where(function ($builder): void {
                $builder
                    ->where('quotation_no', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
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
