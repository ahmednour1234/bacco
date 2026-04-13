<?php

namespace App\Livewire\Enduser\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $perPage = 10;

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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $clientId = Auth::id();

        $allProjects = Project::query()->where('client_id', $clientId);
        $stats = [
            'total'     => (clone $allProjects)->count(),
            'active'    => (clone $allProjects)->where('status', 'active')->count(),
            'completed' => (clone $allProjects)->where('status', 'completed')->count(),
        ];

        $query = Project::query()
            ->withCount(['boqs', 'quotationRequests', 'orders'])
            ->where('client_id', $clientId)
            ->latest();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return view('livewire.enduser.projects.index-list', [
            'projects' => $query->paginate($this->perPage),
            'stats'    => $stats,
        ]);
    }
}
