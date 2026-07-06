<?php

namespace App\Livewire\Admin\Seo;

use App\Models\SeoMeta;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $pages = SeoMeta::query()
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where('label', 'like', $term)
                  ->orWhere('route_name', 'like', $term)
                  ->orWhere('title_en', 'like', $term)
                  ->orWhere('title_ar', 'like', $term);
            })
            ->orderBy('route_name')
            ->paginate(20);

        return view('livewire.admin.seo.index-table', compact('pages'));
    }
}
