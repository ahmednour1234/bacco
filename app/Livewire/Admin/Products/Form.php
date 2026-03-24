<?php

namespace App\Livewire\Admin\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    // ── State ─────────────────────────────────────────────────────────────────

    public ?Product $product = null;

    public bool $isEditing = false;

    public string $activeTab = 'manual';

    // ── Product fields ────────────────────────────────────────────────────────

    public string $name = '';

    public string $division = '';

    public string $model_type = '';

    public mixed $brand_id = '';

    public mixed $category_id = '';

    public mixed $unit_id = '';

    public string $unit_price = '0.00';

    public string $engineering_price = '0.00';

    public string $installation_price = '0.00';

    public string $margin_percentage = '15';

    public string $description = '';

    public bool $active = true;

    public $datasheet = null;

    public ?string $existingDatasheet = null;

    // ── AI Import fields ──────────────────────────────────────────────────────

    public string $aiPriceContext = 'vendor';

    public string $aiIncludesEng = 'no';

    public string $aiIncludesInst = 'no';

    public string $aiMarginHandling = 'auto_20';

    public string $aiCurrency = 'SAR';

    public string $aiPastedText = '';

    public $aiFile = null;

    public array $aiExtractedProducts = [];

    public bool $aiAnalyzing = false;

    public float $aiAccuracy = 0.0;

    // ── Constants ─────────────────────────────────────────────────────────────

    public const DIVISIONS = [
        'HVAC',
        'Electrical',
        'Automation',
        'Mechanical',
        'Plumbing',
        'Fire & Safety',
        'Networking',
        'ICT',
        'Other',
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product            = $product;
            $this->isEditing          = true;
            $this->name               = (string) $product->name;
            $this->division           = (string) ($product->division ?? '');
            $this->model_type         = (string) ($product->model_type ?? '');
            $this->brand_id           = $product->brand_id ?? '';
            $this->category_id        = $product->category_id ?? '';
            $this->unit_id            = $product->unit_id ?? '';
            $this->unit_price         = number_format((float) ($product->unit_price ?? 0), 2, '.', '');
            $this->engineering_price  = number_format((float) ($product->engineering_price ?? 0), 2, '.', '');
            $this->installation_price = number_format((float) ($product->installation_price ?? 0), 2, '.', '');
            $this->margin_percentage  = number_format((float) ($product->margin_percentage ?? 15), 2, '.', '');
            $this->description        = (string) ($product->description ?? '');
            $this->active             = (bool) $product->active;
            $this->existingDatasheet  = $product->datasheet_path;
        }
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'division'           => ['nullable', 'string', 'max:100'],
            'model_type'         => ['nullable', 'string', 'max:255'],
            'brand_id'           => ['nullable', 'integer', 'exists:brands,id'],
            'category_id'        => ['nullable', 'integer', 'exists:categories,id'],
            'unit_id'            => ['nullable', 'integer', 'exists:units,id'],
            'unit_price'         => ['nullable', 'numeric', 'min:0'],
            'engineering_price'  => ['nullable', 'numeric', 'min:0'],
            'installation_price' => ['nullable', 'numeric', 'min:0'],
            'margin_percentage'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description'        => ['nullable', 'string'],
            'active'             => ['boolean'],
            'datasheet'          => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function save(): mixed
    {
        $data = $this->validate();

        // Normalise empty FK strings to null
        $data['brand_id']    = $data['brand_id']    ?: null;
        $data['category_id'] = $data['category_id'] ?: null;
        $data['unit_id']     = $data['unit_id']      ?: null;

        // Handle file upload
        if ($this->datasheet) {
            if ($this->existingDatasheet) {
                Storage::disk('public')->delete($this->existingDatasheet);
            }
            $data['datasheet_path'] = $this->datasheet->store('datasheets', 'public');
        }

        unset($data['datasheet']);

        if ($this->isEditing && $this->product) {
            $this->product->update($data);

            return $this->redirect(route('admin.products.index'), navigate: true);
        }

        $data['sku'] = $this->generateSku();
        Product::create($data);

        session()->flash('success', 'Product created successfully.');

        return $this->redirect(route('admin.products.index'), navigate: true);
    }

    private function generateSku(): string
    {
        do {
            $candidate = 'QMT-' . date('Ym') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        } while (Product::withTrashed()->where('sku', $candidate)->exists());

        return $candidate;
    }

    public function analyzeText(): void
    {
        $this->aiAnalyzing = true;
        // TODO: integrate AI parsing service (e.g., call an LLM API here)
        $this->aiExtractedProducts = [];
        $this->aiAccuracy          = 0.0;
        $this->aiAnalyzing         = false;

        $this->addError('aiPastedText', 'AI analysis service is not yet configured. Please use Manual Entry.');
    }

    public function confirmImport(): mixed
    {
        if (empty($this->aiExtractedProducts)) {
            return null;
        }

        $margin = match ($this->aiMarginHandling) {
            'auto_20' => 20.0,
            'auto_15' => 15.0,
            default   => null,
        };

        foreach ($this->aiExtractedProducts as $item) {
            Product::create([
                'name'               => $item['name'],
                'division'           => $item['division'] ?? null,
                'model_type'         => $item['model_type'] ?? null,
                'unit_price'         => (float) ($item['unit_price'] ?? 0),
                'engineering_price'  => (float) ($item['engineering_price'] ?? 0),
                'installation_price' => (float) ($item['installation_price'] ?? 0),
                'margin_percentage'  => $margin ?? (float) ($item['margin_percentage'] ?? 0),
                'sku'                => $this->generateSku(),
                'active'             => true,
            ]);
        }

        $count = count($this->aiExtractedProducts);
        session()->flash('success', "{$count} product(s) imported successfully.");

        return $this->redirect(route('admin.products.index'), navigate: true);
    }

    public function removeDatasheet(): void
    {
        if ($this->existingDatasheet) {
            Storage::disk('public')->delete($this->existingDatasheet);
            $this->product?->update(['datasheet_path' => null]);
            $this->existingDatasheet = null;
        }
        $this->reset('datasheet');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $brands     = Brand::where('active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        $base       = (float) $this->unit_price
                    + (float) $this->engineering_price
                    + (float) $this->installation_price;
        $finalPrice = $base * (1 + (float) $this->margin_percentage / 100);

        return view('livewire.admin.products.form', [
            'brands'     => $brands,
            'categories' => $categories,
            'units'      => $units,
            'finalPrice' => $finalPrice,
            'divisions'  => self::DIVISIONS,
        ]);
    }
}
