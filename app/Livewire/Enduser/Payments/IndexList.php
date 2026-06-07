<?php

namespace App\Livewire\Enduser\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexList extends Component
{
    use WithPagination;

    public function render()
    {
        $payments = Payment::with(['order', 'uploadedDocuments'])
            ->where('client_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('livewire.enduser.payments.index-list', compact('payments'));
    }
}
