<?php

namespace App\Livewire\Enduser\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowProject extends Component
{
    public ?Project $project = null;

    public function mount(string $uuid): void
    {
        $this->project = Project::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();
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
