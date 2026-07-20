<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationItemStatusEnum;
use App\Enums\QuotationProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Jobs\AuditQuotationItemsJob;
use App\Jobs\ExtractQuotationItemsJob;
use App\Jobs\FetchQuotationPricesJob;
use App\Models\BoqAnswerResult;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\BoqValidationService;
use App\Services\PriceAnalysisService;
use App\Services\Pricing\ProductSpecEngine;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
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

    /** price_status value for a row that is missing something essential to price. */
    private const NEEDS_REVIEW = 'needs_review';

    /** Hard cap on how many validation questions the user is asked to resolve. */
    private const MAX_QUESTIONS = 10;

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    public ?int $quotationId = null;

    public bool $isEditMode = false;

    #[Validate('required|string|max:255')]
    public string $projectName = '';

    #[Validate('required|string')]
    public string $projectStatus = QuotationProjectStatusEnum::Tender->value;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $boqFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $processing = false;

    /** Live progress text while a large BOQ is parsed slice by slice. */
    public string $extractionProgress = '';

    /** Rows written by the job so far — a counter only, never the rows. */
    public int $extractedSoFar = 0;

    /** How many parts the file was split into (1 = parsed in a single call). */
    public int $chunkTotal = 0;

    /** Which part the job is on. */
    public int $chunkCurrent = 0;

    /**
     * How many rows the table renders at once.
     *
     * A real BOQ can run to tens of thousands of lines. Rendering them all
     * produces a DOM (and a Livewire payload) large enough to lock the browser,
     * so the table shows a window and the user extends it on demand.
     */
    public int $visibleRows = self::ROWS_PER_PAGE;

    private const ROWS_PER_PAGE = 200;

    /** Reveal the next slice of rows. */
    public function showMoreRows(): void
    {
        $this->visibleRows += self::ROWS_PER_PAGE;
    }

    /** The rows the table should actually render this pass. */
    public function getVisibleItemsProperty(): array
    {
        return array_slice($this->items, 0, $this->visibleRows, true);
    }

    public string $boqFileName = '';

    public bool $showPricing = false;

    public bool $pricingLoading = false;

    /**
     * Analysis findings surfaced on the pricing-review step.
     * @var list<array{code:string, severity:string, message:string, rows:list<int>}>
     */
    public array $priceFindings = [];

    /**
     * Market unit-price range per item index: [index => ['min'=>, 'avg'=>, 'max'=>]].
     * @var array<int, array{min:float, avg:float, max:float}>
     */
    public array $priceRanges = [];

    /**
     * BOQ-validation questions the user must answer before pricing. The list stays
     * intact for the whole session so the user can navigate back and forth; answers
     * are collected separately and applied once, on finish.
     * @var list<array<string, mixed>>
     */
    public array $validationQuestions = [];

    /**
     * Collected answers, keyed by question index:
     *   [i => ['choice' => <option string>, 'custom' => <free-text or ''>]]
     * A question is "answered" when it has a choice, and — if that choice is the
     * free-text option — a non-empty custom value.
     * @var array<int, array{choice:string, custom:string}>
     */
    public array $validationAnswers = [];

    /** Index of the question currently shown in the modal. */
    public int $currentQuestion = 0;

    /** Whether the post-upload validation gate has run for the current items. */
    public bool $validationRan = false;

    /**
     * Hash of the answers the user gave, set once the gate is finished.
     *
     * Pricing keys its reuse cache on the file hash plus this, so the same file
     * answered the same way returns the same prices without another AI call.
     */
    public string $answersHash = '';

    /** The questions/answers as finalised, stored alongside the priced result. */
    public array $answeredQuestions = [];
    public array $givenAnswers = [];

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
            $this->projectStatus = QuotationProjectStatusEnum::Tender->value;

            // A fresh quotation must start clean. The keys are suffixed with
            // 'new' until the draft exists, so a previous upload can leave
            // progress behind that this page would otherwise read as its own.
            foreach ([
                'boq_ai_status', 'boq_ai_message', 'boq_ai_started_at',
                'boq_ai_partial_count', 'boq_ai_chunk_total', 'boq_ai_chunk_current',
                'boq_ai_chunks_done', 'boq_ai_chunks_failed', 'boq_ai_questions',
                'boq_ai_batch_id', 'boq_ai_stopped_by_user',
            ] as $key) {
                Cache::forget($this->cacheKeyFor($key));
            }

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

        $this->loadItemsFrom($quotation);
    }

    /**
     * Hydrate $items from a quotation's persisted rows.
     *
     * Shared by mount() and checkAiStatus() — after a queued extraction the rows
     * exist only in the database, so the component reloads them the same way an
     * edit-mode boot does.
     */
    private function loadItemsFrom(QuotationRequest $quotation): void
    {
        $this->items = $this->mapItems($quotation->items()->get());
    }

    /**
     * Shape persisted rows into the array the component and view work with.
     *
     * Separate from loadItemsFrom() so the progressive preview can map a limited
     * query result without loading the whole table.
     *
     * @param  \Illuminate\Support\Collection<int, QuotationItem>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function mapItems($rows): array
    {
        return $rows
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
                // 'needs_review' is deliberately not carried into this page.
                // Flagging rows red and blocking pricing on them was removed:
                // the engine's recommendations are applied automatically
                // instead, so a row that arrives flagged from an earlier
                // pricing run is treated as pending like any other.
                'price_status'         => ($item->price_status ?? 'pending') === self::NEEDS_REVIEW
                    ? 'pending'
                    : ($item->price_status ?? 'pending'),
                'is_selected'          => (bool) $item->is_selected,
            ])
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // BOQ Upload & AI extraction
    // -------------------------------------------------------------------------

    public function uploadBoq(): void
    {
        @set_time_limit(480);

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

        $allowedExtensions = ['pdf', 'xlsx', 'xlsm', 'xlsb', 'xls', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif', 'heic', 'heif'];
        $extension         = strtolower($this->boqFile->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            $this->addError('boqFile', 'The file must be of type: pdf, xlsx, xls, csv, or an image.');
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

            // Size check after storing (cannot safely read size from temp file).
            if ($fileSize > 50 * 1024 * 1024) {
                Storage::disk('local')->delete($storedPath);
                $this->addError('boqFile', 'The file must not be larger than 50 MB.');
                $this->processing = false;
                return;
            }

            // Hash the file now, so the parse, questions and priced-answer
            // caches can all key on the same content hash later without any of
            // them re-reading the file to compute it.
            $fileHash = @hash_file('sha256', Storage::disk('local')->path($storedPath)) ?: null;

            UploadedDocument::create([
                'quotation_request_id' => $quotation->id,
                'uploaded_by'          => Auth::id(),
                'file_name'            => $fileName,
                'file_path'            => $storedPath,
                'file_hash'            => $fileHash,
                'file_type'            => 'boq',
                'file_size'            => $fileSize,
            ]);

            $this->boqFileName = $fileName;
            $this->boqFile     = null;

            // ── Queue the AI extraction ───────────────────────────────────────
            // A real BOQ can carry tens of thousands of rows and takes minutes to
            // parse. Running that inline exceeds the request timeout, which kills
            // the Livewire request; the browser then retries as a plain POST and
            // the GET-only route answers 405. Dispatch instead, and let
            // checkAiStatus() poll for the result.
            Cache::put($this->cacheKeyFor('boq_ai_status'), 'pending', now()->addHours(12));
            Cache::put($this->cacheKeyFor('boq_ai_message'), '', now()->addHours(12));
            Cache::put($this->cacheKeyFor('boq_ai_started_at'), now()->timestamp, now()->addHours(12));
            Cache::forget($this->cacheKeyFor('boq_ai_partial_count'));
            Cache::forget($this->cacheKeyFor('boq_ai_chunk_total'));
            Cache::forget($this->cacheKeyFor('boq_ai_chunk_current'));
            $this->chunkTotal   = 0;
            $this->chunkCurrent = 0;

            ExtractQuotationItemsJob::dispatch(
                $quotation->id,
                $storedPath,
                $this->projectName,
                $this->projectStatus,
                // Must match cacheKeyFor()'s suffix exactly, or the job writes
                // progress the component never reads.
                Auth::id() . '_' . $quotation->id,
            );

            // Record this page as the job's origin, so if the user navigates away
            // the background "view data" popup brings them back here rather than
            // to the BOQ page.
            $this->dispatch('boq-job-started');
            $this->dispatch('toast', message: __('app.boq_extraction_queued'), type: 'info');

        } catch (\Throwable $e) {
            Log::error('CreateQuotation::uploadBoq failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->processing = false;
            $this->dispatch('toast', message: 'Upload failed. Please try again.', type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // AI status polling (called by wire:poll every 4 s while $processing)
    // -------------------------------------------------------------------------

    /**
     * How long a queued extraction may run before the UI calls it stuck.
     *
     * Must stay >= ExtractQuotationItemsJob::$timeout, otherwise the page reports
     * failure while the job is still legitimately parsing a very large BOQ.
     */
    private const EXTRACTION_TIMEOUT = 7200;

    /** Build a per-user cache key, shared with ExtractQuotationItemsJob. */
    private function cacheKeyFor(string $type): string
    {
        // Scoped to the quotation, not just the user. Keyed on the user alone,
        // a second quotation read the first one's counters and showed its
        // progress — "part 32 of 49" on a brand new upload.
        return $type . '_' . Auth::id() . '_' . ($this->quotationId ?? 'new');
    }

    /**
     * Stop the extraction and keep whatever has already been written.
     *
     * A large BOQ can run for many parts; the user may decide the rows already
     * extracted are enough. Cancelling the batch stops the parts that have not
     * started yet — the ones already done keep their rows, since each part
     * writes as it finishes rather than at the end.
     */
    public function stopExtraction(): void
    {
        if (! $this->processing) {
            return;
        }

        // Set before cancelling: the batch's finally() fires on cancellation and
        // would otherwise overwrite this status with a partial/failed verdict.
        Cache::put($this->cacheKeyFor('boq_ai_stopped_by_user'), true, now()->addHours(12));

        $batchId = Cache::get($this->cacheKeyFor('boq_ai_batch_id'));

        try {
            if ($batchId) {
                Bus::findBatch($batchId)?->cancel();
            }

            // Cancelling only flags the batch — the queued jobs still get
            // dequeued one by one, and each wakes a worker and hits the database
            // just to discover it should stop. On a 49-part file that is 49
            // pointless round trips. Drop the pending payloads so stopping
            // actually stops.
            //
            // Matched on this quotation's slice path, not the batch id. The batch
            // id lives in a cache key that can expire or be missing, and when it
            // was the only filter a stop silently deleted nothing — which is how
            // an unreserved part survived a stop.
            //
            // The path is matched rather than the serialized property name: the
            // payload is JSON, so a private property's quotes arrive escaped
            // ("quotationId\";i:123;") and the obvious pattern never matches.
            // "boq-chunks/<id>/" appears in the payload verbatim and is unique to
            // this quotation's parts.
            //
            // Only rows still waiting are touched: a job already reserved by a
            // worker is mid-flight, and its own cancelled() check handles that.
            //
            // Guarded by the driver: this reaches into Laravel's own jobs table,
            // which only exists on the database queue.
            if (config('queue.default') === 'database') {
                $table = config('queue.connections.database.table', 'jobs');

                $deleted = DB::table($table)
                    ->whereNull('reserved_at')
                    ->where('payload', 'like', '%boq-chunks%' . $this->quotationId . '%')
                    ->delete();

                // Belt and braces: if the payload is shaped differently than the
                // pattern above expects, fall back to the batch id so a stop is
                // never a complete no-op.
                if ($deleted === 0 && $batchId) {
                    $deleted = DB::table($table)
                        ->whereNull('reserved_at')
                        ->where('payload', 'like', '%' . $batchId . '%')
                        ->delete();
                }

                // Clear this quotation's failed parts too. They are dead weight:
                // retrying one would write rows into a run the user has already
                // closed, and they otherwise accumulate in failed_jobs for every
                // stopped extraction with no one to act on them.
                $failedTable   = config('queue.failed.table', 'failed_jobs');
                $deletedFailed = DB::table($failedTable)
                    ->where('payload', 'like', '%boq-chunks%' . $this->quotationId . '%')
                    ->delete();

                Log::info('CreateQuotation: dropped queued extraction parts.', [
                    'quotation_id'  => $this->quotationId,
                    'batch_id'      => $batchId,
                    'deleted'       => $deleted,
                    'deleted_failed' => $deletedFailed,
                ]);
            }
        } catch (\Throwable $e) {
            // Best-effort: the rows already written are the point, and the chunk
            // jobs check cancelled() themselves regardless.
            Log::warning('CreateQuotation: could not cancel extraction batch.', [
                'batch_id' => $batchId,
                'message'  => $e->getMessage(),
            ]);
        }

        // Every part that will never run leaves its slice on disk. The finaliser
        // clears this directory too, but it only fires once the batch drains —
        // and the jobs that would have drained it were just deleted.
        Storage::disk('local')->deleteDirectory('boq-chunks/' . $this->quotationId);

        $count = QuotationItem::where('quotation_request_id', $this->quotationId)->count();

        // The kept rows still go through pricing, so they still need auditing.
        // Queued rather than run here: the gate is a chunked AI pass and this is
        // a web request. It writes only the questions cache, never the status,
        // so it cannot clobber the "stopped" message set below.
        // Owner key must match cacheKeyFor()'s format exactly — it is
        // quotation-scoped, so passing the user id alone made the job write
        // questions to a key this page never reads.
        AuditQuotationItemsJob::dispatch(
            $this->quotationId,
            Auth::id() . '_' . $this->quotationId,
        );

        Cache::put($this->cacheKeyFor('boq_ai_status'), 'done', now()->addHours(12));
        Cache::put(
            $this->cacheKeyFor('boq_ai_message'),
            __('app.extraction_stopped', ['count' => $count]),
            now()->addHours(12),
        );

        // Pick the result up through the normal path, so the items load and the
        // validation gate runs over what was kept.
        $this->checkAiStatus();
    }

    /**
     * Write a terminal status and clear the keys that keep a run "in flight".
     *
     * Clearing started_at matters as much as the status: while it exists the
     * timeout guard keeps re-firing, and any code that treats a live timestamp
     * as "still running" sees a run that never ends.
     */
    private function stopPolling(string $status, string $message): void
    {
        Cache::put($this->cacheKeyFor('boq_ai_status'), $status, now()->addHours(12));
        Cache::put($this->cacheKeyFor('boq_ai_message'), $message, now()->addHours(12));
        Cache::forget($this->cacheKeyFor('boq_ai_started_at'));
        Cache::forget($this->cacheKeyFor('boq_ai_batch_id'));
    }

    public function checkAiStatus(): void
    {
        if (! $this->processing || $this->quotationId === null) {
            return;
        }

        $status  = Cache::get($this->cacheKeyFor('boq_ai_status'));
        $message = (string) Cache::get($this->cacheKeyFor('boq_ai_message'), '');

        // ── Termination guards ───────────────────────────────────────────────
        // Nothing below is cosmetic: without a terminal status the view polls
        // every 4s forever. Three separate ways a run can stop reporting:

        // 1. The status key is gone (expired, or flushed) while we still think
        //    the job is running. Nothing will ever set it again.
        if ($status === null) {
            $this->stopPolling('failed', __('app.boq_extraction_timeout'));
            return;
        }

        // 2. The batch finished, was cancelled, or no longer exists, but the
        //    finaliser never wrote a terminal status — a killed worker, or a
        //    finaliser that itself died. The rows that made it are still real.
        if (in_array($status, ['pending', 'running'], true)) {
            $batchId = Cache::get($this->cacheKeyFor('boq_ai_batch_id'));

            if ($batchId) {
                $batch = null;

                try {
                    $batch = Bus::findBatch($batchId);
                } catch (\Throwable $e) {
                    Log::warning('CreateQuotation: could not read extraction batch.', [
                        'batch_id' => $batchId,
                        'message'  => $e->getMessage(),
                    ]);
                }

                if ($batch === null || $batch->finished() || $batch->cancelled()) {
                    $count = QuotationItem::where('quotation_request_id', $this->quotationId)->count();

                    $this->stopPolling(
                        $count > 0 ? 'done' : 'failed',
                        $count > 0
                            ? __('app.extraction_stopped', ['count' => $count])
                            : __('app.boq_extraction_timeout'),
                    );

                    $status  = Cache::get($this->cacheKeyFor('boq_ai_status'));
                    $message = (string) Cache::get($this->cacheKeyFor('boq_ai_message'), '');
                }
            }
        }

        // 3. The run has outlived its window. Only trips when the job is truly
        //    stuck, or nothing is consuming the queue at all.
        $startedAt = Cache::get($this->cacheKeyFor('boq_ai_started_at'));
        if ($startedAt && (now()->timestamp - $startedAt) > self::EXTRACTION_TIMEOUT) {
            $this->stopPolling('failed', __('app.boq_extraction_timeout'));
            $status  = 'failed';
            $message = __('app.boq_extraction_timeout');
        }

        // Still working — keep polling. On a chunked parse the job reports which
        // slice it is on, so show that instead of an unchanging spinner.
        if ($status === 'pending' || $status === 'running') {
            $this->extractionProgress = $message;

            $this->extractedSoFar = (int) Cache::get($this->cacheKeyFor('boq_ai_partial_count'), 0);
            $this->chunkTotal = (int) Cache::get($this->cacheKeyFor('boq_ai_chunk_total'), 0);

            // Clamped here as well as at the writer: these counters are shared
            // between concurrent jobs, and "part 65 of 63" reads as a bug even
            // when the extraction itself is fine.
            $this->chunkCurrent = min(
                (int) Cache::get($this->cacheKeyFor('boq_ai_chunk_current'), 0),
                max($this->chunkTotal, 1),
            );

            // Show rows as the job writes them, but never load more than the
            // table actually renders.
            //
            // Loading every row on each poll is what stalled the page before:
            // $items is serialised into the component state, so a 20k-row BOQ
            // shipped megabytes to the browser every 4 seconds. Capping the
            // preview at $visibleRows keeps the payload flat no matter how large
            // the file is — the rest arrive in one go when the job finishes.
            if ($this->extractedSoFar > count($this->items) && count($this->items) < $this->visibleRows) {
                $quotation = QuotationRequest::find($this->quotationId);
                if ($quotation) {
                    $this->items = $this->mapItems(
                        $quotation->items()->with('unit')->limit($this->visibleRows)->get()
                    );
                }
            }

            return;
        }

        $this->extractionProgress = '';

        $this->processing = false;

        // 'partial' loads exactly like 'done' — the rows that were extracted are
        // real and usable — but the user is warned that some are missing.
        if ($status === 'done' || $status === 'partial') {
            $quotation = QuotationRequest::with(['items.unit'])->find($this->quotationId);
            if ($quotation) {
                $this->loadItemsFrom($quotation);
            }

            $this->dispatch('boq-upload-done', outcome: $status === 'partial' ? 'partial' : 'success');
            $this->dispatch(
                'toast',
                message: $message ?: (count($this->items) . ' items extracted successfully from the BOQ file.'),
                type: $status === 'partial' ? 'warning' : 'success',
            );

            // The validation gate ran inside the job (it is another chunked AI
            // pass, far too slow for a poll request). Just pick up its result.
            $this->validationRan       = true;
            $this->validationAnswers   = [];
            $this->currentQuestion     = 0;
            $this->validationQuestions = (array) Cache::get($this->cacheKeyFor('boq_ai_questions'), []);
        } elseif ($status === 'no_items') {
            $this->dispatch('boq-upload-done', outcome: 'no_items');
            $this->dispatch('toast', message: $message ?: 'No items found in the file. Please add items manually.', type: 'warning');
        } elseif ($status === 'failed') {
            // Even a failed run can have written rows — the parts are
            // independent, so one dying does not undo the others. Keep them and
            // report it as partial rather than discarding real work.
            $extracted = QuotationItem::where('quotation_request_id', $this->quotationId)->count();

            if ($extracted > 0) {
                $quotation = QuotationRequest::with(['items.unit'])->find($this->quotationId);
                if ($quotation) {
                    $this->loadItemsFrom($quotation);
                }

                $this->dispatch('boq-upload-done', outcome: 'partial');
                $this->dispatch(
                    'toast',
                    message: __('app.extraction_stopped', ['count' => $extracted]),
                    type: 'warning',
                );

                $this->validationRan       = true;
                $this->validationAnswers   = [];
                $this->currentQuestion     = 0;
                $this->validationQuestions = (array) Cache::get($this->cacheKeyFor('boq_ai_questions'), []);
            } else {
                $this->dispatch('boq-upload-done', outcome: 'failed');
                $this->dispatch('toast', message: $message ?: 'Extraction failed. Please try uploading the file again.', type: 'error');
            }
        } else {
            // No status in the cache. That is NOT proof of failure: the key has
            // a TTL, and a long run on a single worker can outlive it. The rows
            // are the truth — if the job wrote any, the extraction worked and
            // saying otherwise throws away real work.
            $extracted = QuotationItem::where('quotation_request_id', $this->quotationId)->count();

            if ($extracted > 0) {
                $quotation = QuotationRequest::with(['items.unit'])->find($this->quotationId);
                if ($quotation) {
                    $this->loadItemsFrom($quotation);
                }

                $this->dispatch('boq-upload-done', outcome: 'success');
                $this->dispatch('toast', message: $extracted . ' items extracted from the BOQ file.', type: 'success');

                $this->validationRan       = true;
                $this->validationAnswers   = [];
                $this->currentQuestion     = 0;
                $this->validationQuestions = (array) Cache::get($this->cacheKeyFor('boq_ai_questions'), []);
            } else {
                $this->dispatch('boq-upload-done', outcome: 'failed');
                $this->dispatch('toast', message: 'AI extraction status expired. Please try extracting again.', type: 'error');
            }
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

        // Pricing is gated behind BOQ validation: every outstanding question must be
        // answered first. This mirrors the blocking modal on the client side, and
        // also guards direct/programmatic calls.
        if (! empty($this->validationQuestions)) {
            $this->dispatch('toast', message: __('app.validation_answer_first'), type: 'warning');
            return;
        }

        // ── Queue the pricing run ────────────────────────────────────────────
        // Pricing makes chunked AI passes over every line. Inline, a large BOQ
        // blows the request timeout — the same failure mode that produced the
        // 405 on upload. Persist the rows, dispatch, and poll for the result.
        try {
            $quotation = QuotationRequest::where('client_id', Auth::id())->find($this->quotationId);

            if (! $quotation) {
                $this->dispatch('toast', message: 'Save the quotation before pricing.', type: 'error');
                return;
            }

            $this->persistItems($quotation);
            $quotation->update(['prices_fetched_at' => null]);

            // The BOQ file's content hash, so pricing can reuse a result
            // previously produced for this exact file and answer set.
            $fileHash = $quotation->uploadedDocuments()
                ->where('file_type', 'boq')
                ->latest()
                ->value('file_hash');

            // No questions were raised → an empty, but stable, answer set.
            if ($this->answersHash === '') {
                $this->answersHash = BoqAnswerResult::hashAnswers([]);
            }

            FetchQuotationPricesJob::dispatch(
                $quotation->id,
                Auth::id(),
                $quotation->uuid,
                $fileHash,
                $this->answersHash,
                $this->answeredQuestions,
                $this->givenAnswers,
            );

            $this->pricingLoading = true;
            $this->showPricing    = true;

            $this->dispatch('toast', message: __('app.boq_pricing_queued'), type: 'info');
        } catch (\Throwable $e) {
            Log::error('CreateQuotation::fetchPricing failed.', ['message' => $e->getMessage()]);
            $this->pricingLoading = false;
            $this->dispatch('toast', message: 'Pricing could not be started. Please try again.', type: 'error');
        }
    }

    /**
     * Poll for the queued pricing run (wire:poll while $pricingLoading).
     *
     * FetchQuotationPricesJob stamps prices_fetched_at in a finally block, so
     * this advances whether pricing succeeded, partially succeeded, or failed.
     */
    public function checkPricingStatus(): void
    {
        if (! $this->pricingLoading || $this->quotationId === null) {
            return;
        }

        $quotation = QuotationRequest::with(['items.unit'])->find($this->quotationId);

        if (! $quotation || $quotation->prices_fetched_at === null) {
            // Show which batch the job is on, so a large quotation does not sit
            // on an unchanging "fetching prices" message for minutes.
            $this->extractionProgress = (string) Cache::get('boq_pricing_message_' . Auth::id(), '');
            return;
        }

        $this->extractionProgress = '';
        $this->pricingLoading     = false;
        $this->loadItemsFrom($quotation);
        $this->runPriceAnalysis();

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
        $this->runPriceAnalysis();
    }

    public function rejectPriceItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }
        $this->items[$index]['price_status'] = 'rejected';
        $this->runPriceAnalysis();
    }

    /**
     * Run the pricing-stage analysis (price inconsistency + market range) and store
     * the results for the pricing-review step. Never throws: analysis is advisory,
     * so a failure must not break the pricing flow. Duplication and VAT are handled
     * earlier, at BOQ-upload time (see runBoqValidation()).
     */
    private function runPriceAnalysis(): void
    {
        try {
            $result = app(PriceAnalysisService::class)->analyze($this->items);

            $this->priceFindings = $result['findings'];
            $this->priceRanges   = $result['ranges'];
        } catch (\Throwable $e) {
            Log::error('CreateQuotation::runPriceAnalysis failed.', ['message' => $e->getMessage()]);
            $this->priceFindings = [];
            $this->priceRanges   = [];
        }
    }

    // -------------------------------------------------------------------------
    // BOQ validation gate (runs after upload, before pricing)
    // -------------------------------------------------------------------------

    /**
     * Audit the freshly-extracted BOQ items and queue any questions the user must
     * resolve before pricing. On AI failure the gate is treated as passed (empty
     * queue) so a DeepSeek outage never blocks the whole flow — a warning is shown
     * instead.
     */
    private function runBoqValidation(): void
    {
        $this->validationRan       = true;
        $this->validationQuestions = [];
        $this->validationAnswers   = [];
        $this->currentQuestion     = 0;

        try {
            $result = app(BoqValidationService::class)->validate($this->items);
            // Cap the interactive gate at 10 questions so the user is never asked to
            // resolve an endless queue. Anything beyond the cap is left for the
            // second-pass NEEDS_REVIEW flagging (which the user can auto-remove).
            $this->validationQuestions = array_slice($result['questions'], 0, self::MAX_QUESTIONS);

            if ($result['failed']) {
                $this->dispatch('toast', message: __('app.validation_ai_unavailable'), type: 'warning');
            }
        } catch (\Throwable $e) {
            Log::error('CreateQuotation::runBoqValidation failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: __('app.validation_ai_unavailable'), type: 'warning');
        }
    }

    /**
     * Record the user's choice for a question WITHOUT applying it yet.
     *
     * Nothing touches $this->items here: answers are collected and only applied on
     * finishValidation(), so the user can freely navigate back and change any answer
     * before committing. The optional $custom carries free text for the "other" option.
     */
    public function answerValidation(int $questionIndex, string $choice, string $custom = ''): void
    {
        if (! array_key_exists($questionIndex, $this->validationQuestions)) {
            return;
        }

        $question = $this->validationQuestions[$questionIndex];
        $options  = is_array($question['options'] ?? null) ? $question['options'] : [];

        // Reject answers that are not one of the offered options.
        if (! in_array($choice, $options, true)) {
            return;
        }

        $custom = trim($custom);

        $this->validationAnswers[$questionIndex] = [
            'choice' => $choice,
            'custom' => $custom,
        ];

        // Auto-advance to the next question once a usable answer is given, so the user
        // does not have to press "Next" after every pick. We DON'T advance when the
        // chosen option is the free-text "other" but nothing has been typed yet — the
        // user still needs to fill the input first.
        $isCustomOpt = $choice === ($question['custom_option'] ?? null);
        if ($isCustomOpt && $custom === '') {
            return;
        }

        if ($questionIndex === $this->currentQuestion
            && $this->currentQuestion < count($this->validationQuestions) - 1) {
            $this->currentQuestion++;
        }
    }

    /** Navigate directly to a question (tab click). */
    public function goToQuestion(int $index): void
    {
        if (array_key_exists($index, $this->validationQuestions)) {
            $this->currentQuestion = $index;
        }
    }

    public function nextQuestion(): void
    {
        if ($this->currentQuestion < count($this->validationQuestions) - 1) {
            $this->currentQuestion++;
        }
    }

    public function prevQuestion(): void
    {
        if ($this->currentQuestion > 0) {
            $this->currentQuestion--;
        }
    }

    /**
     * True when every question has a usable answer: a chosen option, and — when that
     * option is the free-text "other" — a non-empty custom value.
     */
    /** Number of items currently flagged NEEDS_REVIEW. */
    public function getNeedsReviewCountProperty(): int
    {
        return collect($this->items)
            ->filter(fn($i) => ($i['price_status'] ?? '') === self::NEEDS_REVIEW)
            ->count();
    }

    /**
     * The quotation is blocked (no pricing / no submit) while any item still needs
     * review. This is the enforcement point for quotation_blocked = true.
     */
    public function getQuotationBlockedProperty(): bool
    {
        return $this->needsReviewCount > 0;
    }

    /**
     * Remove every row currently flagged NEEDS_REVIEW ("مواصفات إلزامية ناقصة") in
     * one pass. Backs the "remove all incomplete rows" button, and is also used to
     * auto-clear them. Rejected rows are never touched. Persists when a draft exists.
     */
    public function removeNeedsReviewRows(): void
    {
        $rows = [];
        foreach ($this->items as $i => $item) {
            if (($item['price_status'] ?? '') === self::NEEDS_REVIEW) {
                $rows[] = $i;
            }
        }

        if (empty($rows)) {
            return;
        }

        $count = count($rows);
        $this->removeRows($rows);

        if ($this->quotationId !== null) {
            try {
                $quotation = QuotationRequest::where('client_id', Auth::id())->find($this->quotationId);
                if ($quotation) {
                    $this->persistItems($quotation);
                }
            } catch (\Throwable $e) {
                Log::warning('CreateQuotation::removeNeedsReviewRows persist failed.', ['message' => $e->getMessage()]);
            }
        }

        $this->dispatch('toast', message: __('app.review_rows_removed', ['count' => $count]), type: 'success');
    }

    public function getAllValidationAnsweredProperty(): bool
    {
        foreach ($this->validationQuestions as $i => $question) {
            $answer = $this->validationAnswers[$i] ?? null;
            if ($answer === null || ($answer['choice'] ?? '') === '') {
                return false;
            }
            if (($answer['choice'] ?? '') === ($question['custom_option'] ?? null)
                && ($answer['custom'] ?? '') === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Commit all collected answers to the items, then close the gate. Refuses to run
     * until every question is answered. Field edits are applied in place; removals are
     * batched and executed once at the end (see removeRows) so real deletion never
     * corrupts the indices of rows still being processed.
     */
    public function finishValidation(): void
    {
        if (empty($this->validationQuestions)) {
            return;
        }

        if (! $this->allValidationAnswered) {
            $this->dispatch('toast', message: __('app.validation_answer_first'), type: 'warning');
            return;
        }

        // Rows the user asked to remove. Collected here and deleted in ONE pass at the
        // end, in descending index order, so real deletion never shifts the indices of
        // rows still to be processed.
        $rowsToRemove = [];

        foreach ($this->validationQuestions as $i => $question) {
            $answer = $this->validationAnswers[$i] ?? null;
            if ($answer === null) {
                continue;
            }

            $row = (int) ($question['row'] ?? -1);
            if (! array_key_exists($row, $this->items)) {
                continue;
            }

            $remove = $this->applyValidationAnswer($row, (string) ($question['gate'] ?? ''), $answer, $question);
            foreach ($remove as $r) {
                $rowsToRemove[$r] = true;
            }
        }

        // Actually remove the flagged rows (duplicates / out-of-scope / unwanted).
        $this->removeRows(array_keys($rowsToRemove));

        // Fingerprint the answers before clearing them, so pricing can look up a
        // previously priced result for this exact file + answer combination.
        $this->answersHash = BoqAnswerResult::hashAnswers($this->validationAnswers);
        $this->answeredQuestions = $this->validationQuestions;
        $this->givenAnswers      = $this->validationAnswers;

        // Clear the gate.
        $this->validationQuestions = [];
        $this->validationAnswers   = [];
        $this->currentQuestion     = 0;

        // The engine already qualified every line before these questions were
        // raised, and the answers have now been folded into the descriptions.
        // Re-running it here would re-derive the same blocking gaps and ask the
        // user again, so the gate stays closed once answered.

        // Persist the corrections if we already have a draft.
        if ($this->quotationId !== null) {
            try {
                $quotation = QuotationRequest::where('client_id', Auth::id())->find($this->quotationId);
                if ($quotation) {
                    $this->persistItems($quotation);
                }
            } catch (\Throwable $e) {
                Log::warning('CreateQuotation::finishValidation persist failed.', ['message' => $e->getMessage()]);
            }
        }

        $this->dispatch('toast', message: __('app.validation_done'), type: 'success');
    }

    /**
     * Qualify every line through the Product Specification & Pricing Engine.
     *
     * The engine classifies each line (supply vs service), normalises its unit,
     * applies safe industry defaults as labelled assumptions, and runs the
     * project-level cross-item checks. Policy here:
     *
     *   - BLOCKING specs missing        → one grouped question for the user.
     *   - Non-blocking gaps             → assumed, labelled, and priced.
     *   - Service / installation lines  → pulled out of the supply quotation.
     *   - Quantity / unit / compatibility conflicts → surfaced, never silently fixed.
     *
     * Rows the user explicitly rejected are left untouched.
     */
    private function flagNeedsReview(): void
    {
        $candidates = [];
        foreach ($this->items as $i => $item) {
            if (($item['price_status'] ?? '') === 'rejected'
                || ($item['status'] ?? '') === QuotationItemStatusEnum::Rejected->value) {
                continue;
            }
            $candidates[$i] = [
                'description' => (string) ($item['description'] ?? ''),
                'unit'        => (string) ($item['unit'] ?? ''),
                'quantity'    => (float) ($item['quantity'] ?? 0),
                'category'    => (string) ($item['category'] ?? ''),
                'brand'       => (string) ($item['brand'] ?? ''),
            ];
        }

        if ($candidates === []) {
            return;
        }

        try {
            $qualified = app(ProductSpecEngine::class)->qualify(
                array_values($candidates),
                ['name' => $this->projectName, 'type' => $this->projectStatus],
            );
        } catch (\Throwable $e) {
            // Never block the quotation on an engine outage — leave rows as-is.
            Log::error('CreateQuotation: spec engine failed, skipping qualification.', ['message' => $e->getMessage()]);
            return;
        }

        $indices     = array_keys($candidates);
        $nonSupply   = [];
        $assumed     = 0;
        $questions   = [];

        foreach ($qualified as $pos => $q) {
            $i = $indices[$pos] ?? null;
            if ($i === null || ! array_key_exists($i, $this->items)) {
                continue;
            }

            // Services and installation never belong in a supply quotation.
            if (! ($q['supplyable'] ?? true)) {
                $nonSupply[] = $i;
                continue;
            }

            $this->applyQualification($i, $q);

            if (($q['missing_blocking_specifications'] ?? []) !== []) {
                // A blocking-spec question is free text — the answer is a
                // specification, not a choice from a list. The custom option is
                // the only offered option so answerValidation() accepts it and
                // routes the typed value into the item description.
                $customOption = __('app.validation_specify_option');

                $questions[] = [
                    'row'           => $i,
                    'gate'          => 'specs',
                    'question'      => (string) ($q['grouped_question'] ?? ''),
                    'options'       => [$customOption],
                    'custom_option' => $customOption,
                    'custom_field'  => 'spec',
                ];
            } elseif (($q['assumptions'] ?? []) !== []) {
                $assumed++;
            }
        }

        if ($nonSupply !== []) {
            $this->removeRows($nonSupply);
            $this->dispatch('toast', message: __('app.spec_non_supply_removed', ['count' => count($nonSupply)]), type: 'warning');
        }

        if ($assumed > 0) {
            $this->dispatch('toast', message: __('app.spec_auto_resolved', ['count' => $assumed]), type: 'info');
        }

        // Only genuinely blocking gaps reach the user, capped and grouped.
        if ($questions !== []) {
            $this->validationQuestions = array_slice($questions, 0, self::MAX_QUESTIONS);
            $this->validationAnswers   = [];
            $this->currentQuestion     = 0;
        }
    }

    /**
     * Write one engine verdict onto the in-memory row.
     *
     * The normalised unit and clean final description replace the raw values so
     * the quotation carries specifications only — never questions or placeholders.
     *
     * @param  array<string, mixed>  $q
     */
    private function applyQualification(int $i, array $q): void
    {
        if (! empty($q['normalized_unit'])) {
            $this->setCorrectedUnit($i, (string) $q['normalized_unit']);
        }

        $finalDescription = trim((string) ($q['recommended_final_description'] ?? ''));
        if ($finalDescription !== '') {
            $this->items[$i]['description'] = $finalDescription;
        }

        $this->items[$i]['classification']         = $q['classification']    ?? null;
        $this->items[$i]['pricing_status']         = $q['pricing_status']    ?? null;
        $this->items[$i]['assumptions']            = $q['assumptions']       ?? [];
        $this->items[$i]['confidence_score']       = $q['confidence_score']  ?? 0;
        $this->items[$i]['quantity_warnings']      = $q['quantity_warnings'] ?? [];
        $this->items[$i]['unit_warnings']          = $q['unit_warnings']     ?? [];
        $this->items[$i]['compatibility_warnings'] = $q['compatibility_warnings'] ?? [];

        // The legacy red-row flag is retired: the engine's pricing_status is the
        // single source of truth for whether a line can be priced.
        $this->items[$i]['price_status'] = 'pending';
        unset($this->items[$i]['needs_review_reason']);
    }

    /**
     * Canonicalize a unit string so equivalent spellings compare equal.
     * Mirrors PricingService::normalizeUnit for the tokens we care about here.
     */
    private function normalizeUnitToken(string $unit): string
    {
        $u = trim(mb_strtolower($this->extractUnitToken($unit)));
        if ($u === '') {
            return '';
        }
        $u = strtr($u, ['²' => '2', '³' => '3', '^' => '', '.' => '']);
        $u = preg_replace('/\s+/u', '', $u);

        static $map = [
            'متر مكعب' => 'م3', 'مترمكعب' => 'م3', 'cubicmeter' => 'م3', 'cbm' => 'م3', 'm3' => 'م3',
            'متر مربع' => 'م2', 'مترمربع' => 'م2', 'squaremeter' => 'م2', 'sqm' => 'م2', 'm2' => 'م2',
            'ton' => 'طن', 'tonne' => 'طن', 'طن' => 'طن',
            'kg' => 'كجم', 'كيلو' => 'كجم', 'كيلوجرام' => 'كجم', 'كجم' => 'كجم',
            'no' => 'عدد', 'nos' => 'عدد', 'pcs' => 'عدد', 'pc' => 'عدد', 'piece' => 'عدد', 'unit' => 'عدد', 'عدد' => 'عدد',
            'set' => 'set', 'طقم' => 'طقم',
        ];

        return $map[$u] ?? $u;
    }

    /**
     * Apply a corrected unit while preserving the original (constraints 40-42).
     *
     * The original unit is snapshotted once into 'original_unit' the first time a
     * correction is made, so the change is never silent and stays auditable. A no-op
     * correction (same unit) is ignored.
     */
    private function setCorrectedUnit(int $row, string $newUnit): void
    {
        $current = trim((string) ($this->items[$row]['unit'] ?? ''));
        $newUnit = trim($newUnit);

        if ($newUnit === '' || $newUnit === $current) {
            return;
        }

        if (! array_key_exists('original_unit', $this->items[$row])) {
            $this->items[$row]['original_unit'] = $current;
        }

        $this->items[$row]['unit'] = $newUnit;
    }

    /**
     * Normalized signature for duplicate detection (constraint 44).
     *
     * Considers description + category + quantity + normalized unit, so two rows only
     * collide when they truly describe the same supply in the same amount — not merely
     * a shared description. Unit is normalized so "م3" and "m³" match.
     */
    private function itemSignature(array $item): string
    {
        $desc = mb_strtolower(trim((string) ($item['description'] ?? '')));
        $desc = preg_replace('/\s+/u', ' ', $desc);
        if ($desc === '') {
            return '';
        }

        $category = mb_strtolower(trim((string) ($item['category'] ?? '')));
        $unit     = $this->normalizeUnitToken((string) ($item['unit'] ?? ''));
        $qty      = (float) ($item['quantity'] ?? 0);

        return implode('|', [$desc, $category, $unit, $qty]);
    }

    /**
     * Skip manual answering: auto-pick the system-recommended option for every
     * question, then commit. Questions without a recommendation fall back to the
     * first concrete (non-"other") option so nothing is left requiring free text.
     */
    public function skipWithRecommendations(): void
    {
        if (empty($this->validationQuestions)) {
            return;
        }

        foreach ($this->validationQuestions as $i => $question) {
            $options    = is_array($question['options'] ?? null) ? $question['options'] : [];
            $customOpt  = $question['custom_option'] ?? null;
            $suggested  = $question['suggested'] ?? null;

            // Prefer the recommendation, but never auto-pick the free-text option
            // (it would need typing). Otherwise take the first concrete option.
            $pick = null;
            if ($suggested !== null && $suggested !== $customOpt && in_array($suggested, $options, true)) {
                $pick = $suggested;
            } else {
                foreach ($options as $opt) {
                    if ($opt !== $customOpt) {
                        $pick = $opt;
                        break;
                    }
                }
            }

            if ($pick !== null) {
                $this->validationAnswers[$i] = ['choice' => $pick, 'custom' => ''];
            }
        }

        $this->finishValidation();
    }

    /**
     * Mutate a single item based on a resolved validation question and its answer.
     *
     * When the chosen option is the free-text "other", the custom value is written to
     * the gate's target field (unit/description/brand). Otherwise the chosen option
     * itself is applied.
     *
     * Field edits happen in place immediately. Removals are NOT done here — the rows
     * to remove are returned to the caller, which deletes them all at once at the end
     * so real deletion never shifts indices of rows still being processed.
     *
     * @param  array{choice:string, custom:string}  $answer
     * @return list<int>  Row indices the user asked to remove (empty when none).
     */
    private function applyValidationAnswer(int $row, string $gate, array $answer, array $question): array
    {
        $choice   = $answer['choice'] ?? '';
        $custom   = trim($answer['custom'] ?? '');
        $isCustom = $choice === ($question['custom_option'] ?? null);
        $isRemove = $choice === __('app.validation_dup_remove')
            || $choice === __('app.validation_remove_item');

        // Resolve the value the user settled on: their free text when they chose
        // "other", otherwise the chosen option string itself.
        $value = $isCustom ? $custom : $choice;

        // For gates that target an item field, write the resolved value straight onto
        // the BOQ item so the product row itself changes (brand/unit/description/...).
        // This is the core behaviour: answers EDIT the items, not the quotation shell.
        // A vague placeholder ("not specified", "unclear") is treated as "leave as-is"
        // so it is never written into a field.
        $field = $question['custom_field'] ?? null;
        if ($value !== '' && ! $isRemove && ! $this->isVagueValue($value) && $field !== null) {
            if ($field === 'unit') {
                $this->setCorrectedUnit($row, $this->extractUnitToken($value));
            } elseif ($field === 'brand') {
                // A specs answer names the brand/grade; keep it in brand and also fold
                // it into the description so pricing sees the fuller spec.
                $this->items[$row]['brand'] = $value;
                $this->items[$row]['description'] = $this->appendSpec(
                    (string) ($this->items[$row]['description'] ?? ''),
                    $value
                );
            } elseif ($field === 'description') {
                // generic/scope: the answer replaces the vague description outright.
                $this->items[$row]['description'] = $value;
            } elseif ($field === 'spec') {
                // Engine blocking-spec answer: the description is already a clean
                // qualified spec, so the answer is appended rather than replacing it.
                $this->items[$row]['description'] = $this->appendSpec(
                    (string) ($this->items[$row]['description'] ?? ''),
                    $value
                );
            }
            return [];
        }

        switch ($gate) {
            case 'unit':
                // Options are concrete unit strings; take the leading token as the unit.
                $this->setCorrectedUnit($row, $this->extractUnitToken($choice));
                break;

            case 'quantity':
                if ($this->firstNumber($choice) !== '') {
                    $this->items[$row]['quantity'] = (float) $this->firstNumber($choice);
                }
                break;

            case 'duplicate':
                if ($isRemove) {
                    // Remove the duplicate copies (keep the primary row).
                    $dupRows = is_array($question['dup_rows'] ?? null) ? $question['dup_rows'] : [$row];
                    return array_map('intval', $dupRows);
                }
                break;

            case 'specs':
            case 'generic':
            case 'scope':
                if ($isRemove) {
                    return [$row];
                }
                // Otherwise the user confirmed the row is fine as-is; nothing to change.
                break;
        }

        return [];
    }

    /**
     * Permanently delete the given item rows from the table and the database.
     *
     * Deletes in DESCENDING index order and re-splices once so earlier indices stay
     * valid throughout. Safe to call after every answer has been resolved, because at
     * that point no code still references rows by their original index.
     *
     * @param  list<int>  $rows
     */
    private function removeRows(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $rows = array_values(array_unique(array_map('intval', $rows)));
        rsort($rows);

        foreach ($rows as $row) {
            if (! array_key_exists($row, $this->items)) {
                continue;
            }

            if (! empty($this->items[$row]['id'])) {
                QuotationItem::where('id', $this->items[$row]['id'])->delete();
            }

            array_splice($this->items, $row, 1);
        }
    }

    /**
     * Turn resolved spec answers into a readable suffix for the description,
     * e.g. "المقاس: 110mm | الخامة: PVC".
     *
     * @param  array<int, array<string, mixed>>  $missingSpecs
     * @param  array<string, string>             $answers
     */
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

    /**
     * Pull a usable unit token out of an option like "TON (طن)" → "TON".
     */
    private function extractUnitToken(string $option): string
    {
        $token = trim(preg_replace('/\(.*$/u', '', $option));
        return $token !== '' ? $token : trim($option);
    }

    /**
     * First numeric run in a string, or '' when there is none.
     */
    private function firstNumber(string $s): string
    {
        return preg_match('/[\d.]+/', $s, $m) ? $m[0] : '';
    }

    /**
     * Fold a spec (brand/grade) into a description without duplicating it.
     * "Reinforcement steel" + "SABIC B500" → "Reinforcement steel — SABIC B500".
     */
    private function appendSpec(string $description, string $spec): string
    {
        $description = trim($description);
        $spec        = trim($spec);

        if ($spec === '') {
            return $description;
        }
        if ($description === '') {
            return $spec;
        }
        // Already present (case-insensitive) → leave as-is.
        if (mb_stripos($description, $spec) !== false) {
            return $description;
        }

        return $description . ' — ' . $spec;
    }

    /**
     * A "leave as-is" placeholder answer that must never be written into a field —
     * e.g. the user picked "not specified"/"unclear" rather than a concrete value.
     */
    private function isVagueValue(string $value): bool
    {
        $v = mb_strtolower(trim($value));

        foreach (['غير محدد', 'غير معروف', 'غير واضح', 'not specified', 'unspecified', 'unknown', 'unclear', 'n/a'] as $needle) {
            if ($v === mb_strtolower($needle)) {
                return true;
            }
        }

        return false;
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
        $this->items[$index]['is_selected'] = true;

        if (! empty($this->items[$index]['id'])) {
            QuotationItem::where('id', $this->items[$index]['id'])
                ->update([
                    'status' => QuotationItemStatusEnum::Sourcing,
                    'is_selected' => true,
                ]);
        }
    }

    public function rejectItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        $this->items[$index]['status'] = QuotationItemStatusEnum::Rejected->value;
        $this->items[$index]['is_selected'] = false;

        if (! empty($this->items[$index]['id'])) {
            QuotationItem::where('id', $this->items[$index]['id'])
                ->update([
                    'status' => QuotationItemStatusEnum::Rejected,
                    'is_selected' => false,
                ]);
        }
    }

    public function approveAllItems(): void
    {
        $ids = [];

        foreach ($this->items as $index => $item) {
            $this->items[$index]['status'] = QuotationItemStatusEnum::Sourcing->value;
            $this->items[$index]['is_selected'] = true;

            if (! empty($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }

        if (! empty($ids)) {
            QuotationItem::whereIn('id', $ids)->update([
                'status' => QuotationItemStatusEnum::Sourcing,
                'is_selected' => true,
            ]);
        }

        $this->dispatch('toast', message: __('app.all_items_approved'), type: 'success');
    }

    public function rejectAllItems(): void
    {
        $ids = [];

        foreach ($this->items as $index => $item) {
            $this->items[$index]['status'] = QuotationItemStatusEnum::Rejected->value;
            $this->items[$index]['is_selected'] = false;

            if (! empty($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }

        if (! empty($ids)) {
            QuotationItem::whereIn('id', $ids)->update([
                'status' => QuotationItemStatusEnum::Rejected,
                'is_selected' => false,
            ]);
        }

        $this->dispatch('toast', message: __('app.all_items_rejected'), type: 'warning');
    }

    public function selectAllPricingItems(): void
    {
        foreach ($this->items as $index => $item) {
            if (($item['status'] ?? '') !== QuotationItemStatusEnum::Rejected->value) {
                $this->items[$index]['is_selected'] = true;
            }
        }
    }

    public function clearPricingSelection(): void
    {
        foreach ($this->items as $index => $_) {
            $this->items[$index]['is_selected'] = false;
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


        $selectedItems = collect($this->items)
            ->filter(fn($i) => ($i['status'] ?? '') !== QuotationItemStatusEnum::Rejected->value && ! empty($i['is_selected']));

        if ($selectedItems->isEmpty()) {
            $this->dispatch('toast', message: __('app.select_item_pricing_required'), type: 'error');

            return;
        }

        $quotation = $this->persistQuotation(QuotationRequestStatusEnum::Tender);
        $this->persistItems($quotation);
        $this->quotationId = $quotation->id;

        app(NotificationService::class)->sendToUserAndAdmins(
            title: 'Quotation Submitted',
            body: 'Quotation #' . $quotation->id . ' for "' . $this->projectName . '" has been submitted.',
            type: NotificationTypeEnum::QuotationSubmitted,
            userId: Auth::id(),
            actionUrl: route('enduser.quotations.show', $quotation->uuid),
        );

        session()->flash('quotation_initial_step', 3);
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
            $this->projectStatus = QuotationProjectStatusEnum::Tender->value;
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
                'is_selected'          => (bool) ($row['is_selected'] ?? false),
            ];

            // Engine verdicts, persisted so the quotation carries its qualification
            // trail (assumptions, warnings, readiness) rather than only a price.
            foreach ([
                'classification', 'pricing_status', 'assumptions', 'confidence_score',
                'quantity_warnings', 'unit_warnings', 'compatibility_warnings',
            ] as $field) {
                if (array_key_exists($field, $row)) {
                    $data[$field] = $row[$field];
                }
            }

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
