<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Models\Boq;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $created_from = '';

    public string $created_to = '';

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

    public function deleteBoq(int $id): void
    {
        $boq = Boq::where('id', $id)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if ($boq->status !== BoqStatusEnum::Draft) {
            $this->dispatch('toast', message: 'Only draft BOQs can be deleted.', type: 'error');
            return;
        }

        $boq->delete();
        $this->dispatch('toast', message: 'BOQ deleted successfully.', type: 'success');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->created_from = '';
        $this->created_to = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $clientId = Auth::id();

        $allBoqs = Boq::query()->where('client_id', $clientId);
        $stats = [
            'total'     => (clone $allBoqs)->count(),
            'draft'     => (clone $allBoqs)->where('status', 'draft')->count(),
            'submitted' => (clone $allBoqs)->where('status', 'submitted')->count(),
            'completed' => (clone $allBoqs)->where('status', 'completed')->count(),
        ];

        $query = Boq::query()
            ->with(['project', 'items'])
            ->where('client_id', $clientId)
            ->latest();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('boq_no', 'like', '%' . $search . '%')
                    ->orWhereHas('project', fn($q) => $q->where('name', 'like', '%' . $search . '%'));
            });
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

        $boqs = $query->paginate($this->perPage);

        $hasActiveFilters = $this->search !== ''
            || $this->status !== ''
            || $this->created_from !== ''
            || $this->created_to !== '';

        $statuses = BoqStatusEnum::cases();

        return view('livewire.enduser.boqs.index-list', compact('boqs', 'statuses', 'stats', 'hasActiveFilters'));
    }
}
