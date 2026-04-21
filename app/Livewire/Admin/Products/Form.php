<?php

namespace App\Livewire\Admin\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Http;
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
        // Validate at least one source
        if (empty($this->aiPastedText) && ! $this->aiFile) {
            $this->addError('aiPastedText', 'Please upload a file or paste some text before analyzing.');
            return;
        }

        $this->resetErrorBag();
        $this->aiAnalyzing        = true;
        $this->aiExtractedProducts = [];
        $this->aiAccuracy          = 0.0;

        $apiKey = config('services.gemini.key');
        $model  = config('services.gemini.model', 'gemini-2.5-flash');
        $url    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Build the prompt
        $contextHints = implode(', ', array_filter([
            $this->aiPriceContext === 'vendor'  ? 'prices are vendor/supplier prices'  : null,
            $this->aiPriceContext === 'client'  ? 'prices are selling prices to client' : null,
            $this->aiIncludesEng  === 'yes'     ? 'engineering cost IS included in price' : null,
            $this->aiIncludesEng  === 'no'      ? 'engineering cost is NOT included'      : null,
            $this->aiIncludesInst === 'yes'     ? 'installation cost IS included in price' : null,
            $this->aiIncludesInst === 'no'      ? 'installation cost is NOT included'      : null,
        ]));

        $defaultMargin = match ($this->aiMarginHandling) {
            'auto_20' => 20,
            'auto_15' => 15,
            default   => 0,
        };

        $prompt = <<<PROMPT
You are a product catalog extraction assistant. Extract all products from the provided document or text and return ONLY a valid JSON array (no markdown, no explanation).

Context: {$contextHints}. Use SAR currency.

For each product, return an object with these exact keys:
- name (string, required)
- division (string or null — one of: HVAC, Electrical, Automation, Mechanical, Plumbing, Fire & Safety, Networking, ICT, Other)
- brand (string or null — the brand/manufacturer name as text)
- model_type (string or null)
- unit (string or null — e.g. pcs, meter, set)
- unit_price (number — the base unit price in SAR, 0 if unknown)
- engineering_price (number — engineering/commissioning cost per unit, 0 if not applicable)
- installation_price (number — installation cost per unit, 0 if not applicable)
- margin_percentage (number — use {$defaultMargin} as default if not specified)
- Do NOT duplicate the same product row more than once. If the same row appears multiple times due to OCR noise, return it only once.

