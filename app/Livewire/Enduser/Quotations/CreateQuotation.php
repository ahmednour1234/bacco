<?php

namespace App\Livewire\Enduser\Quotations;

use App\Enums\QuotationItemStatusEnum;
use App\Enums\QuotationProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\BoqValidationService;
use App\Services\PriceAnalysisService;
use App\Services\PricingService;
use App\Services\QuotationAiService;
use App\Services\NotificationService;
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
    public string $projectStatus = QuotationProjectStatusEnum::Tender->value;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $boqFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $processing = false;

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
                $rejectedCount = count($result['rejected'] ?? []);
                $msg = $rejectedCount > 0
                    ? "AI extracted {$rejectedCount} rows but all were rejected as non-supply items (labor, headings, etc.). Please verify the file contains actual supply products with quantities."
                    : 'The AI service could not find any BOQ items in this file. Please check the file has supply products with quantities and units.';
                $this->dispatch('toast', message: $msg, type: 'warning');
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
                        'is_selected'          => false,
                    ], $aiItem);
                }

                $quotation->update(['source_type' => QuotationSourceTypeEnum::Api]);
                $this->persistItems($quotation);

                $this->dispatch('toast', message: count($result['items']) . ' items extracted successfully from the BOQ file.', type: 'success');

                // Gate: audit the freshly-extracted BOQ before pricing is allowed.
                $this->runBoqValidation();
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

        // Pricing is gated behind BOQ validation: every outstanding question must be
        // answered first. This mirrors the blocking modal on the client side, and
        // also guards direct/programmatic calls.
        if (! empty($this->validationQuestions)) {
            $this->dispatch('toast', message: __('app.validation_answer_first'), type: 'warning');
            return;
        }

        $this->pricingLoading = true;

        try {
            $this->items      = app(PricingService::class)->fetchPrices($this->items);
            $this->showPricing = true;

            $this->runPriceAnalysis();
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
            $this->validationQuestions = $result['questions'];

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
     * until every question is answered. Applies row-index-affecting changes safely by
     * never splicing the items array (see softRejectRow).
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

        foreach ($this->validationQuestions as $i => $question) {
            $answer = $this->validationAnswers[$i] ?? null;
            if ($answer === null) {
                continue;
            }

            $row = (int) ($question['row'] ?? -1);
            if (! array_key_exists($row, $this->items)) {
                continue;
            }

            $this->applyValidationAnswer($row, (string) ($question['gate'] ?? ''), $answer, $question);
        }

        // Clear the gate.
        $this->validationQuestions = [];
        $this->validationAnswers   = [];
        $this->currentQuestion     = 0;

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
     * itself is applied. "Remove" choices soft-reject the row(s).
     *
     * @param  array{choice:string, custom:string}  $answer
     */
    private function applyValidationAnswer(int $row, string $gate, array $answer, array $question): void
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
        $field = $question['custom_field'] ?? null;
        if ($value !== '' && ! $isRemove && $field !== null) {
            if ($field === 'unit') {
                $this->items[$row]['unit'] = $this->extractUnitToken($value);
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
            }
            return;
        }

        switch ($gate) {
            case 'unit':
                // Options are concrete unit strings; take the leading token as the unit.
                $this->items[$row]['unit'] = $this->extractUnitToken($choice);
                break;

            case 'quantity':
                if ($this->firstNumber($choice) !== '') {
                    $this->items[$row]['quantity'] = (float) $this->firstNumber($choice);
                }
                break;

            case 'duplicate':
                if ($isRemove) {
                    $dupRows = is_array($question['dup_rows'] ?? null) ? $question['dup_rows'] : [$row];
                    foreach ($dupRows as $r) {
                        $this->softRejectRow((int) $r);
                    }
                }
                break;

            case 'specs':
            case 'generic':
            case 'scope':
                if ($isRemove) {
                    $this->softRejectRow($row);
                }
                // Otherwise the user confirmed the row is fine as-is; nothing to change.
                break;
        }
    }

    /**
     * Mark a row as rejected WITHOUT re-indexing the items array.
     *
     * Validation answers must never splice $this->items: the remaining queued
     * questions still reference rows by their original index, so removing an element
     * would silently point every later question at the wrong row. Rejecting keeps the
     * index stable while excluding the row from pricing, totals and submission.
     */
    private function softRejectRow(int $row): void
    {
        if (! array_key_exists($row, $this->items)) {
            return;
        }

        $this->items[$row]['status']       = QuotationItemStatusEnum::Rejected->value;
        $this->items[$row]['price_status'] = 'rejected';
        $this->items[$row]['is_selected']  = false;

        if (! empty($this->items[$row]['id'])) {
            QuotationItem::where('id', $this->items[$row]['id'])->update([
                'status'      => QuotationItemStatusEnum::Rejected,
                'is_selected' => false,
            ]);
        }
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
