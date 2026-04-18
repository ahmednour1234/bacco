<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Services\PricingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class ShowBoq extends Component
{
    public string $uuid = '';

    public ?Boq $boq = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->boq = Boq::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->with('project')
            ->firstOrFail();

        $this->loadItems();
    }

    // -------------------------------------------------------------------------
    // Item selection
    // -------------------------------------------------------------------------

    public function toggleSelected(int $itemId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $newState = ! ($item['selected'] ?? false);
                $this->items[$index]['selected'] = $newState;
                BoqItem::where('id', $itemId)->update(['is_selected' => $newState]);
                break;
            }
        }
    }

    public function selectAll(): void
    {
        $ids = [];
        foreach ($this->items as $index => $item) {
            if (($item['status'] ?? '') !== 'rejected') {
                $this->items[$index]['selected'] = true;
                $ids[] = $item['id'];
            }
        }
        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update(['is_selected' => true]);
        }
    }

    public function deselectAll(): void
    {
        $ids = [];
        foreach ($this->items as $index => $_) {
            $this->items[$index]['selected'] = false;
            $ids[] = $this->items[$index]['id'];
        }
        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update(['is_selected' => false]);
        }
    }

    // -------------------------------------------------------------------------
    // Inline item editing
    // -------------------------------------------------------------------------

    public function updateItem(int $index, string $field): void
    {
        $item = $this->items[$index] ?? null;
        if (! $item) {
            return;
        }

        $allowed = ['description', 'quantity', 'engineering_required'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        $value = $item[$field];

        if ($field === 'quantity') {
            $value = max(0, (float) $value);
            $this->items[$index]['quantity'] = $value;
        }

        BoqItem::where('id', $item['id'])->update([$field => $value]);
    }

    public function toggleEngineering(int $itemId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $newState = ! ($item['engineering_required'] ?? false);
                $this->items[$index]['engineering_required'] = $newState;
                BoqItem::where('id', $itemId)->update(['engineering_required' => $newState]);
                break;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Create Quotation from selected items
    // -------------------------------------------------------------------------

    public function createQuotation(): void
    {
        $selectedItems = array_values(array_filter($this->items, fn($i) => $i['selected'] ?? false));

        if (empty($selectedItems)) {
            $this->dispatch('toast', message: 'Please select at least one item to create a quotation.', type: 'error');
            return;
        }

        try {
            $quotation = DB::transaction(function () use ($selectedItems) {
                $quotation = QuotationRequest::create([
                    'client_id'    => Auth::id(),
                    'project_id'   => $this->boq->project_id,
                    'boq_id'       => $this->boq->id,
                    'quotation_no' => $this->generateQuotationNo(),
                    'project_name' => $this->boq->project?->name,
                    'status'       => QuotationRequestStatusEnum::Tender,
                    'source_type'  => QuotationSourceTypeEnum::Manual,
                ]);

                foreach ($selectedItems as $row) {
                    $unitName = trim((string) ($row['unit'] ?? ''));
                    $unitId   = $row['unit_id'] ?? null;
                    if ($unitId === null && $unitName !== '') {
                        $unitId = Unit::firstOrCreate(
                            ['name' => $unitName],
                            ['symbol' => mb_strtolower(mb_substr($unitName, 0, 20))]
                        )->id;
                    }

                    $boqUnitPrice = is_numeric($row['unit_price'] ?? null) && (float) $row['unit_price'] > 0
                        ? (float) $row['unit_price']
                        : null;

                    QuotationItem::create([
                        'quotation_request_id' => $quotation->id,
                        'product_id'           => $row['product_id'] ?? null,
                        'description'          => (string) ($row['description'] ?? ''),
                        'quantity'             => is_numeric($row['quantity'] ?? null) ? (float) $row['quantity'] : 0,
                        'unit_id'              => $unitId,
                        'unit_price'           => $boqUnitPrice,
                        'price_source'         => $boqUnitPrice !== null ? 'boq' : null,
                        'price_status'         => $boqUnitPrice !== null ? 'pending' : 'pending',
                        'category'             => (string) ($row['category'] ?? ''),
                        'brand'                => (string) ($row['brand'] ?? ''),
                        'status'               => 'pending',
                        'engineering_required' => (bool) ($row['engineering_required'] ?? false),
                        'confidence'           => is_numeric($row['confidence'] ?? null) ? (float) $row['confidence'] : null,
                        'ai_extracted'         => (bool) ($row['ai_extracted'] ?? false),
                        'is_selected'          => true,
                    ]);
                }

                // Mark BOQ as completed
                $this->boq->update(['status' => BoqStatusEnum::Completed]);

                return $quotation;
            });

            $this->redirect(route('enduser.quotations.show', $quotation->uuid));

        } catch (\Throwable $e) {
            Log::error('ShowBoq::createQuotation failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Failed to create quotation. Please try again.', type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        $selectedCount = collect($this->items)->filter(fn($i) => $i['selected'] ?? false)->count();

        return view('livewire.enduser.boqs.show-boq', [
            'selectedCount' => $selectedCount,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function loadItems(): void
    {
        $this->items = $this->boq
            ->items()
            ->with('product')
            ->get()
            ->map(fn(BoqItem $item) => [
                'id'                   => $item->id,
                'description'          => (string) $item->description,
                'quantity'             => (float) $item->quantity,
                'unit_price'           => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'unit'                 => $item->unit?->name ?? '',
                'unit_id'              => $item->unit_id,
                'product_id'           => $item->product_id,
                'category'             => (string) ($item->category ?? ''),
                'brand'                => (string) ($item->brand ?? ''),
                'status'               => $item->status->value ?? 'pending',
                'engineering_required' => (bool) $item->engineering_required,
                'confidence'           => $item->confidence,
                'ai_extracted'         => (bool) $item->ai_extracted,
                'selected'             => (bool) $item->is_selected,
            ])
            ->toArray();
    }

    private function generateQuotationNo(): string
    {
        $prefix = 'QR-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (QuotationRequest::where('quotation_no', $candidate)->exists());

        return $candidate;
    }
}
