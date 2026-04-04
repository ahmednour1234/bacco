<?php

namespace App\Livewire\Supplier\Products;

use App\Models\Product;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $status  = '';
    public int    $perPage = 10;

    protected array $allowedPerPage = [10, 25, 50];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }

    public function toggleActive(int $id): void
    {
        $row = SupplierProduct::where('id', $id)
            ->where('supplier_id', Auth::id())
            ->firstOrFail();

        $row->update(['active' => ! $row->active]);
    }

    public function delete(int $id): void
    {
        SupplierProduct::where('id', $id)
            ->where('supplier_id', Auth::id())
            ->delete();

        session()->flash('success', 'Product removed from your catalogue.');
    }

    public function render()
    {
        $query = SupplierProduct::with('product')
            ->where('supplier_id', Auth::id())
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->whereHas('product', fn($q) => $q->where('name', 'like', $term));
        }

        if ($this->status === 'active') {
            $query->where('active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('active', false);
        }

        $supplierProducts = $query->paginate($this->perPage);

        return view('livewire.supplier.products.index-table', compact('supplierProducts'));
    }
}
