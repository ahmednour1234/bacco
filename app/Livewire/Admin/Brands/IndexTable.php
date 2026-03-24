<?php

namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    protected array $allowedPerPage = [10, 50, 25, 5];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function delete(string $uuid): void
    {
        $brand = Brand::byUuid($uuid)->firstOrFail();
        $brand->websites()->detach();
        $brand->delete();

        session()->flash('success', 'Brand deleted successfully.');
    }

    public function render()
    {
        $brands = Brand::query()
            ->withCount('products')
            ->with('websites')
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.brands.index-table', compact('brands'));
    }
}
