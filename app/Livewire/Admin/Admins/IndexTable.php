<?php

namespace App\Livewire\Admin\Admins;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $status  = '';
    public int    $perPage = 10;

    // Modal state
    public bool   $showModal    = false;
    public ?int   $editingId    = null;
    public string $name         = '';
    public string $email        = '';
    public string $phone        = '';
    public string $password     = '';
    public string $userType     = 'employee';

    protected array $allowedPerPage = [10, 25, 50];

    protected function rules(): array
    {
        $uniqueEmail = $this->editingId
            ? 'unique:users,email,' . $this->editingId
            : 'unique:users,email';

        return [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', $uniqueEmail],
            'phone'    => 'nullable|string|max:30',
            'userType' => 'required|in:admin,employee',
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'password']);
        $this->userType  = 'employee';
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingId = $userId;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = $user->phone ?? '';
        $this->userType  = $user->user_type->value;
        $this->password  = '';
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'email', 'phone', 'password']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'user_type' => $this->userType,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            // Prevent changing own type away from admin
            if ((int) $this->editingId === (int) auth()->id() && $this->userType !== 'admin') {
                $this->addError('userType', 'You cannot change your own role.');
                return;
            }
            User::findOrFail($this->editingId)->update($data);
        } else {
            $data['active']    = true;
            $data['password'] ??= Hash::make($this->password);
            User::create($data);
        }

        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Admin updated.' : 'Admin created.');
    }

    public function toggleActive(int $userId): void
    {
        if ((int) $userId === (int) auth()->id()) {
            return;
        }
        $user = User::findOrFail($userId);
        $user->update(['active' => ! (bool) $user->active]);
    }

    public function render()
    {
        $query = User::query()
            ->whereIn('user_type', [UserTypeEnum::Admin->value, UserTypeEnum::Employee->value])
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

        $admins = $query->paginate($this->perPage);

        return view('livewire.admin.admins.index-table', compact('admins'));
    }
}
