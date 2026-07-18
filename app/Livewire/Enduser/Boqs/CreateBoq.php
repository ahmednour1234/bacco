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
use App\Jobs\ExtractBoqItemsJob;
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
use App\Services\Catalog\SaveQuotationProductsToCatalog;
use App\Services\NotificationService;
use App\Services\SpecValidationService;
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

    /** Interstitial spec-questions step, shown between confirmation (2) and pricing (3). */
    public const STEP_QUESTIONS = 25;

    /**
     * Seconds before a queued extraction is treated as dead. Generous, because a
     * multi-thousand-row BOQ genuinely takes many minutes; it only trips when the
     * job is stuck or no queue worker is running.
     */
    private const EXTRACTION_TIMEOUT = 1800;

    /**
     * Items awaiting user answers before pricing, built during confirmItems().
     * Shape: [{id, description, validation_status, suggested_unit, unit,
     *          missing_specs:[{key,question,example}], answers:{key:value}}]
     * @var array<int, array<string, mixed>>
     */
    public array $questionItems = [];

    public ?int $boqId = null;
    public ?int $projectId = null;
    public string $draftBoqUuid = '';

    public bool $isEditMode = false;

    // ── Guest mode (unauthenticated try flow) ─────────────────────────────
    public bool    $guestMode              = false;
    public ?string $guestToken             = null;
    public bool    $showGuestLoginOverlay  = false;

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
        // ── Guest mode: skip all auth-dependent resume logic ──────────────────
        if ($this->guestMode) {
            if (empty($this->guestToken)) {
                $this->guestToken = (string) \Illuminate\Support\Str::uuid();
            }

            // Restore boqId/projectId from DB in case Livewire state was lost on refresh
            if ($this->guestToken && $this->boqId === null) {
                $existingBoq = Boq::where('guest_token', $this->guestToken)->first();
                if ($existingBoq) {
                    $this->boqId        = $existingBoq->id;
                    $this->draftBoqUuid = $existingBoq->uuid;
                    $this->projectId    = $existingBoq->project_id;
                    $this->boqType      = $existingBoq->type->value ?? $this->boqType;
                }
            }
            return;
        }

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

    /** Returns true if the queued extraction has outlived EXTRACTION_TIMEOUT. */
    private function isJobTimedOut(): bool
    {
        $startedAt = Cache::get($this->cacheKeyFor('boq_ai_started_at'));
        return $startedAt !== null && (now()->timestamp - $startedAt) > self::EXTRACTION_TIMEOUT;
    }

    /** Build a per-user (or per-guest) cache key. */
    private function cacheKeyFor(string $type): string
    {
        $suffix = $this->guestMode ? $this->guestToken : Auth::id();
        return $type . '_' . $suffix;
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
        @set_time_limit(480);

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
            if (! in_array($extension, ['pdf', 'xlsx', 'xlsm', 'xlsb', 'xls', 'csv', 'docx', 'doc', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'], true)) {
                $this->addError('boqFile', 'The file must be of type: pdf, xlsx, xlsm, xls, csv, docx, or an image.');
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
                    'uploaded_by' => $this->guestMode ? null : Auth::id(),
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

            // ── Queue the AI extraction ───────────────────────────────────────
            // Large BOQs take minutes to parse. Running that inline exceeds the
            // request timeout, which kills the Livewire request and makes the
            // browser retry as a plain POST (405 on the GET-only /try route).
            // Dispatch instead and let checkAiStatus() poll for the result.
            Cache::put($this->cacheKeyFor('boq_ai_status'), 'pending', now()->addHours(2));
            Cache::put($this->cacheKeyFor('boq_ai_message'), '', now()->addHours(2));
            Cache::put($this->cacheKeyFor('boq_ai_started_at'), now()->timestamp, now()->addHours(2));

            ExtractBoqItemsJob::dispatch(
                $boq->id,
                $storedPath,
                $this->projectName,
                (string) ($this->guestMode ? $this->guestToken : Auth::id()),
            );

            // Start the polling UI; checkAiStatus() advances to step 2 when done.
            $this->processing = true;

            $this->dispatch('toast', message: __('app.boq_extraction_queued'), type: 'info');

        } catch (\Throwable $e) {
            Log::error('CreateBoq::uploadBoq failed.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $this->dispatch('boq-upload-done');
            $msg = app()->isProduction()
                ? 'Upload failed. Please try again.'
                : 'Upload failed: ' . $e->getMessage();
            $this->dispatch('toast', message: $msg, type: 'error');
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

        // ── Auto-fail a job that has outlived the extraction window ──────────
        // Large BOQs legitimately run for many minutes, so this only trips when
        // the job is genuinely stuck (or no worker is consuming the queue).
        $startedAt = Cache::get($this->cacheKeyFor('boq_ai_started_at'));
        if ($startedAt && (now()->timestamp - $startedAt) > self::EXTRACTION_TIMEOUT) {
            Cache::put($this->cacheKeyFor('boq_ai_status'), 'failed', now()->addMinutes(30));
            Cache::put($this->cacheKeyFor('boq_ai_message'),
                __('app.boq_extraction_timeout'),
                now()->addMinutes(30)
            );
        }

        $status  = Cache::get($this->cacheKeyFor('boq_ai_status'));
        $message = (string) Cache::get($this->cacheKeyFor('boq_ai_message'), '');

        // Queued but not picked up yet — keep polling, nothing to report.
        if ($status === 'pending' || $status === 'running') {
            return;
        }

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

        // Keep a record of the BOQ's products in the catalog. The catalog lives
        // on a separate DB connection, so a failure here must not abort the
        // submission — log and continue.
        try {
            app(SaveQuotationProductsToCatalog::class)->handleBoq($boq, $this->items);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save BOQ products to catalog', [
                'boq_id' => $boq->id,
                'error'  => $e->getMessage(),
            ]);
        }

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
                    'client_id'    => $this->guestMode ? null : Auth::id(),
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

            // ── Spec validation pass (before pricing) ───────────────────────
            // Audits unit correctness + spec completeness, then resolves every
            // finding itself: wrong units are corrected, missing specs are filled
            // from the AI's own suggestions, and anything still unpriceable is
            // dropped. The user is never shown a row to fix.
            $this->runSpecValidation();

            $this->runPricing();

        } catch (\Throwable $e) {
            Log::error('CreateBoq::confirmItems failed.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', message: 'Failed to process items. Please try again.', type: 'error');
        }
    }

    /**
     * Validate the just-created quotation items for unit/spec issues and persist
     * the verdict on both the QuotationItem and its BoqItem. When any item needs
     * more information, populate $questionItems and switch to the questions step.
     *
     * @return bool  true if the flow paused for questions (caller must return).
     */
    private function runSpecValidation(): bool
    {
        $qItems = QuotationItem::where('quotation_request_id', $this->quotationId)
            ->with('unit')
            ->get();

        $payload = $qItems->map(fn($qi) => [
            'id'          => $qi->id,
            'description' => (string) $qi->description,
            'unit'        => $qi->unit?->name ?? '',
            'quantity'    => (float) $qi->quantity,
            'category'    => (string) ($qi->category ?? ''),
            'brand'       => (string) ($qi->brand ?? ''),
        ])->values()->toArray();

        try {
            $validated = app(SpecValidationService::class)->validate($payload);
        } catch (\Throwable $e) {
            Log::error('CreateBoq: spec validation failed, continuing to pricing.', ['message' => $e->getMessage()]);
            return false; // never block the flow on a validation outage
        }

        $this->questionItems = [];
        $autoFixed   = 0;
        $dropped     = 0;
        $dropIds     = [];
        $dropDescriptions = [];

        foreach ($validated as $row) {
            $id     = $row['id'] ?? null;
            $status = $row['validation_status'] ?? 'valid';
            $specs  = is_array($row['missing_specs'] ?? null) ? $row['missing_specs'] : [];
            if ($id === null) {
                continue;
            }

            $item = QuotationItem::find($id);
            if (! $item) {
                continue;
            }

            $originalDescription = (string) ($row['description'] ?? '');

            // ── Auto-resolve with the AI's own suggestions ───────────────────
            // The reviewer already inferred the correct unit and the most likely
            // value for each missing spec, so apply them here.
            $resolved = $this->autoResolveItem($item, $status, $row['suggested_unit'] ?? null, $specs);

            if ($resolved['fixed']) {
                $autoFixed++;
                $status = 'valid';
                $specs  = [];
            } elseif ($status !== 'valid') {
                // ── Could not be resolved → drop it, never surface it ────────
                // Policy: a line is either priceable after the AI pass or it
                // leaves the quotation. We refuse to price against specs we
                // invented, and we refuse to hand the user a red row to fix.
                $dropIds[]          = $id;
                $dropDescriptions[] = $originalDescription;
                $dropped++;
                continue;
            }

            QuotationItem::where('id', $id)->update([
                'validation_status' => 'valid',
                'suggested_unit'    => null,
                'missing_specs'     => [],
                'validation_note'   => $resolved['note'] ?? ($row['validation_note'] ?? null),
                'validated_at'      => now(),
            ]);

            // Mirror the verdict onto the source BoqItem (matched by description).
            if ($this->boqId !== null) {
                BoqItem::where('boq_id', $this->boqId)
                    ->where('description', $originalDescription)
                    ->update([
                        'validation_status' => 'valid',
                        'suggested_unit'    => null,
                        'missing_specs'     => [],
                        'validation_note'   => $resolved['note'] ?? ($row['validation_note'] ?? null),
                        'validated_at'      => now(),
                    ]);
            }
        }

        // Remove the unresolvable lines in one pass.
        if (! empty($dropIds)) {
            QuotationItem::whereIn('id', $dropIds)->delete();

            if ($this->boqId !== null) {
                BoqItem::where('boq_id', $this->boqId)
                    ->whereIn('description', $dropDescriptions)
                    ->delete();
            }

            // Keep the in-memory table in sync so step 2 does not show ghosts.
            $this->items = array_values(array_filter(
                $this->items,
                fn ($i) => ! in_array((string) ($i['description'] ?? ''), $dropDescriptions, true)
            ));
        }

        if ($autoFixed > 0) {
            $this->dispatch('toast', message: __('app.spec_auto_resolved', ['count' => $autoFixed]), type: 'info');
        }

        if ($dropped > 0) {
            $this->dispatch('toast', message: __('app.spec_auto_dropped', ['count' => $dropped]), type: 'warning');
        }

        // Nothing is ever queued for the user now — pricing always proceeds.
        return false;
    }

    /**
     * Apply the AI reviewer's own corrections to a single item.
     *
     * - unit_error       → adopt the suggested unit (original kept on the BoqItem).
     * - needs_information → fold each suggested spec value into the description,
     *                       so the pricing engine sees a complete line.
     *
     * A line only counts as fixed when EVERY missing spec had a usable suggestion;
     * a partially-answered line still needs the user, otherwise we would price
     * against specs we invented.
     *
     * @param  array<int, array<string, mixed>>  $specs
     * @return array{fixed: bool, note: string|null}
     */
    private function autoResolveItem(QuotationItem $item, string $status, ?string $suggestedUnit, array $specs): array
    {
        if ($status === 'unit_error') {
            $unit = trim((string) $suggestedUnit);

            // A wrong unit is always recoverable: take the AI's correction, or
            // fall back to a countable default. Discrete goods (a panel, a UPS,
            // a desk) are quoted per piece, so this is safe — unlike inventing
            // a technical spec, it cannot distort the price.
            if ($unit === '' || $this->isVagueValue($unit)) {
                $unit = app()->getLocale() === 'ar' ? 'قطعة' : 'pcs';
            }

            $unitId = Unit::firstOrCreate(
                ['name' => $unit],
                ['symbol' => mb_strtolower(mb_substr($unit, 0, 20))]
            )->id;

            $item->update(['unit_id' => $unitId]);

            return ['fixed' => true, 'note' => __('app.spec_note_unit_fixed', ['unit' => $unit])];
        }

        if ($status === 'needs_information' && ! empty($specs)) {
            // Take every suggestion the AI could stand behind; skip the rest.
            $answers = [];
            foreach ($specs as $spec) {
                $key   = trim((string) ($spec['key'] ?? ''));
                $value = trim((string) ($spec['suggested'] ?? ''));

                if ($key === '' || $value === '' || $this->isVagueValue($value)) {
                    continue;
                }

                $answers[$key] = $value;
            }

            // Nothing usable came back → the line stays unpriceable and the
            // caller drops it. We never fabricate specs to force it through.
            if (empty($answers)) {
                return ['fixed' => false, 'note' => null];
            }

            $suffix = $this->buildSpecSuffix($specs, $answers);
            if ($suffix === '') {
                return ['fixed' => false, 'note' => null];
            }

            $item->update([
                'description'  => trim($item->description) . ' — ' . $suffix,
                'spec_answers' => $answers,
            ]);

            return ['fixed' => true, 'note' => __('app.spec_note_auto_filled')];
        }

        return ['fixed' => false, 'note' => null];
    }

    /**
     * A placeholder value the AI returns when it genuinely does not know — must
     * never be written into an item as if it were a real spec.
     */
    private function isVagueValue(string $value): bool
    {
        $v = mb_strtolower(trim($value));

        foreach (['غير محدد', 'غير معروف', 'غير واضح', 'حسب المواصفات', 'not specified', 'unspecified', 'unknown', 'unclear', 'n/a', 'tbd', '-'] as $needle) {
            if ($v === mb_strtolower($needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Merge the user's spec answers into each item, mark them valid, then price.
     */
    public function submitSpecAnswers(): void
    {
        if (! $this->quotationId) {
            return;
        }

        foreach ($this->questionItems as $qi) {
            $id      = $qi['id'] ?? null;
            $answers = is_array($qi['answers'] ?? null) ? array_filter($qi['answers'], fn($v) => trim((string) $v) !== '') : [];
            if ($id === null) {
                continue;
            }

            $item = QuotationItem::find($id);
            if (! $item) {
                continue;
            }

            // Append the answered specs to the description so the pricing AI uses them.
            $suffix = $this->buildSpecSuffix($qi['missing_specs'] ?? [], $answers);
            $newDescription = $suffix !== ''
                ? trim($item->description) . ' — ' . $suffix
                : $item->description;

            $update = [
                'description'       => $newDescription,
                'spec_answers'      => $answers,
                'validation_status' => 'valid',
                'validated_at'      => now(),
            ];
            $item->update($update);

            if ($this->boqId !== null) {
                BoqItem::where('boq_id', $this->boqId)
                    ->where('description', (string) ($qi['description'] ?? ''))
                    ->update(['spec_answers' => $answers, 'validation_status' => 'valid', 'validated_at' => now()]);
            }
        }

        $this->questionItems = [];
        $this->runPricing();
    }

    /** Skip the questions and price with what we have (estimate). */
    public function skipSpecAnswers(): void
    {
        if (! $this->quotationId) {
            return;
        }
        $this->questionItems = [];
        $this->runPricing();
    }

    /** Turn answered questions into a human-readable spec string for the description. */
    private function buildSpecSuffix(array $missingSpecs, array $answers): string
    {
        $parts = [];
        foreach ($missingSpecs as $spec) {
            $key = $spec['key'] ?? null;
            if ($key !== null && isset($answers[$key]) && trim((string) $answers[$key]) !== '') {
                $label   = trim((string) ($spec['question'] ?? $key));
                $parts[] = $label . ': ' . trim((string) $answers[$key]);
            }
        }
        return implode(' | ', $parts);
    }

    /** Load items for display, run pricing synchronously, and advance to step 3. */
    private function runPricing(): void
    {
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

        // ── Run pricing synchronously so prices appear immediately ────────
        $this->pricesFetching = true;
        $this->currentStep = 3;

        try {
            FetchQuotationPricesJob::dispatchSync($this->quotationId, $this->guestMode ? null : Auth::id(), $this->quotationUuid);
        } catch (\Throwable $jobEx) {
            Log::error('CreateBoq: pricing job failed synchronously.', ['message' => $jobEx->getMessage()]);
        }

        // Load results immediately so step 3 renders with real data
        $this->pollPriceStatus();
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

        // ── Guest mode: store intent in session and show login overlay ──────
        if ($this->guestMode) {
            session([
                'pending_guest_boq_uuid'  => $this->draftBoqUuid,
                'pending_guest_boq_token' => $this->guestToken,
            ]);
            $this->showGuestLoginOverlay = true;
        }
    }

    /**
     * Called by the guest login CTA button.
     * Commits session intent and redirects to the login page.
     */
    public function redirectToLogin(): void
    {
        session([
            'pending_guest_boq_uuid'  => $this->draftBoqUuid,
            'pending_guest_boq_token' => $this->guestToken,
        ]);
        $this->redirect(route('enduser.login'));
    }

    /** Step 3 → 4: go to address & payment. */
    public function proceedToAddress(): void
    {
        if ($this->countUnpricedQuotationItems() > 0) {
            $this->dispatch('toast', message: __('app.unpriced_items_block_checkout'), type: 'error');
            return;
        }

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

        if ($this->countUnpricedQuotationItems() > 0) {
            $this->dispatch('toast', message: __('app.unpriced_items_block_checkout'), type: 'error');
            return;
        }

        try {
            $order = DB::transaction(function () {
                $quotation = QuotationRequest::findOrFail($this->quotationId);
                if ($this->countUnpricedQuotationItems() > 0) {
                    throw new \RuntimeException('Cannot place order with unpriced quotation items.');
                }

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
                $quotation->update(['status' => QuotationRequestStatusEnum::InReview]);

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

    private function countUnpricedQuotationItems(): int
    {
        if (! $this->quotationId) {
            return $this->unpricedCount;
        }

        return QuotationItem::where('quotation_request_id', $this->quotationId)
            ->where('is_selected', true)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereNull('unit_price')
                    ->orWhere('unit_price', '<=', 0);
            })
            ->count();
    }

    private function persistProject(): Project
    {
        if ($this->projectId !== null) {
            if ($this->guestMode) {
                $project = Project::where('is_guest', true)->findOrFail($this->projectId);
            } else {
                $project = Project::where('client_id', Auth::id())->findOrFail($this->projectId);
            }

            $project->update([
                'name'        => $this->projectName,
                'description' => $this->projectDescription,
            ]);

            return $project;
        }

        // ── Guest mode: recover existing project via its BOQ's guest_token ──
        if ($this->guestMode && $this->guestToken) {
            $existingBoq = Boq::where('guest_token', $this->guestToken)->first();
            if ($existingBoq && $existingBoq->project_id) {
                $project = Project::find($existingBoq->project_id);
                if ($project) {
                    $this->projectId = $project->id;
                    $project->update([
                        'name'        => $this->projectName,
                        'description' => $this->projectDescription,
                    ]);
                    return $project;
                }
            }
        }

        $project = Project::create([
            'client_id'   => $this->guestMode ? null : Auth::id(),
            'project_no'  => $this->generateProjectNo(),
            'name'        => $this->projectName,
            'description' => $this->projectDescription,
            'status'      => ProjectStatusEnum::Pending,
            'is_guest'    => $this->guestMode,
        ]);

        $this->projectId = $project->id;

        return $project;
    }

    private function persistBoq(Project $project, ?BoqStatusEnum $status = null): Boq
    {
        if ($this->boqId !== null) {
            if ($this->guestMode) {
                $boq = Boq::where('guest_token', $this->guestToken)->findOrFail($this->boqId);
            } else {
                $boq = Boq::where('client_id', Auth::id())->findOrFail($this->boqId);
            }

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

        // ── Guest mode: recover existing BOQ by guest_token on page refresh ─
        if ($this->guestMode && $this->guestToken) {
            $existing = Boq::where('guest_token', $this->guestToken)->first();
            if ($existing) {
                $this->boqId        = $existing->id;
                $this->draftBoqUuid = $existing->uuid;
                $attrs = ['project_id' => $project->id];
                if ($status !== null) { $attrs['status'] = $status; }
                $existing->update($attrs);
                return $existing;
            }
        }

        $boq = Boq::create([
            'project_id'  => $project->id,
            'client_id'   => $this->guestMode ? null : Auth::id(),
            'guest_token' => $this->guestMode ? $this->guestToken : null,
            'boq_no'      => $this->generateBoqNo(),
            'status'      => $status ?? BoqStatusEnum::Draft,
            'type'        => $this->boqType,
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

}
