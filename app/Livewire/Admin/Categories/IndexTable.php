<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
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
        $category = Category::byUuid($uuid)->firstOrFail();
        $category->websites()->detach();
        $category->delete();

        session()->flash('success', 'Category deleted successfully.');
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->with(['parent', 'websites'])
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.categories.index-table', compact('categories'));
    }
}
