<?php

namespace App\Livewire\Supplier\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SupplierProduct;
use App\Models\Unit;
use App\Enums\NotificationTypeEnum;
use App\Enums\UserTypeEnum;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?SupplierProduct $supplierProduct = null;
    public bool $isEditing  = false;
    public string $activeTab = 'manual';

    // ── Product fields ────────────────────────────────────────────────────────

    public string $name               = '';
    public string $division           = '';
    public mixed  $brand_id           = '';
    public mixed  $category_id        = '';
    public mixed  $unit_id            = '';
    public string $model_type         = '';
    public string $unit_price         = '0.00';
    public string $engineering_price  = '0.00';
    public string $installation_price = '0.00';
    public string $description        = '';
    public $datasheet                 = null;
    public ?string $existingDatasheet = null;

    // ── Supplier-specific fields ──────────────────────────────────────────────

    public string $leadTimeDays = '';
    public string $minOrderQty  = '';
    public string $notes        = '';
    public bool   $active       = true;

    // ── AI import fields ──────────────────────────────────────────────────────

    public string $aiPriceContext      = 'vendor';
    public string $aiIncludesEng       = 'no';
    public string $aiIncludesInst      = 'no';
    public string $aiMarginHandling    = 'auto_20';
    public string $aiCurrency          = 'SAR';
    public string $aiPastedText        = '';
    public $aiFile                     = null;
    public array  $aiExtractedProducts = [];
    public float  $aiAccuracy          = 0.0;
    public bool   $aiAnalyzing         = false;

    // ── Constants ─────────────────────────────────────────────────────────────

    public const DIVISIONS = [
        'HVAC', 'Electrical', 'Automation', 'Mechanical',
        'Plumbing', 'Fire & Safety', 'Networking', 'ICT', 'Other',
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(?SupplierProduct $supplierProduct = null): void
    {
        if ($supplierProduct && $supplierProduct->exists) {
            $this->supplierProduct = $supplierProduct;
            $this->isEditing       = true;

            $p = $supplierProduct->product;

            $this->name               = (string) $p->name;
            $this->division           = (string) ($p->division ?? '');
            $this->brand_id           = $p->brand_id ?? '';
            $this->category_id        = $p->category_id ?? '';
            $this->unit_id            = $p->unit_id ?? '';
            $this->model_type         = (string) ($p->model_type ?? '');
            $this->unit_price         = number_format((float) ($p->unit_price ?? 0), 2, '.', '');
            $this->engineering_price  = number_format((float) ($p->engineering_price ?? 0), 2, '.', '');
            $this->installation_price = number_format((float) ($p->installation_price ?? 0), 2, '.', '');
            $this->description        = (string) ($p->description ?? '');
            $this->existingDatasheet  = $p->datasheet_path;

            $this->leadTimeDays = (string) ($supplierProduct->lead_time_days ?? '');
            $this->minOrderQty  = (string) ($supplierProduct->min_order_qty ?? '');
            $this->notes        = (string) ($supplierProduct->notes ?? '');
            $this->active       = (bool) $supplierProduct->active;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function computeFinalPrice(): float
    {
        $base   = (float) $this->unit_price
                + (float) $this->engineering_price
                + (float) $this->installation_price;
        return $base;
    }

    private function generateSku(): string
    {
        do {
            $candidate = 'SUP-' . date('Ym') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        } while (Product::withTrashed()->where('sku', $candidate)->exists());

        return $candidate;
    }

    public function removeDatasheet(): void
    {
        if ($this->existingDatasheet) {
            Storage::disk('public')->delete($this->existingDatasheet);
        }
        $this->existingDatasheet = null;
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate([
            'name'               => ['required', 'string', 'max:255'],
            'division'           => ['nullable', 'string', 'max:100'],
            'model_type'         => ['nullable', 'string', 'max:255'],
            'brand_id'           => ['nullable', 'integer', 'exists:brands,id'],
            'category_id'        => ['nullable', 'integer', 'exists:categories,id'],
            'unit_id'            => ['nullable', 'integer', 'exists:units,id'],
            'unit_price'         => ['required', 'numeric', 'min:0'],
            'engineering_price'  => ['nullable', 'numeric', 'min:0'],
            'installation_price' => ['nullable', 'numeric', 'min:0'],
            'description'        => ['nullable', 'string'],
            'datasheet'          => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'leadTimeDays'       => ['nullable', 'integer', 'min:0'],
            'minOrderQty'        => ['nullable', 'numeric', 'min:0'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $productData = [
            'name'               => $this->name,
            'division'           => $this->division ?: null,
            'brand_id'           => $this->brand_id ?: null,
            'category_id'        => $this->category_id ?: null,
            'unit_id'            => $this->unit_id ?: null,
            'model_type'         => $this->model_type ?: null,
            'unit_price'         => $this->unit_price,
            'engineering_price'  => $this->engineering_price ?: 0,
            'installation_price' => $this->installation_price ?: 0,
            'margin_percentage'  => 0,
            'description'        => $this->description ?: null,
            'active'             => false,
        ];

        if ($this->datasheet) {
            if ($this->existingDatasheet) {
                Storage::disk('public')->delete($this->existingDatasheet);
            }
            $productData['datasheet_path'] = $this->datasheet->store('datasheets', 'public');
        } elseif ($this->isEditing && $this->existingDatasheet === null) {
            $productData['datasheet_path'] = null;
        }

        if ($this->isEditing) {
            $this->supplierProduct->product->update($productData);
            $this->supplierProduct->update([
                'price'           => $this->unit_price,
                'lead_time_days'  => $this->leadTimeDays ?: null,
                'min_order_qty'   => $this->minOrderQty ?: null,
                'notes'           => $this->notes ?: null,
                'active'          => $this->active,
                'approval_status' => 'pending',
                'approved_at'     => null,
                'approved_by'     => null,
                'rejection_reason'=> null,
            ]);

            // Notify admins about the updated product
            app(NotificationService::class)->sendToUserType(
                title: 'Product Updated – Needs Re-approval',
                body: Auth::user()->name . ' updated product "' . $this->name . '" and it needs re-approval.',
                type: NotificationTypeEnum::ProductSubmitted,
                userType: UserTypeEnum::Admin,
                actionUrl: route('admin.suppliers.products'),
            );
        } else {
            $productData['sku'] = $this->generateSku();
            $product = Product::create($productData);
            SupplierProduct::create([
                'supplier_id'     => Auth::id(),
                'product_id'      => $product->id,
                'price'           => $this->unit_price,
                'lead_time_days'  => $this->leadTimeDays ?: null,
                'min_order_qty'   => $this->minOrderQty ?: null,
                'notes'           => $this->notes ?: null,
                'active'          => true,
                'approval_status' => 'pending',
            ]);

            // Notify admins about the new product
            app(NotificationService::class)->sendToUserType(
                title: 'New Product Submitted for Approval',
                body: Auth::user()->name . ' submitted a new product "' . $this->name . '" for approval.',
                type: NotificationTypeEnum::ProductSubmitted,
                userType: UserTypeEnum::Admin,
                actionUrl: route('admin.suppliers.products'),
            );
        }

        $this->redirect(route('supplier.products.index'), navigate: true);
    }

    public function analyzeText(): void
    {
        if (empty($this->aiPastedText) && ! $this->aiFile) {
            $this->addError('aiPastedText', 'Please upload a file or paste some text before analyzing.');
            return;
        }

        $this->resetErrorBag();
        $this->aiAnalyzing         = true;
        $this->aiExtractedProducts = [];
        $this->aiAccuracy          = 0.0;

        $apiKey = config('services.gemini.key');
        $model  = config('services.gemini.model', 'gemini-2.5-flash');
        $url    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $parts = [];
        $fileText = '';

        if ($this->aiFile) {
            $ext  = strtolower($this->aiFile->getClientOriginalExtension());
            $mime = $this->aiFile->getMimeType();
            $b64  = base64_encode(file_get_contents($this->aiFile->getRealPath()));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $parts[] = ['inline_data' => ['mime_type' => $mime, 'data' => $b64]];
            } elseif ($ext === 'pdf') {
                $parts[] = ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $b64]];
            } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
                // Parse spreadsheet into text for the AI prompt
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->aiFile->getRealPath());
                    $rows = [];
                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $sheetName = $sheet->getTitle();
                        $rows[] = "--- Sheet: {$sheetName} ---";
                        foreach ($sheet->toArray(null, true, true, true) as $row) {
                            $cells = array_map(fn ($c) => trim((string) ($c ?? '')), $row);
                            $line = implode(' | ', $cells);
                            if (trim(str_replace('|', '', $line)) !== '') {
                                $rows[] = $line;
                            }
                        }
                    }
                    $fileText = implode("\n", $rows);
                } catch (\Throwable $e) {
                    $this->addError('aiPastedText', 'Could not parse the spreadsheet: ' . $e->getMessage());
                    $this->aiAnalyzing = false;
                    return;
                }
            } elseif ($ext === 'docx') {
                // Extract text from DOCX
                try {
                    $zip = new \ZipArchive();
                    if ($zip->open($this->aiFile->getRealPath()) === true) {
                        $content = $zip->getFromName('word/document.xml');
                        $zip->close();
                        if ($content) {
                            $fileText = strip_tags(str_replace('<', ' <', $content));
                            $fileText = preg_replace('/\s+/', ' ', $fileText);
                        }
                    }
                } catch (\Throwable $e) {
                    $this->addError('aiPastedText', 'Could not parse the document.');
                    $this->aiAnalyzing = false;
                    return;
                }
            }
        }

        $contextHints = implode(', ', array_filter([
            $this->aiPriceContext === 'vendor'  ? 'prices are vendor/supplier prices (cost prices)'  : null,
            $this->aiPriceContext === 'client'  ? 'prices are selling prices to client' : null,
            $this->aiIncludesEng  === 'yes'     ? 'prices include engineering cost'    : null,
            $this->aiIncludesEng  === 'no'      ? 'prices do NOT include engineering'  : null,
            $this->aiIncludesInst === 'yes'     ? 'prices include installation cost'   : null,
            $this->aiIncludesInst === 'no'      ? 'prices do NOT include installation' : null,
        ]));

        $marginHandling = match ($this->aiMarginHandling) {
            'auto_20'  => 'Apply 20% margin automatically',
            'auto_15'  => 'Apply 15% margin automatically',
            'keep'     => 'Keep original price, margin = 0',
            'override' => 'Set margin = 0, user will override',
            default    => 'Apply 20% margin',
        };

        $divisions = implode(', ', self::DIVISIONS);

        $combinedText = trim($fileText . "\n\n" . $this->aiPastedText);

        $prompt = <<<PROMPT
