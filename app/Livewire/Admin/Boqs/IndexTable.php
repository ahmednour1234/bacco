<?php

namespace App\Livewire\Admin\Boqs;

use App\Models\Boq;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $status  = '';
    public int    $perPage = 10;

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

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Boq::with(['project', 'client'])->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                  ->orWhereHas('project', fn($p) => $p->where('name', 'like', $term));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $boqs = $query->paginate($this->perPage);
        $hasActiveFilters = $this->search !== '' || $this->status !== '';

        return view('livewire.admin.boqs.index-table', compact('boqs', 'hasActiveFilters'));
    }
}
