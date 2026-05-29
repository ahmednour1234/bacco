<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Enums\BoqTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\QuotationItemStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Enums\QuotationVersionStatusEnum;
use App\Jobs\FetchQuotationPricesJob;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Project;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\QuotationVersion;
use App\Models\QuotationVersionItem;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\NotificationService;
use App\Services\QuotationAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBoq extends Component
{
    use WithFileUploads;

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    /** Wizard step: 1=Extraction, 2=Confirmation, 3=Fetch Prices, 4=Address+Payment, 5=Order Placed */
    public int $currentStep = 1;

    public ?int $boqId = null;
    public ?int $projectId = null;
    public string $draftBoqUuid = '';

    public bool $isEditMode = false;

    #[Validate('required|string|max:255')]
    public string $projectName = '';

    #[Validate('nullable|string|max:5000')]
    public string $projectDescription = '';

    #[Validate('required|string|in:tender,awarded')]
    public string $boqType = 'tender';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $boqFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $processing = false;

    public string $boqFileName = '';

    // ── Step 3: Quotation & Prices ─────────────────────────────────────────
    public ?int   $quotationId    = null;
    public string $quotationUuid  = '';
    /** @var array<int, array<string, mixed>> */
    public array  $pricedItems    = [];
    public float  $quotationTotal = 0;
    public int    $pricedCount    = 0;
    public int    $unpricedCount  = 0;

    // ── Step 4: Address & Payment ─────────────────────────────────────────
    public string $deliveryAddressMode  = 'detailed';   // 'national' | 'detailed'
    public string $deliveryShortAddress = '';            // 8-char code e.g. RJHH6392
    public string $deliveryBuildingNo   = '';
    public string $deliveryStreet       = '';
    public string $deliveryDistrict     = '';
    public string $deliveryCity         = '';
    public string $deliveryRegion       = '';
    public string $deliveryPostalCode   = '';
    public string $deliveryAdditionalNo = '';
    public string $paymentMethod        = 'bank_transfer';

    // ── Step 3: Prices fetching state ─────────────────────────────────────
    public bool $pricesFetching = false;

    // ── Step 5: Order Placed ──────────────────────────────────────────────
    public string $orderUuid       = '';
    public string $orderNo         = '';
    public float  $orderGrandTotal = 0;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount(?string $projectUuid = null): void
    {
        // ── Load a specific draft by UUID (legacy / direct link) ──────────────
        $draftUuid = request()->query('draft');
        if ($draftUuid) {
            $boq = Boq::where('uuid', $draftUuid)
                ->where('client_id', Auth::id())
                ->where('status', BoqStatusEnum::Draft)
                ->with(['project', 'items.unit'])
                ->first();

            if ($boq) {
                $this->loadFromBoq($boq);
                $this->dispatch('boq-resume-done');
                return;
            }
        }

        // ── Resume latest draft (user returned via the floating pill) ─────────
        if (request()->query('resume') === '1' && $projectUuid === null) {
            $latestDraft = Boq::where('client_id', Auth::id())
                ->where('status', BoqStatusEnum::Draft)
                ->with(['project', 'items.unit'])
                ->latest()
                ->first();

            if ($latestDraft) {
                $this->loadFromBoq($latestDraft);
                $aiStatus = Cache::get('boq_ai_status_' . Auth::id());

                if (count($this->items) > 0) {
                    // Items already saved → AI finished successfully
                    $this->dispatch('boq-upload-done');
                } elseif ($aiStatus === 'running' && ! $this->isJobTimedOut()) {
                    // Job still genuinely in progress — start polling
                    $this->processing = true;
                } elseif ($aiStatus === 'running' && $this->isJobTimedOut()) {
                    // Stale 'running' flag — job timed out (worker not running?)
                    Cache::put('boq_ai_status_' . Auth::id(), 'failed', now()->addMinutes(30));
                    Cache::put('boq_ai_message_' . Auth::id(), 'Processing timed out. The background worker may not be running. Please try extracting again.', now()->addMinutes(30));
                    $this->dispatch('boq-upload-done');
                    $this->dispatch('toast', message: 'Previous extraction timed out. Please try again.', type: 'error');
                } elseif ($aiStatus === 'done') {
                    // Done but no items saved (edge case) — still clear the pill
                    $this->dispatch('boq-upload-done');
                } elseif (in_array($aiStatus, ['failed', 'no_items'], true)) {
                    $msg = (string) Cache::get('boq_ai_message_' . Auth::id(), 'AI extraction finished. Please add items manually.');
                    $this->dispatch('boq-upload-done');
                    $this->dispatch('toast', message: $msg, type: $aiStatus === 'no_items' ? 'warning' : 'error');
                    Cache::forget('boq_ai_status_' . Auth::id());
                }
                // No cache status at all → just show the draft as-is
                $this->dispatch('boq-resume-done');
                return;
            }
        }

        if ($projectUuid !== null) {
            $project = Project::where('uuid', $projectUuid)
                ->where('client_id', Auth::id())
                ->firstOrFail();

            $this->projectId          = $project->id;
            $this->projectName        = (string) $project->name;
            $this->projectDescription = (string) ($project->description ?? '');
            $this->isEditMode         = true;
            return;
        }

        // ── Auto-resume: if there's an active AI job in cache, restore state ─
        // (handles the case where the user navigates back to /boqs/create
        //  without ?resume=1, e.g. via the pill or direct link)
        $aiStatus = Cache::get('boq_ai_status_' . Auth::id());
        if ($aiStatus) {
            $latestDraft = Boq::where('client_id', Auth::id())
                ->where('status', BoqStatusEnum::Draft)
                ->with(['project', 'items.unit'])
                ->latest()
                ->first();

            if ($latestDraft) {
                $this->loadFromBoq($latestDraft);
                if ($aiStatus === 'running' && ! $this->isJobTimedOut()) {
                    // Job still genuinely in progress — start polling
                    $this->processing = true;
                } elseif ($aiStatus === 'running' && $this->isJobTimedOut()) {
                    // Stale 'running' flag — timed out
                    Cache::put('boq_ai_status_' . Auth::id(), 'failed', now()->addMinutes(30));
                    $this->dispatch('boq-upload-done');
                    $this->dispatch('toast', message: 'Previous extraction timed out. Please try again.', type: 'error');
                } elseif ($aiStatus === 'done') {
                    // Job already finished — fire done event to clear the pill
                    $this->dispatch('boq-upload-done');
                } elseif (in_array($aiStatus, ['failed', 'no_items'], true)) {
                    // Job finished with a problem — clear pill and notify user
                    $msg = (string) Cache::get('boq_ai_message_' . Auth::id(), 'AI extraction finished. Please review or add items manually.');
                    $this->dispatch('boq-upload-done');
                    $this->dispatch('toast', message: $msg, type: $aiStatus === 'no_items' ? 'warning' : 'error');
                    // Clear stale cache so this block doesn't re-fire on next visit
                    Cache::forget('boq_ai_status_' . Auth::id());
                }
                $this->dispatch('boq-resume-done');
            }
        }
    }

    /** Returns true if the AI job started > 5 minutes ago (i.e. it timed out). */
    private function isJobTimedOut(): bool
    {
        $startedAt = Cache::get('boq_ai_started_at_' . Auth::id());
        return $startedAt !== null && (now()->timestamp - $startedAt) > 300;
    }

    private function loadFromBoq(Boq $boq): void
    {
        $this->boqId        = $boq->id;
        $this->draftBoqUuid = $boq->uuid;
        $this->projectId    = $boq->project_id;
        $this->boqType      = $boq->type->value;
        $this->isEditMode   = true;

        if ($boq->project) {
            $this->projectName        = (string) $boq->project->name;
            $this->projectDescription = (string) ($boq->project->description ?? '');
        }

        // Load the last uploaded file name
        $lastDoc = UploadedDocument::where('boq_id', $boq->id)->latest()->first();
        if ($lastDoc) {
            $this->boqFileName = $lastDoc->file_name;
        }

        $this->items = $boq->items
            ->filter(fn(BoqItem $item) => $item->status->value !== 'rejected')
            ->map(fn(BoqItem $item) => [
                'id'                   => $item->id,
                'description'          => (string) $item->description,
                'quantity'             => (float) $item->quantity,
                'unit'                 => $item->unit?->name ?? '',
                'unit_price'           => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'category'             => (string) ($item->category ?? ''),
                'brand'                => (string) ($item->brand ?? ''),
                'status'               => $item->status->value ?? 'pending',
                'engineering_required' => (bool) $item->engineering_required,
                'confidence'           => is_numeric($item->confidence) ? (float) $item->confidence : null,
                'raw_data'             => $item->raw_data,
                'ai_extracted'         => (bool) $item->ai_extracted,
                'is_selected'          => (bool) $item->is_selected,
            ])->values()->toArray();
    }

    // -------------------------------------------------------------------------
    // BOQ Upload & AI extraction
    // -------------------------------------------------------------------------

    public function uploadBoq(): void
    {
        set_time_limit(300);

        $this->validate([
            'projectName'        => 'required|string|max:255',
            'projectDescription' => 'nullable|string|max:5000',
        ]);

        // ── Resolve fallback storage path if no new file was selected ────────
        $fallbackStoragePath = null;
        if (! $this->boqFile) {
            if ($this->boqId !== null) {
                $doc = UploadedDocument::where('boq_id', $this->boqId)->latest()->first();
                if ($doc && Storage::disk('local')->exists($doc->file_path)) {
                    $fallbackStoragePath = $doc->file_path;
                }
            }

            if (! $fallbackStoragePath) {
                $this->addError('boqFile', 'Please select a file to upload.');
                return;
            }
        }

        if ($this->boqFile) {
            $extension = strtolower($this->boqFile->getClientOriginalExtension());
            if (! in_array($extension, ['pdf', 'xlsx', 'xlsm', 'xlsb', 'xls', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'], true)) {
                $this->addError('boqFile', 'The file must be of type: pdf, xlsx, xlsm, xls, csv, or an image.');
                return;
            }
        }

        try {
            $project = $this->persistProject();
            $boq     = $this->persistBoq($project);

            // ── Store the uploaded file ──────────────────────────────────────
            if ($this->boqFile) {
                $extension  = strtolower($this->boqFile->getClientOriginalExtension());
                $fileName   = $this->boqFile->getClientOriginalName();
                $storedPath = $this->boqFile->storeAs('boq-uploads', Str::uuid() . '.' . $extension, 'local');
                $fileSize   = Storage::disk('local')->size($storedPath);

                if ($fileSize > 50 * 1024 * 1024) {
                    Storage::disk('local')->delete($storedPath);
                    $this->addError('boqFile', 'The file must not be larger than 50 MB.');
                    return;
                }

                UploadedDocument::create([
                    'boq_id'      => $boq->id,
                    'project_id'  => $project->id,
                    'uploaded_by' => Auth::id(),
                    'file_name'   => $fileName,
                    'file_path'   => $storedPath,
                    'file_type'   => 'boq',
                    'file_size'   => $fileSize,
                ]);

                $this->boqFileName = $fileName;
            } else {
                $storedPath = $fallbackStoragePath;
            }

            $this->boqFile = null;

            // ── Call AI service (synchronous — runs in the same request) ──────
            $absPath = Storage::disk('local')->path($storedPath);
            $ai      = app(QuotationAiService::class);
            $result  = $ai->parseBoq($absPath, [
                'boq_id'       => $boq->id,
                'project_name' => $this->projectName,
            ]);

            if (! $result['success']) {
                $this->dispatch('boq-upload-done');
                $this->dispatch('toast', message: $result['error'] ?? 'Extraction failed. Please try again.', type: 'error');
                return;
            }

            if (empty($result['items'])) {
                $this->dispatch('boq-upload-done');
                $this->dispatch('toast', message: 'No items found in the file. Please add items manually.', type: 'warning');
                return;
            }

            // ── Persist extracted items ───────────────────────────────────────
            BoqItem::where('boq_id', $boq->id)->delete();
            foreach ($result['items'] as $aiItem) {
                $item = array_merge([
                    'description'          => '',
                    'quantity'             => 1,
                    'unit_id'              => null,
                    'category'             => '',
                    'brand'                => '',
                    'status'               => 'pending',
                    'engineering_required' => false,
                    'confidence'           => null,
                    'unit_price'           => null,
                    'raw_data'             => null,
                    'ai_extracted'         => true,
                    'is_selected'          => true,
                ], $aiItem);

                BoqItem::create([
                    'boq_id'               => $boq->id,
                    'description'          => (string) ($item['description'] ?? ''),
                    'quantity'             => is_numeric($item['quantity']) ? (float) $item['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($item['unit_id'] ?? null, $item['unit'] ?? null),
                    'category'             => (string) ($item['category'] ?? ''),
                    'brand'                => (string) ($item['brand'] ?? ''),
                    'status'               => $item['status'] ?? 'pending',
                    'engineering_required' => (bool) ($item['engineering_required'] ?? false),
                    'confidence'           => is_numeric($item['confidence'] ?? null) ? (float) $item['confidence'] : null,
                    'unit_price'           => is_numeric($item['unit_price'] ?? null) ? (float) $item['unit_price'] : null,
                    'raw_data'             => $item['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'is_selected'          => true,
                ]);
            }

            // ── Reload items into component state ─────────────────────────────
            $boq = Boq::where('id', $boq->id)->with(['items.unit'])->first();
            if ($boq) {
                $this->loadFromBoq($boq);
            }

            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: count($this->items) . ' items extracted from your file.', type: 'success');

            // Advance wizard to step 2 (item confirmation)
            $this->currentStep = 2;

        } catch (\Throwable $e) {
            Log::error('CreateBoq::uploadBoq failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: 'Upload failed. Please try again.', type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // AI status polling (called by wire:poll every 4 s while $processing)
    // -------------------------------------------------------------------------

    public function checkAiStatus(): void
    {
        if (! $this->processing || $this->boqId === null) {
            return;
        }

        // ── Auto-fail if the job has been running for more than 5 minutes ────
        $startedAt = Cache::get('boq_ai_started_at_' . Auth::id());
        if ($startedAt && (now()->timestamp - $startedAt) > 300) {
            Cache::put('boq_ai_status_' . Auth::id(), 'failed', now()->addMinutes(30));
            Cache::put('boq_ai_message_' . Auth::id(),
                'Processing timed out after 5 minutes. The file may be too large, or the background worker may not be running. Please try again.',
                now()->addMinutes(30)
            );
        }

        $status  = Cache::get('boq_ai_status_' . Auth::id());
        $message = (string) Cache::get('boq_ai_message_' . Auth::id(), '');

        if ($status === 'done') {
            $boq = Boq::where('id', $this->boqId)->with(['items.unit'])->first();
            if ($boq) {
                $this->loadFromBoq($boq);
            }
            $this->processing = false;
            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: $message ?: (count($this->items) . ' items extracted from your file.'), type: 'success');
            $this->currentStep = 2;
        } elseif ($status === 'no_items') {
            $this->processing = false;
            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: $message ?: 'No items found in the file. Please add items manually.', type: 'warning');
        } elseif ($status === 'failed') {
            $this->processing = false;
            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: $message ?: 'Extraction failed. Please try uploading the file again.', type: 'error');
        } elseif ($status === null) {
            // Cache expired or was cleared while processing — treat as failure.
            $this->processing = false;
            $this->dispatch('boq-upload-done');
            $this->dispatch('toast', message: 'AI extraction status expired. Please try extracting again.', type: 'error');
        }
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
            'is_selected'          => true,
        ];
    }

    public function updateItem(int $index, string $field, mixed $value): void
    {
        $allowed = ['description', 'quantity', 'unit', 'category', 'brand', 'engineering_required', 'is_selected'];

        if (! array_key_exists($index, $this->items) || ! in_array($field, $allowed, true)) {
            return;
        }

        if ($field === 'quantity') {
            $value = is_numeric($value) ? max(0, (float) $value) : 0;
        }

        if ($field === 'engineering_required') {
            $value = (bool) $value;
        }

        if ($field === 'is_selected') {
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
            BoqItem::where('id', $this->items[$index]['id'])
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
            BoqItem::where('id', $this->items[$index]['id'])
                ->update(['status' => QuotationItemStatusEnum::Rejected]);
        }
    }

    public function approveAllItems(): void
    {
        $ids = [];

        foreach ($this->items as $index => $item) {
            $this->items[$index]['status']      = QuotationItemStatusEnum::Sourcing->value;
            $this->items[$index]['is_selected'] = true;

            if (! empty($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }

        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update([
                'status' => QuotationItemStatusEnum::Sourcing,
            ]);
        }

        $this->dispatch('toast', message: 'All items approved successfully.', type: 'success');
    }

    public function selectAllItems(): void
    {
        foreach ($this->items as $index => $item) {
            if (($item['status'] ?? '') !== 'rejected') {
                $this->items[$index]['is_selected'] = true;
            }
        }
    }

    public function deselectAllItems(): void
    {
        foreach ($this->items as $index => $item) {
            $this->items[$index]['is_selected'] = false;
        }
    }

    public function deleteItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        if (! empty($this->items[$index]['id'])) {
            BoqItem::where('id', $this->items[$index]['id'])->delete();
        }

        array_splice($this->items, $index, 1);
    }

    public function clearAllItems(): void
    {
        if ($this->boqId !== null) {
            BoqItem::where('boq_id', $this->boqId)->delete();
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
            'projectName'        => 'required|string|max:255',
            'projectDescription' => 'nullable|string|max:5000',
        ]);

        $project = $this->persistProject();
        $boq     = $this->persistBoq($project);
        $this->persistItems($boq);

        $this->dispatch('toast', message: 'Draft saved successfully.', type: 'success');
    }

    public function submit(): void
    {
        if ($this->processing) {
            $this->dispatch('toast', message: 'Please wait for AI extraction to complete before submitting.', type: 'error');
            return;
        }

        $this->validate([
            'projectName'        => 'required|string|max:255',
            'projectDescription' => 'nullable|string|max:5000',
        ]);

        if (empty($this->items)) {
            $this->dispatch('toast', message: 'Please add at least one item before submitting.', type: 'error');
            return;
        }

        $project = $this->persistProject();
        $boq     = $this->persistBoq($project, BoqStatusEnum::Submitted);
        $this->persistItems($boq);

        app(NotificationService::class)->sendToUserAndAdmins(
            title: 'BOQ Submitted',
            body: 'BOQ for "' . $this->projectName . '" has been submitted with ' . count($this->items) . ' items.',
            type: NotificationTypeEnum::BoqSubmitted,
            userId: Auth::id(),
            actionUrl: route('enduser.boqs.show', $boq->uuid),
        );

        $this->redirect(route('enduser.boqs.show', $boq->uuid));
    }

    // -------------------------------------------------------------------------
    // Wizard step navigation
    // -------------------------------------------------------------------------

    public function goBack(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /** Step 2 → 3: create quotation request then fetch prices synchronously. */
    public function confirmItems(): void
    {
        if (empty($this->items)) {
            $this->dispatch('toast', message: 'Please add at least one item before proceeding.', type: 'error');
            return;
        }

        $activeItems = array_values(array_filter(
            $this->items,
            fn($i) => ($i['status'] ?? 'pending') !== 'rejected' && !empty($i['is_selected'])
        ));

        if (empty($activeItems)) {
            $this->dispatch('toast', message: 'يرجى تحديد عنصر واحد على الأقل للتسعير.', type: 'error');
            return;
        }

        try {
            DB::transaction(function () use ($activeItems) {
                // Persist project + BOQ
                $project = $this->persistProject();
                $boq     = $this->persistBoq($project, BoqStatusEnum::Submitted);
                $this->persistItems($boq);

                // ── Create QuotationRequest ────────────────────────────────
                $quotation = QuotationRequest::create([
                    'client_id'    => Auth::id(),
                    'project_id'   => $project->id,
                    'boq_id'       => $boq->id,
                    'quotation_no' => $this->generateQuotationNo(),
                    'project_name' => $this->projectName,
                    'status'       => QuotationRequestStatusEnum::Tender,
                    'source_type'  => QuotationSourceTypeEnum::Boq,
                ]);

                $this->quotationId   = $quotation->id;
                $this->quotationUuid = $quotation->uuid;

                // ── Create QuotationItems ─────────────────────────────────
                foreach ($activeItems as $row) {
                    $unitName = trim((string) ($row['unit'] ?? ''));
                    $unitId   = null;
                    if ($unitName !== '') {
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
                        'price_status'         => 'pending',
                        'category'             => (string) ($row['category'] ?? ''),
                        'brand'                => (string) ($row['brand'] ?? ''),
                        'status'               => 'pending',
                        'engineering_required' => (bool) ($row['engineering_required'] ?? false),
                        'confidence'           => is_numeric($row['confidence'] ?? null) ? (float) $row['confidence'] : null,
                        'ai_extracted'         => (bool) ($row['ai_extracted'] ?? false),
                        'is_selected'          => true,
                    ]);
                }
            });

            // ── Load items for display (no prices yet) ──────────────────────
            $qItems = QuotationItem::where('quotation_request_id', $this->quotationId)->with('unit')->get();
            $this->pricedItems  = $qItems->map(fn($qi) => [
                'description' => (string) $qi->description,
                'quantity'    => (float) $qi->quantity,
                'unit'        => $qi->unit?->name ?? '',
                'unit_price'  => null,
                'line_total'  => 0,
                'category'    => (string) ($qi->category ?? ''),
            ])->toArray();
            $this->quotationTotal = 0;
            $this->pricedCount    = 0;
            $this->unpricedCount  = count($this->pricedItems);

            // ── Dispatch pricing job (async) ─────────────────────────────────
            FetchQuotationPricesJob::dispatch($this->quotationId, Auth::id(), $this->quotationUuid);
            $this->pricesFetching = true;

            $this->currentStep = 3;

        } catch (\Throwable $e) {
            Log::error('CreateBoq::confirmItems failed.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', message: 'Failed to process items. Please try again.', type: 'error');
        }
    }

    /** Poll called every 5s while prices are being fetched in the background. */
    public function pollPriceStatus(): void
    {
        if (! $this->pricesFetching || ! $this->quotationId) {
            return;
        }

        $quotation = QuotationRequest::find($this->quotationId);
        if (! $quotation || ! $quotation->prices_fetched_at) {
            return;
        }

        $refreshed         = QuotationItem::where('quotation_request_id', $this->quotationId)->with('unit')->get();
        $this->pricedItems = [];
        $total             = 0;
        $pricedCount       = 0;
        $unpricedCount     = 0;

        foreach ($refreshed as $qi) {
            $unitPrice = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : null;
            $quantity  = (float) $qi->quantity;
            $lineTotal = $unitPrice !== null ? round($unitPrice * $quantity, 2) : 0;

            $this->pricedItems[] = [
                'description' => (string) $qi->description,
                'quantity'    => $quantity,
                'unit'        => $qi->unit?->name ?? '',
                'unit_price'  => $unitPrice,
                'line_total'  => $lineTotal,
                'category'    => (string) ($qi->category ?? ''),
            ];

            if ($unitPrice !== null) {
                $pricedCount++;
                $total += $lineTotal;
            } else {
                $unpricedCount++;
            }
        }

        $this->quotationTotal = round($total, 2);
        $this->pricedCount    = $pricedCount;
        $this->unpricedCount  = $unpricedCount;
        $this->pricesFetching = false;
    }

    /** Step 3 → 4: go to address & payment. */
    public function proceedToAddress(): void
    {
        $this->currentStep = 4;
    }

    /** Validate address fields and close the modal (dispatches event to Alpine). */
    public function saveAddress(): void
    {
        if ($this->deliveryAddressMode === 'national') {
            $this->validate([
                'deliveryShortAddress' => ['required', 'regex:/^[A-Za-z0-9]{8}$/'],
            ], [
                'deliveryShortAddress.required' => 'العنوان القصير مطلوب.',
                'deliveryShortAddress.regex'    => 'العنوان القصير يجب أن يكون 8 خانات (حروف وأرقام إنجليزية).',
            ]);
        } else {
            $this->validate([
                'deliveryBuildingNo'   => 'nullable|regex:/^\d{0,4}$/',
                'deliveryStreet'       => 'required|string|max:200',
                'deliveryDistrict'     => 'required|string|max:100',
                'deliveryCity'         => 'required|string|max:100',
                'deliveryRegion'       => 'required|string|max:100',
                'deliveryPostalCode'   => ['required', 'regex:/^\d{5}$/'],
                'deliveryAdditionalNo' => 'nullable|regex:/^\d{0,4}$/',
            ], [
                'deliveryStreet.required'     => 'اسم الشارع مطلوب.',
                'deliveryDistrict.required'   => 'الحي مطلوب.',
                'deliveryCity.required'       => 'المدينة مطلوبة.',
                'deliveryRegion.required'     => 'المنطقة مطلوبة.',
                'deliveryPostalCode.required' => 'الرمز البريدي مطلوب.',
                'deliveryPostalCode.regex'    => 'الرمز البريدي يجب أن يكون 5 أرقام.',
                'deliveryBuildingNo.regex'    => 'رقم المبنى يجب أن يكون 4 أرقام كحد أقصى.',
                'deliveryAdditionalNo.regex'  => 'الرقم الإضافي يجب أن يكون 4 أرقام كحد أقصى.',
            ]);
        }

        $this->dispatch('address-saved');
    }

    /** Step 4 → 5: validate address, create order. */
    public function placeOrder(): void
    {
        if ($this->deliveryAddressMode === 'national') {
            $this->validate([
                'deliveryShortAddress' => ['required', 'regex:/^[A-Za-z0-9]{8}$/'],
                'paymentMethod'        => 'required|string|in:bank_transfer,cash,credit',
            ], [
                'deliveryShortAddress.required' => 'العنوان القصير مطلوب.',
                'deliveryShortAddress.regex'    => 'العنوان القصير يجب أن يكون 8 خانات (حروف وأرقام إنجليزية).',
            ]);
        } else {
            $this->validate([
                'deliveryStreet'     => 'required|string|max:200',
                'deliveryDistrict'   => 'required|string|max:100',
                'deliveryCity'       => 'required|string|max:100',
                'deliveryRegion'     => 'required|string|max:100',
                'deliveryPostalCode' => ['required', 'regex:/^\d{5}$/'],
                'paymentMethod'      => 'required|string|in:bank_transfer,cash,credit',
            ], [
                'deliveryStreet.required'     => 'اسم الشارع مطلوب.',
                'deliveryDistrict.required'   => 'الحي مطلوب.',
                'deliveryCity.required'       => 'المدينة مطلوبة.',
                'deliveryRegion.required'     => 'المنطقة مطلوبة.',
                'deliveryPostalCode.required' => 'الرمز البريدي مطلوب.',
                'deliveryPostalCode.regex'    => 'الرمز البريدي يجب أن يكون 5 أرقام.',
            ]);
        }

        if (! $this->quotationId) {
            $this->dispatch('toast', message: 'Quotation not found. Please go back and try again.', type: 'error');
            return;
        }

        try {
            $order = DB::transaction(function () {
                $quotation = QuotationRequest::findOrFail($this->quotationId);

                // ── Create a QuotationVersion (self-accepted) ──────────────
                $version = QuotationVersion::create([
                    'quotation_request_id' => $quotation->id,
                    'version_number'       => 1,
                    'prepared_by'          => Auth::id(),
                    'status'               => QuotationVersionStatusEnum::Accepted,
                    'valid_until'          => now()->addDays(30)->toDateString(),
                    'notes'                => 'Auto-generated from BOQ wizard',
                ]);

                // ── Build version items + compute totals ───────────────────
                $quotationItems = QuotationItem::where('quotation_request_id', $quotation->id)->get();
                $totalAmount    = 0;

                foreach ($quotationItems as $qi) {
                    $unitPrice  = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : 0;
                    $quantity   = (float) $qi->quantity;
                    $totalPrice = round($unitPrice * $quantity, 2);

                    QuotationVersionItem::create([
                        'quotation_version_id' => $version->id,
                        'quotation_item_id'    => $qi->id,
                        'product_id'           => $qi->product_id,
                        'description'          => (string) $qi->description,
                        'quantity'             => $quantity,
                        'unit_id'              => $qi->unit_id,
                        'unit_price'           => $unitPrice,
                        'total_price'          => $totalPrice,
                        'price_source'         => $qi->price_source ?? 'manual',
                        'vat_rate'             => 15,
                    ]);

                    $totalAmount += $totalPrice;
                }

                $vatAmount  = round($totalAmount * 0.15, 2);
                $grandTotal = round($totalAmount + $vatAmount, 2);

                // ── Create Order ───────────────────────────────────────────
                $order = Order::create([
                    'order_no'                => $this->generateOrderNo(),
                    'quotation_request_id'    => $quotation->id,
                    'quotation_version_id'    => $version->id,
                    'client_id'               => Auth::id(),
                    'project_id'              => $quotation->project_id,
                    'status'                  => OrderStatusEnum::Open,
                    'total_amount'            => $totalAmount,
                    'vat_amount'              => $vatAmount,
                    'grand_total'             => $grandTotal,
                    'currency'                => 'SAR',
                    'delivery_address_type'   => $this->deliveryAddressMode,
                    'delivery_short_address'  => $this->deliveryAddressMode === 'national' ? strtoupper($this->deliveryShortAddress) : null,
                    'delivery_building_no'    => $this->deliveryBuildingNo,
                    'delivery_street'         => $this->deliveryStreet,
                    'delivery_district'       => $this->deliveryDistrict,
                    'delivery_city'           => $this->deliveryCity,
                    'delivery_region'         => $this->deliveryRegion,
                    'delivery_postal_code'    => $this->deliveryPostalCode,
                    'delivery_additional_no'  => $this->deliveryAdditionalNo,
                    'delivery_country'        => 'SA',
                    'notes'                   => 'Payment: ' . $this->paymentMethod,
                ]);

                // ── Create OrderItems ──────────────────────────────────────
                foreach ($quotationItems as $qi) {
                    $unitPrice  = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : 0;
                    $quantity   = (float) $qi->quantity;
                    $totalPrice = round($unitPrice * $quantity, 2);

                    OrderItem::create([
                        'order_id'    => $order->id,
                        'product_id'  => $qi->product_id,
                        'description' => (string) $qi->description,
                        'quantity'    => $quantity,
                        'unit_id'     => $qi->unit_id,
                        'unit_price'  => $unitPrice,
                        'total_price' => $totalPrice,
                        'vat_rate'    => 15,
                        'discount_pct' => 0,
                    ]);
                }

                // ── Mark quotation as submitted ────────────────────────────
                $quotation->update(['status' => QuotationRequestStatusEnum::UnderReview]);

                return $order;
            });

            $this->orderUuid       = $order->uuid;
            $this->orderNo         = $order->order_no;
            $this->orderGrandTotal = (float) $order->grand_total;
            $this->currentStep     = 5;

            app(NotificationService::class)->sendToUserAndAdmins(
                title: 'طلب جديد',
                body: 'تم إنشاء طلب جديد رقم ' . $this->orderNo . ' من مشروع "' . $this->projectName . '".',
                type: NotificationTypeEnum::BoqSubmitted,
                userId: Auth::id(),
                actionUrl: route('enduser.orders.show', $this->orderUuid),
            );

        } catch (\Throwable $e) {
            Log::error('CreateBoq::placeOrder failed.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', message: 'Failed to place order. Please try again.', type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.enduser.boqs.create-boq', [
            'itemStatuses' => QuotationItemStatusEnum::cases(),
            'boqTypes'     => BoqTypeEnum::cases(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function persistProject(): Project
    {
        if ($this->projectId !== null) {
            $project = Project::where('client_id', Auth::id())
                ->findOrFail($this->projectId);

            $project->update([
                'name'        => $this->projectName,
                'description' => $this->projectDescription,
            ]);

            return $project;
        }

        $project = Project::create([
            'client_id'   => Auth::id(),
            'project_no'  => $this->generateProjectNo(),
            'name'        => $this->projectName,
            'description' => $this->projectDescription,
            'status'      => ProjectStatusEnum::Pending,
        ]);

        $this->projectId = $project->id;

        return $project;
    }

    private function persistBoq(Project $project, ?BoqStatusEnum $status = null): Boq
    {
        if ($this->boqId !== null) {
            $boq = Boq::where('client_id', Auth::id())
                ->findOrFail($this->boqId);

            $attrs = [];
            if ($status !== null) {
                $attrs['status'] = $status;
            }
            if (! empty($attrs)) {
                $boq->update($attrs);
            }

            $this->draftBoqUuid = $boq->uuid;

            return $boq;
        }

        $boq = Boq::create([
            'project_id' => $project->id,
            'client_id'  => Auth::id(),
            'boq_no'     => $this->generateBoqNo(),
            'status'     => $status ?? BoqStatusEnum::Draft,
            'type'       => $this->boqType,
        ]);

        $this->boqId        = $boq->id;
        $this->draftBoqUuid = $boq->uuid;

        return $boq;
    }

    private function persistItems(Boq $boq): void
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
                'boq_id'               => $boq->id,
                'description'          => (string) ($row['description'] ?? ''),
                'quantity'             => is_numeric($row['quantity'] ?? null) ? (float) $row['quantity'] : 0,
                'unit_price'           => is_numeric($row['unit_price'] ?? null) && (float) $row['unit_price'] > 0 ? (float) $row['unit_price'] : null,
                'unit_id'              => $unitId,
                'category'             => (string) ($row['category'] ?? ''),
                'brand'                => (string) ($row['brand'] ?? ''),
                'status'               => $row['status'] ?? 'pending',
                'engineering_required' => (bool) ($row['engineering_required'] ?? false),
                'confidence'           => is_numeric($row['confidence'] ?? null) ? (float) $row['confidence'] : null,
                'raw_data'             => $row['raw_data'] ?? null,
                'ai_extracted'         => (bool) ($row['ai_extracted'] ?? false),
                'is_selected'          => (bool) ($row['is_selected'] ?? false),
            ];

            if (! empty($row['id'])) {
                $item = BoqItem::find($row['id']);
                if ($item && $item->boq_id === $boq->id) {
                    $item->update($data);
                    continue;
                }
            }

            $created = BoqItem::create($data);
            $this->items[$index]['id'] = $created->id;
        }
    }

    private function generateProjectNo(): string
    {
        $prefix = 'PRJ-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (Project::where('project_no', $candidate)->exists());

        return $candidate;
    }

    private function generateBoqNo(): string
    {
        $prefix = 'BOQ-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (Boq::where('boq_no', $candidate)->exists());

        return $candidate;
    }

    private function generateQuotationNo(): string
    {
        $prefix = 'QR-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (QuotationRequest::where('quotation_no', $candidate)->exists());

        return $candidate;
    }

    private function generateOrderNo(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (Order::where('order_no', $candidate)->exists());

        return $candidate;
    }

    private function resolveUnitId(?int $unitId, mixed $unitText): ?int
    {
        if ($unitId !== null) {
            return $unitId;
        }

        $label = trim((string) ($unitText ?? ''));
        if ($label === '') {
            return null;
        }

        return Unit::firstOrCreate(
            ['name' => $label],
            ['symbol' => mb_strtolower(mb_substr($label, 0, 20))]
        )->id;
    }
}