You are a data extraction assistant for a supplier catalogue. Extract ALL products from the provided document or text.
Context: {$contextHints}. Margin handling: {$marginHandling}.
Available divisions: {$divisions}.

Return ONLY a valid JSON array (no markdown, no code fences) with this exact structure per item:
[
  {
    "name": "exact product name",
    "division": "one of the available divisions or empty string",
    "brand": "brand name string or empty string",
    "category": "category name string or empty string",
    "model_type": "model or type number or empty string",
    "unit_price": 0.00,
    "engineering_price": 0.00,
    "installation_price": 0.00,
    "margin_percentage": 20,
    "lead_time_days": null,
    "notes": ""
  }
]
Rules:
- unit_price: required numeric SAR value (use 0 if not found)
- engineering_price and installation_price: numeric or 0
- margin_percentage: percentage number (e.g. 20 for 20%)
- lead_time_days: integer or null
- Extract every product, do not skip any

Text:
{$combinedText}
PROMPT;

        $parts[] = ['text' => $prompt];

        try {
            $response = Http::timeout(120)->post($url, [
                'contents'         => [['parts' => $parts]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 65536],
            ]);

            if ($response->failed()) {
                $this->addError('aiPastedText', 'AI service error: ' . $response->status());
                $this->aiAnalyzing = false;
                return;
            }

            $raw  = $response->json('candidates.0.content.parts.0.text', '');
            $raw  = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $raw  = preg_replace('/\s*```$/i', '', $raw);

            // Try direct decode first
            $data = json_decode($raw, true);

            // If that fails, try to find JSON array in the response
            if (! is_array($data) && preg_match('/\[[\s\S]*\]/', $raw, $m)) {
                $data = json_decode($m[0], true);
            }

            if (! is_array($data) || empty($data)) {
                \Log::warning('AI response could not be parsed', ['raw' => mb_substr($raw, 0, 2000)]);
                $this->addError('aiPastedText', 'Could not parse AI response. Please try again.');
                $this->aiAnalyzing = false;
                return;
            }

            $brands     = Brand::all()->keyBy(fn($b) => strtolower(trim($b->name)));
            $categories = Category::all()->keyBy(fn($c) => strtolower(trim($c->name)));

            foreach ($data as &$item) {
                $item['unit_price']         = (float) ($item['unit_price'] ?? 0);
                $item['engineering_price']  = (float) ($item['engineering_price'] ?? 0);
                $item['installation_price'] = (float) ($item['installation_price'] ?? 0);
                $item['margin_percentage']  = (float) ($item['margin_percentage'] ?? 20);
                $item['lead_time_days']     = isset($item['lead_time_days']) ? (int) $item['lead_time_days'] : null;
                $item['notes']              = (string) ($item['notes'] ?? '');

                $base          = $item['unit_price'] + $item['engineering_price'] + $item['installation_price'];
                $item['total'] = $base * (1 + $item['margin_percentage'] / 100);

                $brandMatch          = $brands->get(strtolower(trim($item['brand'] ?? '')));
                $item['brand_id']    = $brandMatch?->id;

                $catMatch            = $categories->get(strtolower(trim($item['category'] ?? '')));
                $item['category_id'] = $catMatch?->id;
            }
            unset($item);

            $this->aiExtractedProducts = $data;
            $this->aiAccuracy          = 100;
        } catch (\Throwable $e) {
            $this->addError('aiPastedText', 'Error: ' . $e->getMessage());
        }

        $this->aiAnalyzing = false;
    }

    public function confirmImport(): void
    {
        $supplierId = Auth::id();
        $imported   = 0;

        foreach ($this->aiExtractedProducts as $item) {
            if (empty(trim($item['name'] ?? ''))) {
                continue;
            }

            $product = Product::create([
                'sku'                => $this->generateSku(),
                'name'               => $item['name'],
                'division'           => $item['division'] ?: null,
                'brand_id'           => $item['brand_id'] ?: null,
                'category_id'        => $item['category_id'] ?: null,
                'model_type'         => $item['model_type'] ?: null,
                'unit_price'         => $item['unit_price'],
                'engineering_price'  => $item['engineering_price'],
                'installation_price' => $item['installation_price'],
                'margin_percentage'  => 0,
                'active'             => false,
            ]);

            SupplierProduct::create([
                'supplier_id'     => $supplierId,
                'product_id'      => $product->id,
                'price'           => $item['unit_price'],
                'lead_time_days'  => $item['lead_time_days'] ?: null,
                'notes'           => $item['notes'] ?: null,
                'active'          => true,
                'approval_status' => 'pending',
            ]);

            $imported++;
        }

        session()->flash('success', "{$imported} product(s) imported to your catalogue.");

        if ($imported > 0) {
            app(NotificationService::class)->sendToUserType(
                title: 'New Products Submitted for Approval',
                body: Auth::user()->name . ' submitted ' . $imported . ' product(s) via AI import for approval.',
                type: NotificationTypeEnum::ProductSubmitted,
                userType: UserTypeEnum::Admin,
                actionUrl: route('admin.suppliers.products'),
            );
        }

        $this->redirect(route('supplier.products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.supplier.products.form', [
            'brands'     => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'units'      => Unit::orderBy('name')->get(),
            'divisions'  => self::DIVISIONS,
            'finalPrice' => $this->computeFinalPrice(),
        ]);
    }
}
