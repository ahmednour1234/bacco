<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\QuotationItemStatusEnum;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\QuotationAiService;
use Illuminate\Support\Facades\Auth;
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

    public ?int $boqId = null;
    public ?int $projectId = null;

    public bool $isEditMode = false;

    #[Validate('required|string|max:255')]
    public string $projectName = '';

    #[Validate('required|string|max:5000')]
    public string $projectDescription = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $boqFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $processing = false;

    public string $boqFileName = '';

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount(?string $projectUuid = null): void
    {
        if ($projectUuid !== null) {
            $project = Project::where('uuid', $projectUuid)
                ->where('client_id', Auth::id())
                ->firstOrFail();

            $this->projectId          = $project->id;
            $this->projectName        = (string) $project->name;
            $this->projectDescription = (string) ($project->description ?? '');
            $this->isEditMode         = true;
        }
    }

    // -------------------------------------------------------------------------
    // BOQ Upload & AI extraction
    // -------------------------------------------------------------------------

    public function uploadBoq(): void
    {
        $this->validate([
            'projectName'        => 'required|string|max:255',
            'projectDescription' => 'required|string|max:5000',
        ]);

        if (! $this->boqFile) {
            $this->addError('boqFile', 'Please select a file to upload.');
            return;
        }

        $allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
        $extension         = strtolower($this->boqFile->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            $this->addError('boqFile', 'The file must be of type: pdf, xlsx, xls, csv, jpg, jpeg, or png.');
            return;
        }

        $this->processing = true;

        try {
            DB::transaction(function () use ($extension) {
                // Create or get the project
                $project = $this->persistProject();

                // Create the BOQ
                $boq = $this->persistBoq($project);

                $fileName   = $this->boqFile->getClientOriginalName();
                $storedPath = $this->boqFile->storeAs('boq-uploads', Str::uuid() . '.' . $extension, 'local');
                $fileSize   = Storage::disk('local')->size($storedPath);
                $storedAbsPath = Storage::disk('local')->path($storedPath);

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

                // AI extraction
                $ai     = app(QuotationAiService::class);
                $result = $ai->parseBoq($storedAbsPath, [
                    'boq_id'       => $boq->id,
                    'project_name' => $this->projectName,
                ]);

                if (! $result['success']) {
                    $this->dispatch('toast', message: $result['error'] ?? 'AI extraction failed.', type: 'error');
                } elseif (empty($result['items'])) {
                    $this->dispatch('toast', message: 'The AI service could not extract any items from the uploaded file. Please add items manually.', type: 'warning');
                } else {
                    // Replace current items with the latest AI response
                    BoqItem::where('boq_id', $boq->id)->delete();
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
                            'is_selected'          => false,
                        ], $aiItem);
                    }

                    $this->persistItems($boq);

                    $this->dispatch('toast', message: count($result['items']) . ' items extracted successfully from the BOQ file.', type: 'success');
                }

                $this->boqFile = null;
            });

        } catch (\Throwable $e) {
            Log::error('CreateBoq::uploadBoq failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', message: 'Upload failed. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
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
            'is_selected'          => false,
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
            'projectDescription' => 'required|string|max:5000',
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
            'projectDescription' => 'required|string|max:5000',
        ]);

        if (empty($this->items)) {
            $this->dispatch('toast', message: 'Please add at least one item before submitting.', type: 'error');
            return;
        }

        $project = $this->persistProject();
        $boq     = $this->persistBoq($project, BoqStatusEnum::Submitted);
        $this->persistItems($boq);

        $this->redirect(route('enduser.boqs.show', $boq->uuid));
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.enduser.boqs.create-boq', [
            'itemStatuses' => QuotationItemStatusEnum::cases(),
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

            return $boq;
        }

        $boq = Boq::create([
            'project_id' => $project->id,
            'client_id'  => Auth::id(),
            'boq_no'     => $this->generateBoqNo(),
            'status'     => $status ?? BoqStatusEnum::Draft,
        ]);

        $this->boqId = $boq->id;

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
}
