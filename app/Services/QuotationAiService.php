<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
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
     * Send a BOQ file to the external AI endpoint and return extracted items.
     * Falls back to Gemini if the primary API fails.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file  Uploaded file or stored path
     * @param  array<string, mixed>  $context  Extra context (quotation_id, project_name, project_status)
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

        $result = $this->callPrimaryApi($file, $context);

        if ($result['success']) {
            return $result;
        }

        // ── Gemini fallback ──────────────────────────────────────────────────
        $geminiKey = (string) config('services.gemini.key', '');
        if (empty($geminiKey)) {
            return $result; // No fallback configured — return the original failure.
        }

        Log::info('QuotationAiService: Primary API failed — falling back to Gemini.', [
            'primary_error' => $result['error'],
        ]);

        $geminiResult = $this->parseBoqWithGemini($file, $context);

        if ($geminiResult['success']) {
            return $geminiResult;
        }

        // Both failed — return the most specific error message.
        Log::error('QuotationAiService: Both primary API and Gemini fallback failed.', [
            'primary_error' => $result['error'],
            'gemini_error'  => $geminiResult['error'],
        ]);

        return $this->failure($geminiResult['error'] ?? $result['error']);
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
            $request = Http::timeout($this->timeout)->asMultipart();

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

            return $this->failure('AI service timed out or is unavailable. Please try again later.');
        } catch (\Throwable $e) {
            Log::error('QuotationAiService: Unexpected error calling AI endpoint.', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('An unexpected error occurred while contacting the AI service.');
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
        $primaryModel  = (string) config('services.gemini.model', 'gemini-2.5-flash');
        // Ordered list of models to try — confirmed working for new API keys
        $geminiModels  = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-flash-latest',
        ])));

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

            $prompt = $this->buildGeminiPrompt($context, $mime ?? 'application/octet-stream');

            // Excel/CSV: convert to plain text so Gemini can parse rows.
            // If PhpSpreadsheet fails for any reason, fall back to raw bytes
            // (Gemini Files API accepts xlsx/xls via their native MIME types).
            if (in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                $csvText = $this->spreadsheetToCsvText($absPath);
                if ($csvText !== null) {
                    $bytes = $csvText;
                    $mime  = 'text/plain';
                } else {
                    Log::warning('QuotationAiService: PhpSpreadsheet failed — sending raw bytes to Gemini.', [
                        'path' => $absPath,
                    ]);
                    $raw = file_get_contents($absPath);
                    if ($raw === false || $raw === '') {
                        return $this->failure('Could not read the uploaded file for AI processing.');
                    }
                    $bytes = $raw;
                    $mime  = $this->mimeForExtension($ext);
                }
            } else {
                $raw = file_get_contents($absPath);
                if ($raw === false || $raw === '') {
                    return $this->failure('Could not read the uploaded file for Gemini processing.');
                }
                $bytes = $raw;
                $mime  = $this->mimeForExtension($ext);
            }

            // Files > 20 MB must go through the Gemini Files API.
            $lastResult = $this->failure('Gemini AI fallback encountered an unexpected error.');

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
            ]);

            return $this->failure('Gemini AI fallback encountered an unexpected error.');
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
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
                [
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
            Log::error('QuotationAiService: Gemini generateContent failed.', [
                'model'  => $model,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->failure("Gemini returned HTTP {$response->status()}.");
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
            'items'   => array_map([$this, 'normaliseGeminiItem'], $items),
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
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'multipart',
            ])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$key}");

        if (! $uploadResponse->successful()) {
            Log::error('QuotationAiService: Gemini Files API upload failed.', [
                'status' => $uploadResponse->status(),
                'body'   => $uploadResponse->body(),
            ]);

            return $this->failure("Gemini file upload failed (HTTP {$uploadResponse->status()}).");
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

    /** Build the Gemini extraction prompt. */
    private function buildGeminiPrompt(array $context = [], string $mime = ''): string
    {
        $projectName   = $context['project_name'] ?? '';
        $projectStatus = $context['project_status'] ?? '';

        $contextLine = $projectName !== ''
            ? "Project: {$projectName}" . ($projectStatus !== '' ? " (Status: {$projectStatus})" : '')
            : '';

        $isImage = str_starts_with($mime, 'image/');
        $sourceHint = $isImage
            ? 'The input is an image (photo or scan) of a BOQ table or list. Use OCR to read every row carefully.'
            : 'The input is a document file containing a BOQ table.';

        return trim(<<<PROMPT
You are a BOQ (Bill of Quantities) extraction assistant.
{$contextLine}
{$sourceHint}

Extract every line item from the provided document and return ONLY a valid JSON object with this exact structure:
{
  "items": [
    {
      "product_name": "Full item description",
      "quantity": 1.0,
      "unit": "pcs",
      "unit_price": 0.0,
      "total_price": 0.0,
      "category": "Category name or empty string",
      "brand": "Brand name or empty string",
      "engineering_required": false,
      "confidence": 0.95
    }
  ]
}

Rules:
- Include EVERY line item / product found in the document — do not skip any row.
- "quantity" must be a number (use 1 if not specified).
- "unit" is the unit of measure (pcs, m, m2, m3, kg, L, set, etc.).
- "unit_price" is the unit price of the item from the document. If a column contains prices, you MUST read the value for every row. If only a total price is given and no unit price, calculate unit_price = total_price / quantity. If no price is available for a row, use 0.
- "total_price" is the total/line amount (quantity × unit_price). If not explicitly shown, calculate it. Use 0 if not available.
- "engineering_required" is true only if the item clearly needs engineering work.
- "confidence" is a number between 0 and 1 reflecting extraction certainty.
- Do NOT merge section headers or floor labels as items; extract only actual products/services.
- Return ONLY the JSON object — no markdown, no code fences, no extra text.
PROMPT);
    }

    /**
     * Normalise a single Gemini item into the shape expected by the Livewire component.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normaliseGeminiItem(array $raw): array
    {
        $quantity   = is_numeric($raw['quantity'] ?? null) ? (float) $raw['quantity'] : 1;
        $unitPrice  = is_numeric($raw['unit_price'] ?? null) ? (float) $raw['unit_price'] : null;
        $totalPrice = is_numeric($raw['total_price'] ?? null) ? (float) $raw['total_price'] : null;

        // Derive unit_price from total_price if not given directly
        if ($unitPrice === null && $totalPrice !== null && $totalPrice > 0 && $quantity > 0) {
            $unitPrice = $totalPrice / $quantity;
        }

        // Treat 0 as unpriced
        if ($unitPrice !== null && $unitPrice <= 0) {
            $unitPrice = null;
        }

        return [
            'description'          => (string) ($raw['product_name'] ?? $raw['description'] ?? ''),
            'quantity'             => $quantity,
            'unit'                 => (string) ($raw['unit'] ?? ''),
            'category'             => (string) ($raw['category'] ?? ''),
            'brand'                => (string) ($raw['brand'] ?? ''),
            'status'               => 'pending',
            'engineering_required' => (bool) ($raw['engineering_required'] ?? false),
            'unit_price'           => $unitPrice,
            'confidence'           => is_numeric($raw['confidence'] ?? null) ? (float) $raw['confidence'] : null,
            'raw_data'             => null,
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
     * @return array{success: bool, items: array, error: string}
     */
    private function failure(string $message): array
    {
        return ['success' => false, 'items' => [], 'error' => $message];
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
