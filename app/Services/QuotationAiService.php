<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuotationAiService
{
    private string $baseUrl;

    private string $parseEndpoint;

    private string $apiKey;

    private int $timeout;

    private bool $testMode;

    public function __construct()
    {
        $this->baseUrl       = rtrim((string) config('services.ai_quotation.base_url', ''), '/');
        $this->parseEndpoint = ltrim((string) config('services.ai_quotation.parse_endpoint', 'parse'), '/');
        $this->apiKey        = (string) config('services.ai_quotation.api_key', '');
        $this->timeout       = (int) config('services.ai_quotation.timeout', 180);
        $this->testMode      = (bool) config('services.ai_quotation.test_mode', false);
    }

    /**
     * Return a hardcoded dummy response for local testing.
     * Activated when AI_QUOTATION_TEST_MODE=true in .env.
     *
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    private function mockResponse(): array
    {
        sleep(2); // Simulate network delay so the UI spinner feels real.

        return [
            'success' => true,
            'error'   => null,
            'items'   => [
                [
                    'product_name'         => 'Steel Square Hollow Section 100×100×4mm',
                    'quantity'             => 50,
                    'unit'                 => 'pcs',
                    'category'             => 'Structural Steel',
                    'brand'                => '',
                    'engineering_required' => false,
                    'unit_price'           => 0,
                    'ai_extracted'         => true,
                ],
                [
                    'product_name'         => 'Cement OPC 42.5 (50 kg bag)',
                    'quantity'             => 200,
                    'unit'                 => 'bag',
                    'category'             => 'Concrete Works',
                    'brand'                => 'Lafarge',
                    'engineering_required' => false,
                    'unit_price'           => 0,
                    'ai_extracted'         => true,
                ],
                [
                    'product_name'         => 'Ceramic Floor Tile 60×60 cm (Beige)',
                    'quantity'             => 300,
                    'unit'                 => 'm2',
                    'category'             => 'Flooring',
                    'brand'                => 'RAK Ceramics',
                    'engineering_required' => false,
                    'unit_price'           => 0,
                    'ai_extracted'         => true,
                ],
                [
                    'product_name'         => 'UPVC Window 120×150 cm Double Glazed',
                    'quantity'             => 12,
                    'unit'                 => 'set',
                    'category'             => 'Windows & Doors',
                    'brand'                => '',
                    'engineering_required' => true,
                    'unit_price'           => 0,
                    'ai_extracted'         => true,
                ],
                [
                    'product_name'         => 'Electrical Conduit PVC 25mm (3m)',
                    'quantity'             => 100,
                    'unit'                 => 'pcs',
                    'category'             => 'Electrical',
                    'brand'                => '',
                    'engineering_required' => false,
                    'unit_price'           => 0,
                    'ai_extracted'         => true,
                ],
            ],
        ];
    }

    /**
     * Parse a BOQ file and extract items.
     * For Excel/CSV files: parsed directly with PhpSpreadsheet (no API calls).
     * Results are cached by file hash.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file  Uploaded file or stored path
     * @param  array<string, mixed>  $context  Extra context (boq_id, project_name, project_status)
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    public function parseBoq(UploadedFile|string $file, array $context = []): array
    {
        // ── Test / offline mode — never calls any external API ───────────────
        if ($this->testMode) {
            Log::info('QuotationAiService: Running in TEST MODE — returning mock data.');

            $mock = $this->mockResponse();
            $mock['items'] = array_map([$this, 'normaliseItem'], $mock['items']);

            return $mock;
        }

        // ── Resolve absolute path ────────────────────────────────────────────
        if ($file instanceof UploadedFile) {
            $absPath = (string) $file->getRealPath();
            $ext     = strtolower($file->getClientOriginalExtension());
        } else {
            $absPath = file_exists($file) ? $file : storage_path('app/' . $file);
            $ext     = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        }

        // ── Cache look-up: same file content → return stored result ─────────
        $fileHash = is_file($absPath) ? hash_file('sha256', $absPath) : null;
        if ($fileHash !== null) {
            $cached = Cache::get('boq_analysis_' . $fileHash);
            if ($cached !== null) {
                Log::info('QuotationAiService: Returning cached analysis.', ['hash' => $fileHash]);
                return $cached;
            }
        }

        // ── Direct Excel/CSV parsing — no external API needed ────────────────
        if (in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            $result = $this->parseSpreadsheetDirect($absPath);
        } else {
            return $this->failure('Only Excel (.xlsx, .xls) and CSV files are supported for direct parsing. Please upload an Excel or CSV file.');
        }

        // ── Cache successful result for 30 days ──────────────────────────────
        if ($result['success'] && $fileHash !== null) {
            Cache::put('boq_analysis_' . $fileHash, $result, now()->addDays(30));
        }

        return $result;
    }

    /**
     * Parse an Excel or CSV file directly using PhpSpreadsheet.
     * Auto-detects header rows and maps columns to BOQ item fields.
     *
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    private function parseSpreadsheetDirect(string $absPath): array
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        try {
            if ($ext === 'csv') {
                $reader = IOFactory::createReader('Csv');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($absPath);
                $sheets = [$spreadsheet->getActiveSheet()];
            } else {
                $readerTypes = $ext === 'xls' ? ['Xls', 'Xlsx'] : ['Xlsx', 'Xls'];
                $spreadsheet = null;
                foreach ($readerTypes as $type) {
                    try {
                        $reader = IOFactory::createReader($type);
                        $reader->setReadDataOnly(true);
                        if ($reader->canRead($absPath)) {
                            $spreadsheet = $reader->load($absPath);
                            break;
                        }
                    } catch (\Throwable) {
                        continue;
                    }
                }

                if ($spreadsheet === null) {
                    return $this->failure('Could not read the Excel file. Please make sure it is a valid .xlsx or .xls file.');
                }

                $sheets = $spreadsheet->getAllSheets();
            }

            $allItems = [];

            foreach ($sheets as $sheet) {
                $highestRow    = $sheet->getHighestDataRow();
                $highestColumn = $sheet->getHighestDataColumn();

                if ($highestRow < 2) {
                    continue;
                }

                $grid = [];
                foreach ($sheet->getRowIterator(1, $highestRow) as $rowObj) {
                    $rowData      = [];
                    $cellIterator = $rowObj->getCellIterator('A', $highestColumn);
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        try {
                            $value = $cell->getCalculatedValue();
                        } catch (\Throwable) {
                            // Structured table references (Table13[[#This Row],...]) can't be
                            // resolved by PhpSpreadsheet — use the value Excel last saved instead.
                            $value = $cell->getOldCalculatedValue() ?? $cell->getValue();
                        }
                        // If still a raw formula string, blank it out
                        if (is_string($value) && str_starts_with(ltrim($value), '=')) {
                            $value = '';
                        }
                        $rowData[] = $value;
                    }
                    $grid[] = $rowData;
                }

                // ── Find the header row (first row with meaningful column names) ──
                $headerRowIndex = null;
                $headerMap      = [];

                // Keywords mapped to our field names
                $fieldKeywords = [
                    'description' => ['description', 'item description', 'item name', 'name', 'material', 'scope', 'work', 'activity', 'details', 'وصف', 'البند', 'البنود', 'الوصف'],
                    'quantity'    => ['quantity', 'qty', 'amount', 'no.', 'nos', 'count', 'الكمية', 'كمية'],
                    'unit'        => ['unit', 'uom', 'u/m', 'الوحدة', 'وحدة'],
                    'unit_price'  => ['unit price', 'unit cost', 'rate', 'price', 'cost', 'سعر الوحدة', 'السعر'],
                    'total_price' => ['total', 'total price', 'amount', 'line total', 'الإجمالي', 'المجموع', 'إجمالي'],
                    'brand'       => ['brand', 'make', 'manufacturer', 'العلامة', 'الماركة'],
                    'category'    => ['category', 'section', 'division', 'type', 'القسم', 'النوع'],
                    'item_code'   => ['item code', 'code', 'ref', 'no', '#', 'كود', 'الكود', 'رقم'],
                ];

                for ($r = 0; $r < min(15, count($grid)); $r++) {
                    $row       = $grid[$r];
                    $matchCount = 0;
                    $tempMap   = [];

                    foreach ($row as $colIdx => $cellValue) {
                        $cell = strtolower(trim((string) $cellValue));
                        if ($cell === '') {
                            continue;
                        }

                        foreach ($fieldKeywords as $field => $keywords) {
                            foreach ($keywords as $kw) {
                                if (str_contains($cell, $kw)) {
                                    if (! isset($tempMap[$field])) {
                                        $tempMap[$field] = $colIdx;
                                        $matchCount++;
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    if ($matchCount >= 2) {
                        $headerRowIndex = $r;
                        $headerMap      = $tempMap;
                        break;
                    }
                }

                // ── If no header found, try to guess from data shape ─────────
                if ($headerRowIndex === null) {
                    // Use row 0 as header anyway if it has text cells
                    $headerRowIndex = 0;
                    foreach ($grid[0] as $colIdx => $cellValue) {
                        $cell = strtolower(trim((string) $cellValue));
                        if ($cell === '') {
                            continue;
                        }
                        // Map positionally: first long-text col = description, first numeric col = quantity
                        foreach ($fieldKeywords as $field => $keywords) {
                            foreach ($keywords as $kw) {
                                if (str_contains($cell, $kw)) {
                                    if (! isset($headerMap[$field])) {
                                        $headerMap[$field] = $colIdx;
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    // Last resort: find the first column with long text in row 1
                    if (! isset($headerMap['description'])) {
                        foreach ($grid[1] ?? [] as $colIdx => $cellValue) {
                            if (strlen(trim((string) $cellValue)) > 10) {
                                $headerMap['description'] = $colIdx;
                                break;
                            }
                        }
                    }
                }

                // ── Extract items from rows after header ─────────────────────
                $dataStartRow = $headerRowIndex + 1;

                for ($r = $dataStartRow; $r < count($grid); $r++) {
                    $row = $grid[$r];

                    // Get description
                    $description = '';
                    if (isset($headerMap['description'])) {
                        $description = trim((string) ($row[$headerMap['description']] ?? ''));
                    } else {
                        // Find the longest non-numeric cell value as description
                        foreach ($row as $v) {
                            $v = trim((string) $v);
                            if (strlen($v) > strlen($description) && ! is_numeric($v)) {
                                $description = $v;
                            }
                        }
                    }

                    // Skip blank rows and section headers (no quantity)
                    if ($description === '') {
                        continue;
                    }

                    // Skip rows that look like headers or section titles (no numeric data)
                    $hasNumeric = false;
                    foreach ($row as $v) {
                        if (is_numeric($v) && $v > 0) {
                            $hasNumeric = true;
                            break;
                        }
                    }
                    if (! $hasNumeric && strlen($description) < 80) {
                        // Likely a section header — skip
                        continue;
                    }

                    $quantity   = null;
                    $unit       = '';
                    $unitPrice  = null;
                    $totalPrice = null;
                    $brand      = '';
                    $category   = '';

                    if (isset($headerMap['quantity'])) {
                        $v = $row[$headerMap['quantity']] ?? null;
                        if (is_numeric($v) && $v > 0) {
                            $quantity = (float) $v;
                        }
                    }

                    if (isset($headerMap['unit'])) {
                        $unit = trim((string) ($row[$headerMap['unit']] ?? ''));
                    }

                    if (isset($headerMap['unit_price'])) {
                        $v = $row[$headerMap['unit_price']] ?? null;
                        if (is_numeric($v) && $v > 0) {
                            $unitPrice = (float) $v;
                        }
                    }

                    if (isset($headerMap['total_price'])) {
                        $v = $row[$headerMap['total_price']] ?? null;
                        if (is_numeric($v) && $v > 0) {
                            $totalPrice = (float) $v;
                        }
                    }

                    if (isset($headerMap['brand'])) {
                        $brand = trim((string) ($row[$headerMap['brand']] ?? ''));
                    }

                    if (isset($headerMap['category'])) {
                        $category = trim((string) ($row[$headerMap['category']] ?? ''));
                    }

                    // Derive missing price
                    if ($unitPrice === null && $totalPrice !== null && $quantity !== null && $quantity > 0) {
                        $unitPrice = $totalPrice / $quantity;
                    }
                    if ($totalPrice === null && $unitPrice !== null && $quantity !== null) {
                        $totalPrice = $unitPrice * $quantity;
                    }

                    // ── Supply-only filter ───────────────────────────────
                    $supply = $this->filterSupplyItem($description);
                    if (! $supply['keep']) {
                        continue;
                    }

                    $allItems[] = [
                        'description'          => $supply['description'],
                        'quantity'             => $quantity ?? 1,
                        'unit'                 => $unit,
                        'category'             => $category,
                        'brand'                => $brand,
                        'status'               => 'pending',
                        'engineering_required' => false,
                        'unit_price'           => $unitPrice,
                        'confidence'           => 0.9,
                        'raw_data'             => [
                            'original_description' => $description,
                            'cleaned_description'  => $supply['description'],
                            'extraction_type'      => $supply['extraction_type'],
                        ],
                        'ai_extracted'         => true,
                    ];
                }
            }

            if (empty($allItems)) {
                return $this->failure('No items could be extracted from the file. Please make sure the file has rows with item descriptions and quantities.');
            }

            Log::info('QuotationAiService: Direct spreadsheet parse succeeded.', ['items' => count($allItems)]);

            return [
                'success' => true,
                'items'   => $allItems,
                'error'   => null,
            ];

        } catch (\Throwable $e) {
            Log::error('QuotationAiService: parseSpreadsheetDirect failed.', [
                'message' => $e->getMessage(),
            ]);

            return $this->failure('Failed to read the Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Call the custom primary AI extract endpoint.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file
     * @param  array<string, mixed>  $context
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    private function callPrimaryApi(UploadedFile|string $file, array $context = []): array
    {
        if (empty($this->baseUrl)) {
            Log::warning('QuotationAiService: AI_QUOTATION_BASE_URL is not configured.');

            return $this->failure('AI service is not configured.');
        }

        $url = "{$this->baseUrl}/extract/products";

        try {
            $request = Http::timeout($this->timeout)->connectTimeout(5)->asMultipart();

            if ($this->apiKey !== '') {
                $request = $request->withToken($this->apiKey);
            }

            // Attach file
            if ($file instanceof UploadedFile) {
                $mime    = $this->mimeForExtension($file->getClientOriginalExtension());
                $request = $request->attach(
                    'file',
                    $file->getContent(),
                    $file->getClientOriginalName(),
                    ['Content-Type' => $mime]
                );
            } else {
                // $file is an absolute path (passed from uploadBoq via Storage::disk('local')->path())
                // Use it directly — do NOT prepend storage_path() again.
                $absPath  = file_exists($file) ? $file : storage_path('app/' . $file);
                $ext      = strtolower(pathinfo(basename($file), PATHINFO_EXTENSION));
                $mime     = $this->mimeForExtension($ext);
                $request  = $request->attach(
                    'file',
                    file_get_contents($absPath),
                    basename($file),
                    ['Content-Type' => $mime]
                );
            }

            // Attach context fields
            foreach ($context as $key => $value) {
                $request = $request->attach($key, (string) $value);
            }

            $response = $request->post($url);

        } catch (ConnectionException $e) {
            Log::error('QuotationAiService: Connection timeout or refused.', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('AI service timed out or is unavailable. Please try again later.', true);
        } catch (\Throwable $e) {
            Log::error('QuotationAiService: Unexpected error calling AI endpoint.', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('An unexpected error occurred while contacting the AI service.', true);
        }

        if (! $response->successful()) {
            $body = $response->body();

            Log::error('QuotationAiService: AI endpoint returned non-2xx response.', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $body,
            ]);

            // Expose FastAPI validation detail in debug mode so we can see exactly what failed
            $detail = '';
            if (config('app.debug') && $response->status() === 422) {
                $errJson = $response->json();
                if (is_array($errJson['detail'] ?? null)) {
                    $msgs   = array_map(fn($e) => implode(' → ', (array) ($e['loc'] ?? [])) . ': ' . ($e['msg'] ?? ''), $errJson['detail']);
                    $detail = ' — ' . implode('; ', $msgs);
                }
            }

            return $this->failure("AI service returned HTTP {$response->status()}{$detail}.");
        }

        $json = $response->json();

        if (! is_array($json)) {
            Log::error('QuotationAiService: AI response is not valid JSON.', [
                'body' => $response->body(),
            ]);

            return $this->failure('AI service returned an invalid response.');
        }

        if (! ($json['success'] ?? true)) {
            $message = $json['message'] ?? 'AI service reported a failure.';
            Log::warning('QuotationAiService: AI returned success=false.', compact('message'));

            return $this->failure($message);
        }

        $items = data_get($json, 'data.items', []);

        if (! is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            Log::info('QuotationAiService: AI returned zero items for BOQ.', $context);
        }

        return [
            'success' => true,
            'items'   => array_map([$this, 'normaliseItem'], $items),
            'error'   => null,
        ];
    }

    // ── Gemini fallback ──────────────────────────────────────────────────────

    /**
     * Extract BOQ items using Google Gemini as a fallback.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file
     * @param  array<string, mixed>  $context
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    private function parseBoqWithGemini(UploadedFile|string $file, array $context = []): array
    {
        $geminiKey     = (string) config('services.gemini.key', '');
        $primaryModel  = (string) config('services.gemini.model', 'gemini-2.0-flash');
        // Ordered list of models to try — use stable/GA model names
        $geminiModels  = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
        ])));

        // ── Check if Gemini API key is configured ───────────────────────────
        if (empty($geminiKey)) {
            Log::warning('QuotationAiService: GEMINI_API_KEY is not configured.');
            return $this->failure('AI service is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        try {
            if ($file instanceof UploadedFile) {
                $absPath  = $file->getRealPath();
                $ext      = strtolower($file->getClientOriginalExtension());
                $filename = $file->getClientOriginalName();
            } else {
                $absPath  = file_exists($file) ? $file : storage_path('app/' . $file);
                $ext      = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
                $filename = basename($absPath);
            }

            // Excel/CSV: convert to plain text so Gemini can parse rows.
            // Gemini does NOT accept text/plain as inline_data — must be sent as a text part.
            if (in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                $csvText = $this->spreadsheetToCsvText($absPath);
                if ($csvText !== null) {
                    // Send the CSV as plain text parts — no inline_data needed.
                    $prompt      = $this->buildGeminiPrompt($context, 'text/plain');
                    $textContent = "BOQ file content (converted from spreadsheet):\n\n" . $csvText;

                    $lastResult = $this->failure('Failed to process file with all available AI models.');

                    foreach ($geminiModels as $attemptModel) {
                        $lastResult = $this->callGeminiGenerateContent(
                            [
                                ['text' => $textContent],
                                ['text' => $prompt],
                            ],
                            $geminiKey,
                            $attemptModel
                        );

                        if ($lastResult['success']) {
                            return $lastResult;
                        }

                        Log::info('QuotationAiService: Gemini model failed, trying next.', [
                            'model' => $attemptModel,
                            'error' => $lastResult['error'],
                        ]);
                    }

                    return $lastResult;
                }

                // PhpSpreadsheet failed — fall back to raw bytes with Excel MIME type.
                Log::warning('QuotationAiService: PhpSpreadsheet failed — sending raw bytes to Gemini.', [
                    'path' => $absPath,
                ]);
                $raw = file_get_contents($absPath);
                if ($raw === false || $raw === '') {
                    return $this->failure('Could not read the uploaded file for AI processing.');
                }
                $bytes = $raw;
                $mime  = $this->mimeForExtension($ext);
            } else {
                $raw = file_get_contents($absPath);
                if ($raw === false || $raw === '') {
                    return $this->failure('Could not read the uploaded file for Gemini processing.');
                }
                $bytes = $raw;
                $mime  = $this->mimeForExtension($ext);
            }

            $prompt = $this->buildGeminiPrompt($context, $mime);

            // Files > 20 MB must go through the Gemini Files API.
            $lastResult = $this->failure('Failed to process file with all available AI models.');

            foreach ($geminiModels as $attemptModel) {
                if (strlen($bytes) > 20 * 1024 * 1024) {
                    $lastResult = $this->geminiViaFilesApi($bytes, $mime, $filename, $prompt, $geminiKey, $attemptModel);
                } else {
                    $lastResult = $this->geminiViaInlineData($bytes, $mime, $prompt, $geminiKey, $attemptModel);
                }

                if ($lastResult['success']) {
                    return $lastResult;
                }

                Log::info('QuotationAiService: Gemini model failed, trying next.', [
                    'model' => $attemptModel,
                    'error' => $lastResult['error'],
                ]);
            }

            return $lastResult;

        } catch (\Throwable $e) {
            Log::error('QuotationAiService: Gemini fallback threw an exception.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return $this->failure('An error occurred while processing your file with AI. Please try again or upload a different file.');
        }
    }

    /**
     * Call Gemini generateContent with the file embedded as base64 inline data.
     *
     * @param  array<string, mixed>  $parts  Gemini "parts" array
     */
    private function callGeminiGenerateContent(array $parts, string $key, string $model): array
    {
        $response = Http::timeout($this->timeout)
            ->withoutVerifying()
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
                [
                    // Permanent system instruction — always applied before BOQ content.
                    'system_instruction' => [
                        'parts' => [['text' => $this->buildSystemInstruction()]],
                    ],
                    'contents' => [['parts' => $parts]],
                    // Do NOT set responseMimeType — not supported by all models (causes HTTP 400 on flash-lite).
                    // Raise output token limit so large BOQs (100+ items) are not truncated mid-JSON.
                    'generationConfig' => [
                        'maxOutputTokens' => 65536,
                        'temperature'     => 0.1,
                    ],
                ]
            );

        if (! $response->successful()) {
            $status = $response->status();
            $errorMsg = "Gemini API returned HTTP {$status}.";

            // Extract actual error message from Gemini's response body
            $geminiError = '';
            $bodyJson = $response->json();
            if (is_array($bodyJson)) {
                $geminiError = (string) data_get($bodyJson, 'error.message', '');
            }
            if ($geminiError === '') {
                $geminiError = substr($response->body(), 0, 300);
            }

            // Provide specific error messages for common Gemini API errors
            $isUnavailable = false;
            if ($status === 400) {
                $errorMsg = "Gemini API error (400): {$geminiError}";
            } elseif ($status === 401) {
                $errorMsg = "Gemini API authentication failed. Please check your GEMINI_API_KEY in .env.";
            } elseif ($status === 403) {
                $errorMsg = "Gemini API access forbidden. Your API key may not have the required permissions.";
                $isUnavailable = true;
            } elseif ($status === 429) {
                $errorMsg = "Gemini API rate limit exceeded. Please try again in a few moments.";
                $isUnavailable = true;
            } elseif ($status === 500 || $status === 503) {
                $errorMsg = "Gemini API server error. Please try again later.";
                $isUnavailable = true;
            }

            Log::error('QuotationAiService: Gemini generateContent failed.', [
                'model'  => $model,
                'status' => $status,
                'body'   => $response->body(),
            ]);

            return $this->failure($errorMsg, $isUnavailable);
        }

        $text = (string) $response->json('candidates.0.content.parts.0.text');

        if ($text === '') {
            return $this->failure('Gemini returned an empty response.');
        }

        // Strip markdown code fences if present.
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $text, $m)) {
            $text = $m[1];
        }

        // Extract the first JSON object or array from the response.
        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $text = $m[0];
        } elseif (preg_match('/\[[\s\S]*\]/s', $text, $m)) {
            $text = $m[0];
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            Log::error('QuotationAiService: Gemini response could not be decoded as JSON.', ['text' => $text]);

            return $this->failure('Gemini returned a non-JSON response.');
        }

        $items = $decoded['items'] ?? (array_is_list($decoded) ? $decoded : []);

        if (! is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            Log::info('QuotationAiService: Gemini returned zero items.');
        }

        return [
            'success' => true,
            'items'   => array_values(array_filter(
                array_map([$this, 'normaliseGeminiItem'], $items),
                fn ($item) => $item !== null,
            )),
            'error'   => null,
        ];
    }

    /** Send file as base64 inline data (≤ 20 MB). */
    private function geminiViaInlineData(string $bytes, string $mime, string $prompt, string $key, string $model): array
    {
        return $this->callGeminiGenerateContent(
            [
                ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode($bytes)]],
                ['text'        => $prompt],
            ],
            $key,
            $model
        );
    }

    /** Upload file via Gemini Files API then call generateContent (> 20 MB). */
    private function geminiViaFilesApi(string $bytes, string $mime, string $filename, string $prompt, string $key, string $model): array
    {
        $boundary = 'GeminiBoundary' . bin2hex(random_bytes(8));
        $metadata = json_encode(['file' => ['display_name' => $filename]], JSON_THROW_ON_ERROR);

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=utf-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime}\r\n\r\n";
        $body .= $bytes . "\r\n";
        $body .= "--{$boundary}--";

        $uploadResponse = Http::timeout(120)
            ->withoutVerifying()
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'multipart',
            ])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$key}");

        if (! $uploadResponse->successful()) {
            $status = $uploadResponse->status();
            $errorMsg = "Gemini Files API upload failed with HTTP {$status}.";

            $isUnavailable = false;
            if ($status === 401) {
                $errorMsg = "Gemini API authentication failed. Please check your GEMINI_API_KEY in .env.";
            } elseif ($status === 413) {
                $errorMsg = "File is too large. Please upload a file smaller than 2GB.";
            } elseif ($status === 429) {
                $errorMsg = "Rate limit exceeded. Please wait a moment and try again.";
                $isUnavailable = true;
            } elseif ($status === 503) {
                $errorMsg = "Gemini API is temporarily unavailable. Please try again later.";
                $isUnavailable = true;
            }

            Log::error('QuotationAiService: Gemini Files API upload failed.', [
                'status' => $status,
                'body'   => $uploadResponse->body(),
            ]);

            return $this->failure($errorMsg, $isUnavailable);
        }

        $fileUri = (string) $uploadResponse->json('file.uri');

        if ($fileUri === '') {
            return $this->failure('Gemini Files API did not return a valid file URI.');
        }

        return $this->callGeminiGenerateContent(
            [
                ['file_data' => ['mime_type' => $mime, 'file_uri' => $fileUri]],
                ['text'      => $prompt],
            ],
            $key,
            $model
        );
    }

    /**
     * Permanent system instruction injected into every Gemini call.
     * This always runs before any BOQ content reaches the model.
     */
    private function buildSystemInstruction(): string
    {
        return trim(<<<'SYSTEM'
You are a BOQ supply-product extraction engine for Qimta.

Qimta prices products and materials only. It does NOT price installation, labor, testing, commissioning, site works, or project execution.

For EVERY BOQ line apply these rules in order:
1. KEEP only real supply products, materials, or equipment that can be purchased from suppliers.
2. REMOVE all installation, testing, commissioning, labor, fixing, support works, and general execution wording.
3. If a line says "Supply and Install" (or "Supply & Install" / "Supply/Install"), extract ONLY the supply product — strip the install part entirely.
4. Extract the core product name and preserve pricing-critical specifications: size, material, rating, voltage, capacity, pressure rating, standard, brand, model, and unit.
5. REJECT: section totals, subtotals, grand totals, mechanical/electrical/civil totals, summary lines, preliminaries, provisional sums, prime cost sums, mobilization, as-built drawings, shop drawings, testing-only lines, commissioning-only lines, supervision lines, and any line with no purchasable product.
6. If one BOQ line contains multiple clearly different products, split them into separate item records.
7. Do NOT extract generic fittings, supports, or accessories unless they are clearly specified with type/size/material.
8. Do NOT invent missing products. If no clear supply product exists, set "rejected": true and provide a rejection_reason.
9. Lines that start with or consist purely of installation/labor/fixing/erection/painting/laying/excavation verbs are always rejected.

Always return ONLY a valid JSON object — no markdown, no code fences, no extra text:
{
  "items": [
    {
      "original_description": "exact text copied from the BOQ line",
      "cleaned_description": "supply product with specs only — no install/labor wording",
      "core_product": "short product type, 2-5 words, e.g. Gate Valve, Cable Tray, HV Panel",
      "specifications": {
        "size": "",
        "material": "",
        "rating": "",
        "voltage": "",
        "capacity": "",
        "pressure_rating": "",
        "standard": "",
        "brand": "",
        "model": ""
      },
      "quantity": 1.0,
      "unit": "pcs",
      "unit_price": 0.0,
      "total_price": 0.0,
      "extraction_type": "supply_only",
      "rejected": false,
      "rejection_reason": null,
      "confidence": 0.95,
      "notes": ""
    }
  ]
}

Field rules:
- original_description: exact BOQ text, never altered.
- cleaned_description: supply product only — no "Supply and Install", no labor clauses.
- core_product: 2-5 word product category name.
- specifications: fill every present spec; leave unused keys as "".
- quantity: number (use 1 if absent).
- unit: unit of measure (pcs, m, m2, m3, kg, L, set, lot, etc.).
- unit_price / total_price: from BOQ if present, otherwise 0.
- extraction_type: "supply_only" | "extracted_from_supply_and_install" | "split_item".
- rejected: true only when the line has NO purchasable product.
- rejection_reason: short reason string when rejected, else null.
- confidence: 0–1 float.
- notes: clarification if needed, else "".
SYSTEM);
    }

    /**
     * Per-BOQ task prompt — provides file context and triggers extraction.
     * The permanent rules live in buildSystemInstruction() and are sent separately.
     */
    private function buildGeminiPrompt(array $context = [], string $mime = ''): string
    {
        $projectName   = $context['project_name'] ?? '';
        $projectStatus = $context['project_status'] ?? '';

        $contextLine = $projectName !== ''
            ? "Project: {$projectName}" . ($projectStatus !== '' ? " (Status: {$projectStatus})" : '')
            : '';

        $isImage = str_starts_with($mime, 'image/');
        $sourceHint = $isImage
            ? 'The input is an image (photo or scan) of a BOQ. Use OCR to read every row carefully before applying the extraction rules.'
            : 'The input is a document or spreadsheet containing a BOQ table.';

        return trim(<<<PROMPT
Extract all supply products from the attached BOQ file using the system rules.
{$contextLine}
{$sourceHint}

Apply every extraction rule from the system instruction to each line and return only the JSON object.
PROMPT);
    }

    /**
     * Normalise a single Gemini supply-extraction item.
     * Returns null for rejected items — callers must filter nulls.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>|null
     */
    private function normaliseGeminiItem(array $raw): ?array
    {
        // Drop rejected lines — they carry no purchasable product.
        if (! empty($raw['rejected'])) {
            return null;
        }

        $quantity   = is_numeric($raw['quantity'] ?? null) ? (float) $raw['quantity'] : 1;
        $unitPrice  = is_numeric($raw['unit_price'] ?? null) ? (float) $raw['unit_price'] : null;
        $totalPrice = is_numeric($raw['total_price'] ?? null) ? (float) $raw['total_price'] : null;

        // Derive unit_price from total_price when not given directly.
        if ($unitPrice === null && $totalPrice !== null && $totalPrice > 0 && $quantity > 0) {
            $unitPrice = $totalPrice / $quantity;
        }
        if ($unitPrice !== null && $unitPrice <= 0) {
            $unitPrice = null;
        }

        $specs = is_array($raw['specifications'] ?? null) ? $raw['specifications'] : [];

        // Prefer cleaned_description (supply-only), fall back to legacy fields.
        $description = (string) ($raw['cleaned_description']
            ?? $raw['product_name']
            ?? $raw['description']
            ?? '');

        return [
            'description'          => $description,
            'quantity'             => $quantity,
            'unit'                 => (string) ($raw['unit'] ?? ''),
            'category'             => (string) ($raw['core_product'] ?? $raw['category'] ?? ''),
            'brand'                => (string) ($specs['brand'] ?? $raw['brand'] ?? ''),
            'status'               => 'pending',
            'engineering_required' => false,
            'unit_price'           => $unitPrice,
            'confidence'           => is_numeric($raw['confidence'] ?? null) ? (float) $raw['confidence'] : null,
            'raw_data'             => [
                'original_description' => (string) ($raw['original_description'] ?? $description),
                'cleaned_description'  => $description,
                'core_product'         => (string) ($raw['core_product'] ?? ''),
                'specifications'       => $specs,
                'extraction_type'      => (string) ($raw['extraction_type'] ?? 'supply_only'),
                'notes'                => (string) ($raw['notes'] ?? ''),
            ],
            'ai_extracted'         => true,
        ];
    }

    /**
     * Normalise a single AI item into the shape expected by the Livewire component.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normaliseItem(array $raw): array
    {
        return [
            'description'          => (string) ($raw['product_name'] ?? ''),
            'quantity'             => is_numeric($raw['quantity'] ?? null) ? (float) $raw['quantity'] : 1,
            'unit'                 => (string) ($raw['unit'] ?? ''),
            'category'             => (string) ($raw['category'] ?? ''),
            'brand'                => (string) ($raw['brand'] ?? ''),
            'status'               => 'pending',
            'engineering_required' => (bool) ($raw['engineering_required'] ?? false),
            'confidence'           => is_numeric($raw['confidence'] ?? null) ? (float) $raw['confidence'] : null,
            'raw_data'             => $raw['raw'] ?? null,
            'ai_extracted'         => true,
        ];
    }

    /**
     * Determine whether a BOQ description represents a purchasable supply product.
     *
     * Returns:
     *   ['keep' => true,  'description' => '...', 'extraction_type' => '...']
     *   ['keep' => false, 'rejection_reason' => '...']
     *
     * @return array<string, mixed>
     */
    private function filterSupplyItem(string $description): array
    {
        $desc = trim($description);
        if ($desc === '') {
            return ['keep' => false, 'rejection_reason' => 'Empty description'];
        }

        // 1. Reject totals / summaries
        if (preg_match(
            '/\b(sub.?total|grand\s*total|section\s*total|total\s*price|total\s*amount|mechanical\s*total|electrical\s*total|civil\s*total|summary)\b/i',
            $desc
        )) {
            return ['keep' => false, 'rejection_reason' => 'Section total or summary line'];
        }

        // 2. Reject pure non-supply lines
        if (preg_match(
            '/^\s*(preliminar|mobiliz|demobiliz|provisional\s+sum|contingenc|p\.?c\.?\s*sum|prime\s*cost)\b/i',
            $desc
        )) {
            return ['keep' => false, 'rejection_reason' => 'Preliminary, provisional sum, or mobilization item'];
        }

        if (preg_match(
            '/\b(supervision|testing\s+and\s+commissioning|commissioning\s+only|labour\s+only|labor\s+only|install(ation)?\s+only|site\s*clearance|excavat(ion)?|backfill(ing)?|compaction|scaffolding|temporary\s+works?|as.built\s+drawing|shop\s+drawing|method\s+statement|performance\s+bond|insurance)\b/i',
            $desc
        )) {
            return ['keep' => false, 'rejection_reason' => 'Non-supply item (labor, site works, or project execution)'];
        }

        // 3. Lines that START with a pure installation / labor verb are rejected
        if (preg_match(
            '/^\s*(install(ing|ation)?|fix(ing)?|erect(ing|ion)?|lay(ing)?|paint(ing)?|plaster(ing)?|demolish(ing)?|remov(ing|al)?|dismantle|commission(ing)?|test(ing)?|supervise|supervision|excavat(ing|ion)?)\b/i',
            $desc
        )) {
            return ['keep' => false, 'rejection_reason' => 'Starts with installation or labor verb'];
        }

        // 4. "Supply and Install" — extract supply product only
        if (preg_match(
            '/\b(supply\s+and\s+install(ation)?|supply\s*[\/&]\s*install(ation)?)\b/i',
            $desc
        )) {
            // Strip the "Supply and Install" prefix
            $cleaned = preg_replace(
                '/\b(supply\s+and\s+install(ation)?|supply\s*[\/&]\s*install(ation)?)\s*(?:of\s*)?/i',
                '',
                $desc
            );
            // Strip trailing install/commission/test clauses
            $cleaned = preg_replace(
                '/,?\s*(includ(ing|e)?\s+)?(install(ation|ing)?|erect(ion|ing)?|fix(ing)?|connect(ion|ing)?|commission(ing)?|test(ing)?|as\s+per\s+spec[a-z]*)\b.*/i',
                '',
                (string) $cleaned
            );
            $cleaned = trim((string) $cleaned, ', ');

            return [
                'keep'           => true,
                'description'    => $cleaned !== '' ? $cleaned : $desc,
                'extraction_type' => 'extracted_from_supply_and_install',
            ];
        }

        // 5. Accept as a supply item
        return [
            'keep'           => true,
            'description'    => $desc,
            'extraction_type' => 'supply_only',
        ];
    }

    /**
     * @return array{success: bool, items: array, error: string}
     */
    private function failure(string $message, bool $serviceUnavailable = false): array
    {
        return ['success' => false, 'items' => [], 'error' => $message, 'service_unavailable' => $serviceUnavailable];
    }

    private function mimeForExtension(string $ext): string
    {
        return match (strtolower($ext)) {
            'pdf'          => 'application/pdf',
            'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'          => 'application/vnd.ms-excel',
            'csv'          => 'text/csv',
            'jpg', 'jpeg'  => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
            default        => 'application/octet-stream',
        };
    }

    /**
     * Read an xlsx, xls, or csv file and return all sheets as CSV text.
     * Returns null on failure.
     */
    private function spreadsheetToCsvText(string $absPath): ?string
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        // CSV: read raw — no library needed.
        if ($ext === 'csv') {
            $content = file_get_contents($absPath);
            return ($content !== false && $content !== '') ? $content : null;
        }

        // Try explicit PhpSpreadsheet readers in priority order.
        $readerTypes = $ext === 'xls' ? ['Xls', 'Xlsx'] : ['Xlsx', 'Xls'];

        foreach ($readerTypes as $readerType) {
            try {
                $reader = IOFactory::createReader($readerType);
                $reader->setReadDataOnly(true);

                if (! $reader->canRead($absPath)) {
                    continue;
                }

                $spreadsheet   = $reader->load($absPath);
                $output        = '';

                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    $highestRow    = $sheet->getHighestDataRow();
                    $highestColumn = $sheet->getHighestDataColumn();

                    if ($highestRow < 1) {
                        continue;
                    }

                    $output .= 'Sheet: ' . $sheet->getTitle() . "\n";

                    $grid = $sheet->rangeToArray(
                        "A1:{$highestColumn}{$highestRow}",
                        null,
                        true,   // calculateFormulas
                        true,   // formatData
                        false   // returnCellRef (false = numeric keys)
                    );

                    foreach ($grid as $rowCells) {
                        // Strip trailing empty cells
                        while (count($rowCells) > 0 && trim((string) end($rowCells)) === '') {
                            array_pop($rowCells);
                        }

                        if (count($rowCells) > 0) {
                            $output .= implode(',', array_map(function (mixed $v): string {
                                $v = (string) $v;
                                return (str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n"))
                                    ? '"' . str_replace('"', '""', $v) . '"'
                                    : $v;
                            }, $rowCells)) . "\n";
                        }
                    }

                    $output .= "\n";
                }

                if ($output !== '') {
                    return $output;
                }

            } catch (\Throwable $e) {
                Log::warning('QuotationAiService: spreadsheetToCsvText reader failed, trying next.', [
                    'reader'  => $readerType,
                    'path'    => $absPath,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }
}
