<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $status   = '';
    public string $userType = '';
    public int    $perPage  = 10;

    protected array $allowedPerPage = [10, 25, 50];

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedStatus(): void  { $this->resetPage(); }
    public function updatedUserType(): void { $this->resetPage(); }
    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'userType']);
        $this->resetPage();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Prevent admin from accidentally disabling their own account.
        if ((int) $user->id === (int) auth()->id()) {
            return;
        }

        $user->update([
            'active' => ! (bool) $user->active,
        ]);
    }

    public function render()
    {
        $query = User::query()
            ->where('user_type', '!=', UserTypeEnum::Supplier->value)
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        if ($this->status === 'active') {
            $query->where('active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('active', false);
        }

        if ($this->userType !== '') {
            $query->where('user_type', $this->userType);
        }

        $users = $query->paginate($this->perPage);

        $hasActiveFilters = $this->search !== '' || $this->status !== '' || $this->userType !== '';

        return view('livewire.admin.users.index-table', compact('users', 'hasActiveFilters'));
    }
}
