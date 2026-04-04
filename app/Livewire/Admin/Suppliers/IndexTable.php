<?php

namespace App\Livewire\Admin\Suppliers;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $status   = '';
    public int    $perPage  = 10;

    protected array $allowedPerPage = [10, 25, 50];

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedStatus(): void  { $this->resetPage(); }
    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with('supplierProfile')
            ->withCount('supplierProducts')
            ->where('user_type', UserTypeEnum::Supplier)
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhereHas('supplierProfile', fn($p) => $p->where('company_name', 'like', $term));
            });
        }

        if ($this->status === 'active') {
            $query->where('active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('active', false);
        }

        $suppliers = $query->paginate($this->perPage);

        $hasActiveFilters = $this->search !== '' || $this->status !== '';

        return view('livewire.admin.suppliers.index-table', compact('suppliers', 'hasActiveFilters'));
    }
}
