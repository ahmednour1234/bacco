<?php

namespace App\Livewire\Enduser\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowProject extends Component
{
    public ?Project $project = null;

    public bool   $editMode        = false;
    public string $editName        = '';
    public string $editDescription = '';

    protected function rules(): array
    {
        return [
            'editName'        => 'required|string|max:255',
            'editDescription' => 'nullable|string|max:2000',
        ];
    }

    public function mount(string $uuid): void
    {
        $this->project = Project::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();
    }

    public function startEdit(): void
    {
        $this->editName        = $this->project->name;
        $this->editDescription = $this->project->description ?? '';
        $this->editMode        = true;
    }

    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $this->validate();

        $this->project->update([
            'name'        => $this->editName,
            'description' => $this->editDescription ?: null,
        ]);

        $this->editMode = false;
        $this->dispatch('notify', type: 'success', message: __('app.project_updated'));
    }

    public function render()
    {
        $boqs = $this->project->boqs()
            ->withCount('items')
            ->latest()
            ->get();

        $quotations = $this->project->quotationRequests()
            ->with('items')
            ->latest()
            ->get();

        $orders = $this->project->orders()
            ->latest()
            ->get();

        return view('livewire.enduser.projects.show-project', [
            'boqs'       => $boqs,
            'quotations' => $quotations,
            'orders'     => $orders,
        ]);
    }
}
