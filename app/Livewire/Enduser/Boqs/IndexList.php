<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Models\Boq;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    public function convertToQuotation(string $uuid): void
    {
        $boq = Boq::with(['project', 'items'])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if ($boq->status !== BoqStatusEnum::Draft) {
            $this->dispatch('toast', message: 'Only draft BOQs can be converted.', type: 'error');
            return;
        }

        $items = $boq->items;

        if ($items->isEmpty()) {
            $this->dispatch('toast', message: 'This BOQ has no items. Add items before converting.', type: 'error');
            return;
        }

        try {
            $quotation = DB::transaction(function () use ($boq, $items) {
                $quotation = QuotationRequest::create([
                    'client_id'    => Auth::id(),
                    'project_id'   => $boq->project_id,
                    'boq_id'       => $boq->id,
                    'quotation_no' => $this->generateQuotationNo(),
                    'project_name' => $boq->project?->name,
                    'status'       => QuotationRequestStatusEnum::Tender,
                    'source_type'  => QuotationSourceTypeEnum::Manual,
                ]);

                foreach ($items as $item) {
                    QuotationItem::create([
                        'quotation_request_id' => $quotation->id,
                        'product_id'           => $item->product_id,
                        'description'          => (string) $item->description,
                        'quantity'             => (float) $item->quantity,
                        'unit_id'              => $item->unit_id,
                        'category'             => (string) ($item->category ?? ''),
                        'brand'                => (string) ($item->brand ?? ''),
                        'status'               => 'pending',
                        'engineering_required' => (bool) $item->engineering_required,
                        'confidence'           => $item->confidence,
                        'ai_extracted'         => (bool) $item->ai_extracted,
                        'is_selected'          => true,
                    ]);
                }

                $boq->update(['status' => BoqStatusEnum::Completed]);

                return $quotation;
            });

            $this->redirect(route('enduser.quotations.show', $quotation->uuid));

        } catch (\Throwable $e) {
            Log::error('IndexList::convertToQuotation failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Failed to create quotation. Please try again.', type: 'error');
        }
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

    private function generateQuotationNo(): string
    {
        $prefix = 'QR-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (QuotationRequest::where('quotation_no', $candidate)->exists());

        return $candidate;
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
