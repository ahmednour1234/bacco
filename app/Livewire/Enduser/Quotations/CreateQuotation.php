<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationItemStatusEnum;
use App\Enums\QuotationProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\PricingService;
use App\Services\QuotationAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateQuotation extends Component
{
    use WithFileUploads;

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    public ?int $quotationId = null;

    public bool $isEditMode = false;

    #[Validate('required|string|max:255')]
    public string $projectName = '';

    #[Validate('required|string')]
    public string $projectStatus = QuotationProjectStatusEnum::Pending->value;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $boqFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $processing = false;

    public string $boqFileName = '';

    public bool $showPricing = false;

    public bool $pricingLoading = false;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /**
     * Boot from an existing QuotationRequest (show route).
     */
    public function mount(?int $quotationId = null): void
    {
        if ($quotationId === null) {
            $this->isEditMode = false;
            $this->projectStatus = QuotationProjectStatusEnum::Pending->value;
            return;
        }

        $this->isEditMode = true;

        $quotation = QuotationRequest::where('client_id', Auth::id())
            ->findOrFail($quotationId);

        $this->quotationId   = $quotation->id;
        $this->projectName   = (string) ($quotation->project_name ?? '');
        $this->projectStatus = $quotation->project_status instanceof QuotationProjectStatusEnum
            ? $quotation->project_status->value
            : (string) ($quotation->project_status ?? '');

        $boqDoc = $quotation->uploadedDocuments()
            ->latest()
            ->where('file_type', 'boq')
            ->first();

        if ($boqDoc) {
            $this->boqFileName = $boqDoc->file_name;
        }

        $this->items = $quotation->items()
            ->get()
            ->map(fn(QuotationItem $item) => [
                'id'                   => $item->id,
                'description'          => $item->description,
                'quantity'             => (float) $item->quantity,
                'unit'                 => $item->unit?->name ?? '',
                'category'             => (string) ($item->category ?? ''),
                'brand'                => (string) ($item->brand ?? ''),
                'status'               => $item->status instanceof QuotationItemStatusEnum
                    ? $item->status->value
                    : (string) $item->status,
                'engineering_required' => (bool) $item->engineering_required,
                'confidence'           => $item->confidence,
                'ai_extracted'         => (bool) $item->ai_extracted,
                'unit_price'           => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'price_source'         => $item->price_source,
                'price_status'         => $item->price_status ?? 'pending',
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // BOQ Upload & AI extraction
    // -------------------------------------------------------------------------

    public function uploadBoq(): void
    {
        $this->validate([
            'projectName'   => 'required|string|max:255',
            'projectStatus' => 'required|string',
        ]);

        // ALL metadata calls on TemporaryUploadedFile — getSize(), getMimeType(), and
        // every Laravel file validation rule — route through Flysystem on the livewire-tmp
        // disk and throw UnableToRetrieveMetadata in Livewire v3 on Windows.
        // Only getClientOriginalExtension() and getClientOriginalName() are safe (plain strings).
        // Strategy: validate extension first, then store immediately via stream-copy
        // (no metadata read on source), then read everything from the stored local file.
        if (! $this->boqFile) {
            $this->addError('boqFile', 'Please select a file to upload.');
            return;
        }

        $allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv'];
        $extension         = strtolower($this->boqFile->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            $this->addError('boqFile', 'The file must be of type: pdf, xlsx, xls, or csv.');
            return;
        }

        $this->processing = true;

        try {
            $quotation     = $this->persistQuotation(QuotationRequestStatusEnum::Draft);
            $fileName      = $this->boqFile->getClientOriginalName(); // safe: plain string property

            // Store via stream-copy — does NOT read any Flysystem metadata on source temp file.
            $storedPath    = $this->boqFile->storeAs('boq-uploads', Str::uuid() . '.' . $extension, 'local');

            // Read metadata from the stored file on the local disk — fully safe.
            $fileSize      = Storage::disk('local')->size($storedPath);
            $storedAbsPath = Storage::disk('local')->path($storedPath);

            // Size check after storing (cannot safely read size from temp file).
            if ($fileSize > 50 * 1024 * 1024) {
                Storage::disk('local')->delete($storedPath);
                $this->addError('boqFile', 'The file must not be larger than 50 MB.');
                $this->processing = false;
                return;
            }

            UploadedDocument::create([
                'quotation_request_id' => $quotation->id,
                'uploaded_by'          => Auth::id(),
                'file_name'            => $fileName,
                'file_path'            => $storedPath,
                'file_type'            => 'boq',
                'file_size'            => $fileSize,
            ]);

            $this->boqFileName = $fileName;

            // Pass the absolute stored path to the AI service (real filesystem, fully safe).
            $ai     = app(QuotationAiService::class);
            $result = $ai->parseBoq($storedAbsPath, [
                'quotation_id'   => $quotation->id,
                'project_name'   => $this->projectName,
                'project_status' => $this->projectStatus,
            ]);

            if (! $result['success']) {
                $this->dispatch('toast', message: $result['error'] ?? 'AI extraction failed.', type: 'error');
            } elseif (empty($result['items'])) {
                $this->dispatch('toast', message: 'The AI service could not extract any items from the uploaded file. Please add items manually.', type: 'warning');
            } else {
                // Replace current table rows with the latest AI response.
                QuotationItem::where('quotation_request_id', $quotation->id)->delete();
                $this->items = [];

                foreach ($result['items'] as $aiItem) {
                    $this->items[] = array_merge([
                        'id'                   => null,
                        'description'          => '',
                        'quantity'             => 1,
                        'unit'                 => '',
                        'category'             => '',
                        'brand'                => '',
                        'status'               => 'pending',
                        'engineering_required' => false,
                        'confidence'           => null,
                        'ai_extracted'         => true,
                        'unit_price'           => null,
                        'price_source'         => null,
                        'price_status'         => 'pending',
                    ], $aiItem);
                }

                $quotation->update(['source_type' => QuotationSourceTypeEnum::Api]);
                $this->persistItems($quotation);

                $this->dispatch('toast', message: count($result['items']) . ' items extracted successfully from the BOQ file.', type: 'success');
            }

            $this->boqFile = null;

        } catch (\Throwable $e) {
            Log::error('CreateQuotation::uploadBoq failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', message: 'Upload failed. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    // -------------------------------------------------------------------------
    // Pricing
    // -------------------------------------------------------------------------

    public function fetchPricing(): void
    {
        if (empty($this->items)) {
            $this->dispatch('toast', message: 'Add items first before fetching prices.', type: 'warning');
            return;
        }

        $this->pricingLoading = true;

        try {
            $this->items      = app(PricingService::class)->fetchPrices($this->items);
            $this->showPricing = true;
        } catch (\Throwable $e) {
            Log::error('CreateQuotation::fetchPricing failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Pricing fetch failed. Please try again.', type: 'error');
        } finally {
            $this->pricingLoading = false;
        }

        $found  = collect($this->items)->filter(fn($i) => ! empty($i['unit_price']))->count();
        $missed = count($this->items) - $found;

        $msg = "{$found} item(s) priced successfully.";
        if ($missed > 0) {
            $msg .= " {$missed} item(s) could not be priced automatically.";
        }
        $this->dispatch('toast', message: $msg, type: $found > 0 ? 'success' : 'warning');
    }

    public function approvePriceItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }
        $this->items[$index]['price_status'] = 'approved';
    }

    public function rejectPriceItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }
        $this->items[$index]['price_status'] = 'rejected';
    }

    // -------------------------------------------------------------------------
    // Manual item management
    // -------------------------------------------------------------------------

    public function addManualItem(): void
    {
        $this->items[] = [
            'id'                   => null,
            'description'          => '',
            'quantity'             => 0,
            'unit'                 => '',
            'category'             => '',
            'brand'                => '',
            'status'               => 'pending',
            'engineering_required' => false,
            'confidence'           => null,
            'ai_extracted'         => false,
            'unit_price'           => null,
            'price_source'         => null,
            'price_status'         => 'pending',
        ];
    }

    public function updateItem(int $index, string $field, mixed $value): void
    {
        $allowed = ['description', 'quantity', 'unit', 'category', 'brand', 'engineering_required'];

        if (! array_key_exists($index, $this->items) || ! in_array($field, $allowed, true)) {
            return;
        }

        if ($field === 'quantity') {
            $value = is_numeric($value) ? max(0, (float) $value) : 0;
        }

        if ($field === 'engineering_required') {
            $value = (bool) $value;
        }

        $this->items[$index][$field] = $value;
    }

    public function approveItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        $this->items[$index]['status'] = QuotationItemStatusEnum::Sourcing->value;

        if (! empty($this->items[$index]['id'])) {
            QuotationItem::where('id', $this->items[$index]['id'])
                ->update(['status' => QuotationItemStatusEnum::Sourcing]);
        }
    }

    public function rejectItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        $this->items[$index]['status'] = QuotationItemStatusEnum::Rejected->value;

        if (! empty($this->items[$index]['id'])) {
            QuotationItem::where('id', $this->items[$index]['id'])
                ->update(['status' => QuotationItemStatusEnum::Rejected]);
        }
    }

    public function deleteItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        if (! empty($this->items[$index]['id'])) {
            QuotationItem::where('id', $this->items[$index]['id'])->delete();
        }

        array_splice($this->items, $index, 1);
    }

    public function clearAllItems(): void
    {
        if ($this->quotationId !== null) {
            QuotationItem::where('quotation_request_id', $this->quotationId)->delete();
        }

        $this->items = [];

        $this->dispatch('toast', message: 'All items removed successfully.', type: 'success');
    }

    // -------------------------------------------------------------------------
    // Save draft / Submit
    // -------------------------------------------------------------------------

    public function saveDraft(): void
    {
        $this->validate([
            'projectName'   => 'required|string|max:255',
            'projectStatus' => 'required|string',
        ]);

        $quotation = $this->persistQuotation(QuotationRequestStatusEnum::Draft);
        $this->persistItems($quotation);
        $this->quotationId = $quotation->id;

        $this->dispatch('toast', message: 'Draft saved successfully.', type: 'success');
    }

    public function submit(): void
    {
        if ($this->processing) {
            $this->dispatch('toast', message: 'Please wait for AI extraction to complete before submitting.', type: 'error');

            return;
        }

        $this->validate([
            'projectName'   => 'required|string|max:255',
            'projectStatus' => 'required|string',
        ]);

        if (empty($this->items)) {
            $this->dispatch('toast', message: 'Please add at least one item before submitting.', type: 'error');

            return;
        }

        $quotation = $this->persistQuotation(QuotationRequestStatusEnum::Tender);
        $this->persistItems($quotation);
        $this->quotationId = $quotation->id;

        $this->redirect(route('enduser.quotations.show', $quotation->uuid));
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.enduser.quotations.create-quotation', [
            'projectStatuses' => QuotationProjectStatusEnum::cases(),
            'itemStatuses'    => QuotationItemStatusEnum::cases(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function persistQuotation(QuotationRequestStatusEnum $status): QuotationRequest
    {
        if (! $this->isEditMode) {
            $this->projectStatus = QuotationProjectStatusEnum::Pending->value;
        }

        $attributes = [
            'project_name'   => $this->projectName,
            'project_status' => $this->projectStatus,
        ];

        if ($this->quotationId !== null) {
            $quotation = QuotationRequest::where('client_id', Auth::id())
                ->findOrFail($this->quotationId);

            // Only allow updating status forward (draft → submitted), never backward
            if (in_array($status, [QuotationRequestStatusEnum::Submitted, QuotationRequestStatusEnum::Tender], true)) {
                $attributes['status'] = $status;
            }

            $quotation->update($attributes);

            return $quotation;
        }

        $quotation = QuotationRequest::create(array_merge($attributes, [
            'client_id'      => Auth::id(),
            'quotation_no'   => $this->generateQuotationNo(),
            'status'         => $status,
            'source_type'    => QuotationSourceTypeEnum::Manual,
        ]));

        $this->quotationId = $quotation->id;

        return $quotation;
    }

    private function persistItems(QuotationRequest $quotation): void
    {
        foreach ($this->items as $index => $row) {
            $unitName = trim((string) ($row['unit'] ?? ''));
            $unitId   = null;
            if ($unitName !== '') {
                $unitId = Unit::firstOrCreate(
                    ['name' => $unitName],
                    ['symbol' => mb_strtolower(mb_substr($unitName, 0, 20))]
                )->id;
            }

            $data = [
                'quotation_request_id' => $quotation->id,
                'description'          => (string) ($row['description'] ?? ''),
                'quantity'             => is_numeric($row['quantity'] ?? null) ? (float) $row['quantity'] : 0,
                'unit_id'              => $unitId,
                'category'             => (string) ($row['category'] ?? ''),
                'brand'                => (string) ($row['brand'] ?? ''),
                'status'               => $row['status'] ?? 'pending',
                'engineering_required' => (bool) ($row['engineering_required'] ?? false),
                'confidence'           => is_numeric($row['confidence'] ?? null) ? (float) $row['confidence'] : null,
                'raw_data'             => $row['raw_data'] ?? null,
                'ai_extracted'         => (bool) ($row['ai_extracted'] ?? false),
                'unit_price'           => is_numeric($row['unit_price'] ?? null) ? (float) $row['unit_price'] : null,
                'price_source'         => $row['price_source'] ?? null,
                'price_status'         => $row['price_status'] ?? 'pending',
            ];

            if (! empty($row['id'])) {
                $item = QuotationItem::find($row['id']);

                if ($item && $item->quotation_request_id === $quotation->id) {
                    $item->update($data);
                    continue;
                }
            }

            $created = QuotationItem::create($data);
            $this->items[$index]['id'] = $created->id;
        }
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