Return only the JSON array. No text before or after it.
PROMPT;

        // Build the parts payload
        $parts = [];
        $fileText = '';

        if ($this->aiFile) {
            $path     = $this->aiFile->getRealPath();
            $mime     = $this->aiFile->getMimeType() ?: 'application/octet-stream';
            $b64      = base64_encode(file_get_contents($path));
            $ext      = strtolower($this->aiFile->getClientOriginalExtension());

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $parts[] = ['inline_data' => ['mime_type' => $mime, 'data' => $b64]];
            } elseif ($ext === 'pdf') {
                $parts[] = ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $b64]];
            } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
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
                try {
                    $zip = new \ZipArchive();
                    if ($zip->open($path) === true) {
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
            } elseif (in_array($mime, ['text/plain', 'text/csv'])) {
                $fileText = file_get_contents($path);
            } else {
                $parts[] = ['inline_data' => ['mime_type' => $mime, 'data' => $b64]];
            }
        }

        if (! empty($fileText)) {
            $parts[] = ['text' => $fileText];
        }

        if (! empty($this->aiPastedText)) {
            $parts[] = ['text' => $this->aiPastedText];
        }

        // Append the instruction prompt last
        $parts[] = ['text' => $prompt];

        try {
            $response = Http::timeout(120)->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => $parts],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.1,
                    'maxOutputTokens'  => 65536,
                ],
            ]);

            if (! $response->successful()) {
                $this->aiAnalyzing = false;
                $this->addError('aiPastedText', 'Gemini API error: ' . $response->status() . ' — ' . ($response->json('error.message') ?? 'Unknown error'));
                return;
            }

            $text = $response->json('candidates.0.content.parts.0.text') ?? '';

            // Strip any accidental markdown fences
            $text = preg_replace('/^```json\s*/i', '', trim($text));
            $text = preg_replace('/```\s*$/i', '', $text);

            $items = json_decode($text, true);

            // If direct decode fails, try to extract JSON array from response
            if (! is_array($items) && preg_match('/\[[\s\S]*\]/', $text, $m)) {
                $items = json_decode($m[0], true);
            }

            if (! is_array($items) || empty($items)) {
                \Log::warning('Admin AI response could not be parsed', ['raw' => mb_substr($text, 0, 2000)]);
                $this->aiAnalyzing = false;
                $this->addError('aiPastedText', 'Could not parse Gemini response. Please try again or rephrase your input.');
                return;
            }

            // Collapse duplicate rows from OCR/LLM noise before mapping to UI rows.
            $items = $this->deduplicateExtractedItems($items);

            // Normalise and compute totals
            $filled = 0;
            $this->aiExtractedProducts = array_values(array_map(function ($item, $idx) use (&$filled) {
                $unitPrice   = (float) ($item['unit_price']         ?? 0);
                $engPrice    = (float) ($item['engineering_price']  ?? 0);
                $instPrice   = (float) ($item['installation_price'] ?? 0);
                $margin      = (float) ($item['margin_percentage']  ?? 20);
                $base        = $unitPrice + $engPrice + $instPrice;
                $total       = $base * (1 + $margin / 100);

                if (! empty($item['name']) && $unitPrice > 0) {
                    $filled++;
                }

                return [
                    'name'               => $item['name']       ?? '',
                    'division'           => $item['division']   ?? null,
                    'brand'              => $item['brand']      ?? null,
                    'brand_id'           => null,  // user picks in table
                    'category_id'        => null,  // user picks in table
                    'model_type'         => $item['model_type'] ?? null,
                    'unit'               => $item['unit']       ?? null,
                    'unit_price'         => $unitPrice,
                    'engineering_price'  => $engPrice,
                    'installation_price' => $instPrice,
                    'margin_percentage'  => $margin,
                    'total'              => round($total, 2),
                ];
            }, $items, array_keys($items)));

            $this->aiAccuracy = count($items) > 0
                ? round(($filled / count($items)) * 100, 1)
                : 0.0;

        } catch (\Throwable $e) {
            $this->addError('aiPastedText', 'Connection error: ' . $e->getMessage());
        }

        $this->aiAnalyzing = false;
    }

    /**
     * Remove duplicate extracted products that represent the same line item.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateExtractedItems(array $items): array
    {
        $bucket = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name  = trim((string) ($item['name'] ?? ''));
            $model = trim((string) ($item['model_type'] ?? ''));
            $unit  = trim((string) ($item['unit'] ?? ''));
            $price = number_format((float) ($item['unit_price'] ?? 0), 2, '.', '');

            // Build a robust signature for "same product line".
            $signature = $this->normaliseKeyPart($name)
                . '|' . $this->normaliseKeyPart($model)
                . '|' . $this->normaliseKeyPart($unit)
                . '|' . $price;

            if (! isset($bucket[$signature])) {
                $bucket[$signature] = $item;
                continue;
            }

            // Merge missing fields from duplicates into the first canonical row.
            foreach (['division', 'brand', 'model_type', 'unit'] as $field) {
                $current = trim((string) ($bucket[$signature][$field] ?? ''));
                $incoming = trim((string) ($item[$field] ?? ''));

                if ($current === '' && $incoming !== '') {
                    $bucket[$signature][$field] = $incoming;
                }
            }

            foreach (['unit_price', 'engineering_price', 'installation_price', 'margin_percentage'] as $field) {
                $current = (float) ($bucket[$signature][$field] ?? 0);
                $incoming = (float) ($item[$field] ?? 0);

                if ($current <= 0 && $incoming > 0) {
                    $bucket[$signature][$field] = $incoming;
                }
            }
        }

        return array_values($bucket);
    }

    private function normaliseKeyPart(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
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
                'division'           => $item['division']    ?? null,
                'model_type'         => $item['model_type']  ?? null,
                'brand_id'           => ($item['brand_id']   ?? null) ?: null,
                'category_id'        => ($item['category_id'] ?? null) ?: null,
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
