<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuotationAiService
{
    private string $baseUrl;
    private string $parseEndpoint;
    private string $apiKey;
    private int $timeout;
    private bool $testMode;
    private BoqCleaningService $boqCleaner;

    public function __construct(BoqCleaningService $boqCleaner)
    {
        $this->boqCleaner = $boqCleaner;
        $this->baseUrl = rtrim((string) config('services.ai_quotation.base_url', ''), '/');
        $this->parseEndpoint = ltrim((string) config('services.ai_quotation.parse_endpoint', 'parse'), '/');
        $this->apiKey = (string) config('services.ai_quotation.api_key', '');
        $this->timeout = (int) config('services.ai_quotation.timeout', 300);
        $this->testMode = (bool) config('services.ai_quotation.test_mode', false);
    }

    public function parseBoq(UploadedFile|string $file, array $context = []): array
    {
        if ($this->testMode) {
            return $this->mockResponse();
        }

        [$absPath, $ext] = $this->resolveFile($file);

        if (! is_file($absPath)) {
            return $this->failure('File not found or not readable.');
        }

        $fileHash     = hash_file('sha256', $absPath);
        $cacheKey     = 'boq_analysis_' . $fileHash;
        $forceRefresh = (bool) ($context['force_refresh'] ?? false);

        if (! $forceRefresh && ($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        // Clear stale cached result when force-refreshing
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        if (in_array($ext, ['xlsx', 'xlsm', 'xlsb', 'xls', 'csv'], true)) {
            // Read Excel locally first. Fallback to DeepSeek if local parse failed or found 0 items.
            $result = $this->parseSpreadsheetDirect($absPath);

            if (! $result['success'] || empty($result['items'])) {
                $deepSeekResult = $this->parseBoqWithDeepSeek($file, $context);
                // Only use DeepSeek result if it actually found items
                if ($deepSeekResult['success'] && ! empty($deepSeekResult['items'])) {
                    $result = $deepSeekResult;
                } elseif (! $result['success']) {
                    $result = $deepSeekResult;
                }
                // else: keep local result (even with 0 items) if DeepSeek also failed
            }
        } elseif (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'], true)) {
            $result = $this->parseBoqWithDeepSeek($file, $context);
        } else {
            return $this->failure('Unsupported file type. Please upload Excel, CSV, PDF, or image file.');
        }

        if ($result['success'] && ! empty($result['items'])) {
            $cleaning = $this->boqCleaner->process($result['items']);
            $result['items'] = $cleaning['accepted'] ?? $result['items'];
            $result['rejected'] = array_merge($result['rejected'] ?? [], $cleaning['rejected'] ?? []);
        }

        // Only cache when we actually extracted items; empty results should be re-tried.
        if ($result['success'] && ! empty($result['items'])) {
            Cache::put($cacheKey, $result, now()->addDays(30));
        }

        return $result;
    }

    private function resolveFile(UploadedFile|string $file): array
    {
        if ($file instanceof UploadedFile) {
            return [(string) $file->getRealPath(), strtolower($file->getClientOriginalExtension())];
        }

        $absPath = is_file($file) ? $file : storage_path('app/' . ltrim($file, '/'));
        return [$absPath, strtolower(pathinfo($absPath, PATHINFO_EXTENSION))];
    }

    private function parseSpreadsheetDirect(string $absPath): array
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(180);

        try {
            $sheets = $this->spreadsheetToGrids($absPath);

            if (empty($sheets)) {
                return $this->failure('Could not read the Excel file.');
            }

            $items = [];
            $rejected = [];

            foreach ($sheets as $sheet) {
                $grid = $sheet['grid'];
                if (count($grid) < 2) {
                    continue;
                }

                $header = $this->detectHeader($grid);

                if ($header === null || ! isset($header['map']['description'])) {
                    Log::warning('QuotationAiService: header not detected in sheet.', ['sheet' => $sheet['name']]);
                    continue;
                }

                $map = $header['map'];
                $start = $header['row'] + 1;

                for ($r = $start; $r < count($grid); $r++) {
                    $row = $grid[$r];
                    $description = $this->cell($row, $map['description'] ?? null);
                    $itemNo      = $this->cell($row, $map['item_code'] ?? null);
                    $unit        = $this->cell($row, $map['unit'] ?? null);
                    $quantity    = $this->number($this->cell($row, $map['quantity'] ?? null));
                    $unitPrice   = $this->number($this->cell($row, $map['unit_price'] ?? null));
                    $totalPrice  = $this->number($this->cell($row, $map['total_price'] ?? null));

                    if ($description === '') {
                        continue;
                    }

                    // Skip floor breakdown sub-rows: Ref. is "-" meaning it's a per-floor
                    // quantity split of the parent item, not a standalone product.
                    if ($itemNo === '-' || $this->isFloorBreakdownRow($itemNo, $description)) {
                        continue;
                    }

                    if ($this->isTotalOrHeaderRow($description)) {
                        continue;
                    }

                    if ($quantity === null || $quantity <= 0) {
                        $rejected[] = $this->rejectedRow($description, $unit, $quantity, 'Missing quantity', $itemNo, $sheet['name']);
                        continue;
                    }

                    if ($unitPrice === null && $totalPrice !== null && $quantity > 0) {
                        $unitPrice = $totalPrice / $quantity;
                    }

                    $supply = $this->boqCleaner->filterItem($description);

                    if (! ($supply['keep'] ?? false)) {
                        $rejected[] = $this->rejectedRow(
                            $description,
                            $unit,
                            $quantity,
                            (string) ($supply['rejection_reason'] ?? 'Rejected by cleaner'),
                            $itemNo,
                            $sheet['name']
                        );
                        continue;
                    }

                    $finalDescription = (string) ($supply['description'] ?? $description);

                    $items[] = [
                        'description'          => $finalDescription,
                        'quantity'             => $quantity,
                        'unit'                 => $unit,
                        'category'             => $sheet['name'],
                        'brand'                => '',
                        'status'               => 'pending',
                        'engineering_required' => $this->boqCleaner->requiresEngineering($finalDescription),
                        'unit_price'           => $unitPrice,
                        'confidence'           => 0.95,
                        'needs_review'         => false,
                        'raw_data'             => [
                            'sheet'                => $sheet['name'],
                            'row_number'           => $r + 1,
                            'source_item_no'       => $itemNo,
                            'original_description' => $description,
                        'cleaned_description'  => $finalDescription,
                            'extraction_type'      => (string) ($supply['extraction_type'] ?? 'supply_only'),
                        ],
                        'ai_extracted' => true,
                    ];
                }
            }

            if (empty($items)) {
                return $this->failure('No BOQ supply items could be extracted from the Excel file.');
            }

            return [
                'success'  => true,
                'items'    => $items,
                'rejected' => $rejected,
                'error'    => null,
            ];
        } catch (\Throwable $e) {
            Log::error('QuotationAiService: parseSpreadsheetDirect failed.', [
                'path'    => $absPath,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return $this->failure('Failed to read the Excel file: ' . $e->getMessage());
        }
    }

    private function spreadsheetToGrids(string $absPath): array
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            $rows = [];
            $handle = fopen($absPath, 'rb');
            if ($handle !== false) {
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $this->trimTrailingEmptyCells($row);
                }
                fclose($handle);
            }
            return [['name' => 'Sheet1', 'grid' => $rows]];
        }

        $reader = IOFactory::createReader($ext === 'xls' ? 'Xls' : 'Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($absPath);
        $sheets = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $highestRow         = $sheet->getHighestDataRow();
            $highestColumn      = $sheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            // BOQ data is normally in first 8-20 columns.
            // Files with heavy formatting/images can report 300+ columns â€” limit to 30.
            $maxColumnIndex = min($highestColumnIndex, 30);

            $grid = [];
            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData  = [];
                $hasValue = false;

                for ($col = 1; $col <= $maxColumnIndex; $col++) {
                    $value = $sheet->getCell([$col, $row])->getValue();
                    $value = ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText)
                        ? $value->getPlainText()
                        : (string) ($value ?? '');
                    $value = trim($value);
                    if ($value !== null && $value !== '') {
                        $hasValue = true;
                    }
                    $rowData[] = $value;
                }

                $rowData = $this->trimTrailingEmptyCells($rowData);
                if ($hasValue) {
                    $grid[] = $rowData;
                }
            }

            if (! empty($grid)) {
                $sheets[] = ['name' => $sheet->getTitle(), 'grid' => $grid];
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $sheets;
    }

    private function detectHeader(array $grid): ?array
    {
        $keywords = [
            'item_code'   => ['Ø±Ù‚Ù… Ø§Ù„Ø¨Ù†Ø¯', 'item no', 'item number', 'ref.', 'ref', 'item', 'no.', 'no', '#', 'ÙƒÙˆØ¯'],
            'description' => ['ÙˆØµÙ Ø§Ù„Ø¨Ù†Ø¯', 'scope of works', 'scope of work', 'scope', 'item description', 'description of works', 'description', 'Ø¨Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©', 'Ø§Ù„ÙˆØµÙ', 'Ø§Ù„Ø¨Ù†Ø¯', 'Ø¨ÙŠØ§Ù†'],
            'unit'        => ['Ø§Ù„ÙˆØ­Ø¯Ø©', 'unit', 'uom', 'u/m'],
            'quantity'    => ['Ø§Ù„ÙƒÙ…ÙŠØ©', 'qty', 'quantity', 'q.ty'],
            'unit_price'  => ['Ø³Ø¹Ø± Ø§Ù„ÙˆØ­Ø¯Ø©', 'u/price', 'unit price', 'unit rate', 'rate', 'price'],
            'total_price' => ['Ø§Ù„Ø³Ø¹Ø± Ø§Ù„Ø¥Ø¬Ù…Ø§Ù„ÙŠ', 'total amount', 'total price', 'amount', 'Ø§Ù„Ø¥Ø¬Ù…Ø§Ù„ÙŠ'],
        ];

        $best      = null;
        $bestScore = 0;

        for ($r = 0; $r < min(30, count($grid)); $r++) {
            $map   = [];
            $score = 0;

            foreach ($grid[$r] as $c => $value) {
                $cell = mb_strtolower(trim((string) $value));
                if ($cell === '') {
                    continue;
                }

                foreach ($keywords as $field => $list) {
                    foreach ($list as $kw) {
                        if (str_contains($cell, mb_strtolower($kw))) {
                            if (! isset($map[$field])) {
                                $map[$field] = $c;
                                $score++;
                            }
                            break 2;
                        }
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = ['row' => $r, 'map' => $map];
            }
        }

        return $bestScore >= 3 ? $best : null;
    }

    private function cell(array $row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }
        return trim((string) ($row[$index] ?? ''));
    }

    private function number(string $value): ?float
    {
        $value = trim(str_replace([',', 'SAR', 'Ø±.Ø³'], '', $value));
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function trimTrailingEmptyCells(array $row): array
    {
        while (! empty($row) && trim((string) end($row)) === '') {
            array_pop($row);
        }
        return array_values($row);
    }

    private function isTotalOrHeaderRow(string $description): bool
    {
        $d = mb_strtolower(trim($description));

        if ($d === '' || preg_match('/^[\d\.\-\/\s]+$/u', $d)) {
            return true;
        }

        // Section totals / summaries
        if (preg_match('/(Ø§Ø¬Ù…Ø§Ù„ÙŠ|Ø¥Ø¬Ù…Ø§Ù„ÙŠ|Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹|grand\s*total|sub\s*total|total\s*amount|total\s*price)/iu', $d)) {
            return true;
        }

        // Pure floor / level labels used as section headers
        if (preg_match('/^(gf|g\.f\.?|ground\s*floor|basement|rooftop|mezzanine|podium|floor\s*\d+|\d+(st|nd|rd|th)\s*floor)\s*$/iu', $d)) {
            return true;
        }

        // Division / section header lines (e.g. "Division - 02: Wood Works")
        if (preg_match('/^division\s*([-:]|\d)/iu', $d)) {
            return true;
        }

        return false;
    }

    private function isFloorBreakdownRow(string $itemNo, string $description): bool
    {
        // A row whose Ref column is empty or a dash is a sub-breakdown row (per-floor split).
        $ref = trim($itemNo);
        if ($ref === '' || $ref === '-') {
            // Confirm by checking the description is a location/floor label
            $d = mb_strtolower(trim($description));
            return (bool) preg_match(
                '/^(gf|g\.f\.?|ground\s*floor|basement|rooftop|mezzanine|podium|floor\s*\d+|\d+(st|nd|rd|th)\s*floor|\d+f)\s*$/iu',
                $d
            );
        }
        return false;
    }

    private function rejectedRow(string $description, string $unit, ?float $quantity, string $reason, string $itemNo = '', string $sheet = ''): array
    {
        return [
            'description'          => $description,
            'quantity'             => $quantity ?? 0,
            'unit'                 => $unit,
            'category'             => $sheet,
            'brand'                => '',
            'status'               => 'rejected',
            'engineering_required' => false,
            'unit_price'           => null,
            'confidence'           => 0.0,
            'raw_data'             => [
                'sheet'                => $sheet,
                'source_item_no'       => $itemNo,
                'original_description' => $description,
                'extraction_type'      => 'rejected',
                'rejection_reason'     => $reason,
            ],
            'ai_extracted' => true,
        ];
    }

    private function parseBoqWithDeepSeek(UploadedFile|string $file, array $context = []): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            return $this->failure('AI service is not configured. Please set DEEPSEEK_API_KEY in .env file.');
        }

        [$absPath, $ext] = $this->resolveFile($file);
        $mime = $this->mimeForExtension($ext);

        try {
            // â”€â”€ Spreadsheets â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if (in_array($ext, ['xlsx', 'xlsm', 'xlsb', 'xls', 'csv'], true)) {
                $text = $this->spreadsheetToCompactCsvText($absPath);
                if ($text === null || trim($text) === '') {
                    return $this->failure('Could not convert spreadsheet to readable text.');
                }
                $text        = $this->sanitizeUtf8(mb_substr($text, 0, 180000));
                $userContent = "BOQ spreadsheet converted to CSV:\n\n" . $text . "\n\n" . $this->buildDeepSeekPrompt($context, 'text/plain');
                return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
            }

            // â”€â”€ PDF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if ($ext === 'pdf') {
                $extracted = $this->extractPdfText($absPath);
                if ($extracted !== null && mb_strlen(trim($extracted)) > 50) {
                    $extracted   = $this->sanitizeUtf8(mb_substr($extracted, 0, 180000));
                    $userContent = "BOQ PDF extracted text:\n\n" . $extracted . "\n\n" . $this->buildDeepSeekPrompt($context, 'application/pdf');
                    return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
                }
                return $this->failure('Could not extract text from the PDF. Please make sure it is a text-based PDF (not a scanned image), or convert it to Excel or CSV.');
            }

            // ── Images ──────────────────────────────────────────────────────────────
            // DeepSeek cannot receive images. A vision-capable AI processes the image
            // directly (no OCR) — configure GROQ_API_KEY or GEMINI_API_KEY in .env.
            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif', 'heic', 'heif'];
            if (in_array($ext, $imageExts, true)) {
                $bytes = (string) file_get_contents($absPath);
                return $this->callDeepSeekVision($bytes, $mime, $context);
            }

            return $this->failure('Unsupported file type. Please upload an Excel, CSV, PDF, or image file.');
        } catch (\Throwable $e) {
            Log::error('QuotationAiService: DeepSeek fallback failed.', ['message' => $e->getMessage()]);
            return $this->failure('An error occurred while processing the file with AI: ' . $e->getMessage());
        }
    }

    private function deepSeekModel(): string
    {
        return (string) config('services.deepseek.model', 'deepseek-chat');
    }

    /**
     * Remove or replace any non-UTF-8 / malformed byte sequences so json_encode never fails.
     */
    private function sanitizeUtf8(string $text): string
    {
        // Replace invalid byte sequences with the UTF-8 replacement character
        $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Strip any remaining non-printable control characters (except tab/newline/CR)
        $clean = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        return $clean;
    }

    /**
     * Extract plain text from a PDF using smalot/pdfparser (pure PHP, no binary required).
     */
    private function extractPdfText(string $absPath): ?string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($absPath);
            $text   = $pdf->getText();
            $text   = (string) preg_replace('/[ \t]{2,}/', ' ', $text);
            $text   = (string) preg_replace('/\n{3,}/', "\n\n", $text);
            $text   = trim($text);
            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('QuotationAiService: PDF parsing failed.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send a vision request to a vision-capable AI model.
     * Priority order (first configured key wins):
     *   1. GEMINI_API_KEY  → Google Gemini  (free: https://aistudio.google.com/apikey)
     *   2. GROQ_API_KEY    → Groq           (free: https://console.groq.com)
     *   3. VISION_API_KEY  → any OpenAI-compatible endpoint (OpenAI, OpenRouter …)
     * DeepSeek does NOT support image inputs.
     */
    private function callDeepSeekVision(string $bytes, string $mime, array $context): array
    {
        // ── 1. Google Gemini ─────────────────────────────────────────────────────
        $geminiKey = (string) config('services.gemini.key', '');
        if ($geminiKey !== '') {
            $visionKey     = $geminiKey;
            $visionBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
            $visionModel   = (string) config('services.gemini.model', 'gemini-2.0-flash');
        }
        // ── 2. Groq (free tier) ──────────────────────────────────────────────────
        elseif ((string) config('services.groq.key', '') !== '') {
            $visionKey     = (string) config('services.groq.key');
            $visionBaseUrl = 'https://api.groq.com/openai/v1';
            $visionModel   = (string) config('services.groq.model', 'meta-llama/llama-4-scout-17b-16e-instruct');
        }
        // ── 3. Generic VISION_API_KEY (OpenAI, OpenRouter, …) ───────────────────
        else {
            $visionKey     = (string) config('services.vision.key', '');
            $visionBaseUrl = rtrim((string) config('services.vision.base_url', 'https://openrouter.ai/api/v1'), '/');
            $visionModel   = (string) config('services.vision.model', 'google/gemini-flash-1.5-8b');
        }

        if ($visionKey === '') {
            return $this->failure(
                'Image processing requires a vision-capable AI. Add one of the following to your .env: ' .
                'GEMINI_API_KEY (free at https://aistudio.google.com/apikey) or ' .
                'GROQ_API_KEY (free at https://console.groq.com).'
            );
        }

        $dataUrl     = "data:{$mime};base64," . base64_encode($bytes);
        $prompt      = $this->buildDeepSeekPrompt($context, $mime);
        $userContent = [
            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
            ['type' => 'text',      'text'       => $prompt],
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $visionKey,
                    'HTTP-Referer'  => config('app.url', 'https://qimta.sa'),
                    'X-Title'       => 'Qimta Platform',
                ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLVERSION   => CURL_SSLVERSION_TLSv1_2,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->post($visionBaseUrl . '/chat/completions', [
                    'model'       => $visionModel,
                    'messages'    => [
                        ['role' => 'system', 'content' => $this->buildSystemInstruction()],
                        ['role' => 'user',   'content' => $userContent],
                    ],
                    'max_tokens'  => 65536,
                    'temperature' => 0.1,
                ]);
        } catch (ConnectionException $e) {
            return $this->failure('Vision API connection timeout or unavailable.', true);
        }

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());
            Log::error('QuotationAiService: Vision API HTTP error.', [
                'model'    => $visionModel,
                'base_url' => $visionBaseUrl,
                'status'   => $response->status(),
                'message'  => $message,
            ]);
            return $this->failure("Vision API returned HTTP {$response->status()}: {$message}", in_array($response->status(), [429, 500, 503], true));
        }

        return $this->parseDeepSeekJsonResponse((string) $response->json('choices.0.message.content'));
    }

    private function callDeepSeekChat(string $userContent, string $key, string $model): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(30)
                ->withoutVerifying()
                ->withHeaders(['Authorization' => 'Bearer ' . $key])
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLVERSION   => CURL_SSLVERSION_TLSv1_2,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->post('https://api.deepseek.com/chat/completions', [
                    'model'       => $model,
                    'messages'    => [
                        ['role' => 'system', 'content' => $this->buildSystemInstruction()],
                        ['role' => 'user',   'content' => $userContent],
                    ],
                    'max_tokens'  => 65536,
                    'temperature' => 0.1,
                    'user'        => 'Qimta_Platform',
                ]);
        } catch (ConnectionException $e) {
            return $this->failure('DeepSeek connection timeout or unavailable.', true);
        }

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());
            Log::error('QuotationAiService: DeepSeek HTTP error.', [
                'model'   => $model,
                'status'  => $response->status(),
                'message' => $message,
            ]);
            if ($response->status() === 401) {
                return $this->failure('DeepSeek API key is invalid or unauthorised. Please update DEEPSEEK_API_KEY in .env.', false);
            }
            return $this->failure("DeepSeek API returned HTTP {$response->status()}: {$message}", in_array($response->status(), [429, 500, 503], true));
        }

        $text = (string) $response->json('choices.0.message.content');
        return $this->parseDeepSeekJsonResponse($text);
    }

    /**
     * Shared JSON response parser used by both chat and vision paths.
     */
    private function parseDeepSeekJsonResponse(string $text): array
    {
        if ($text === '') {
            return $this->failure('DeepSeek returned an empty response.');
        }

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $text, $m)) {
            $text = $m[1];
        }
        if (preg_match('/\{[\s\S]*\}/s', $text, $m)) {
            $text = $m[0];
        }

        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            Log::error('QuotationAiService: DeepSeek non-JSON response.', ['text' => mb_substr($text, 0, 1000)]);
            return $this->failure('DeepSeek returned a non-JSON response.');
        }

        $rawItems = $decoded['items'] ?? (array_is_list($decoded) ? $decoded : []);
        $items    = [];
        $rejected = [];

        foreach ($rawItems as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            $item = $this->normaliseAiItem($raw);
            if ($item === null) {
                continue;
            }
            $check = $this->boqCleaner->filterItem((string) $item['description']);
            if ($check['keep'] ?? false) {
                $items[] = $item;
            } else {
                $item['status']                       = 'rejected';
                $item['raw_data']['rejection_reason'] = $check['rejection_reason'] ?? 'Rejected by cleaner';
                $rejected[]                           = $item;
            }
        }

        if (empty($items)) {
            Log::info('QuotationAiService: DeepSeek returned 0 accepted items.', [
                'raw_items_count' => count($rawItems),
                'rejected_count'  => count($rejected),
                'first_rejected'  => ! empty($rejected) ? ($rejected[0]['description'] ?? '') : '',
            ]);
        }

        return ['success' => true, 'items' => $items, 'rejected' => $rejected, 'error' => null];
    }

    private function spreadsheetToCompactCsvText(string $absPath): ?string
    {
        $sheets = $this->spreadsheetToGrids($absPath);
        if (empty($sheets)) {
            return null;
        }

        $output = '';
        foreach ($sheets as $sheet) {
            $output .= 'Sheet: ' . $sheet['name'] . "\n";
            foreach ($sheet['grid'] as $row) {
                $row = $this->trimTrailingEmptyCells($row);
                if (empty($row)) {
                    continue;
                }
                $output .= implode(',', array_map([$this, 'csvEscape'], $row)) . "\n";
            }
            $output .= "\n";
        }

        return $output;
    }

    private function csvEscape(mixed $value): string
    {
        $value = (string) $value;
        return str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")
            ? '"' . str_replace('"', '""', $value) . '"'
            : $value;
    }

    private function normaliseAiItem(array $raw): ?array
    {
        if (! empty($raw['rejected'])) {
            return null;
        }

        $description = trim((string) ($raw['cleaned_description'] ?? $raw['product_name'] ?? $raw['description'] ?? ''));
        if ($description === '' || preg_match('/^[\d\.\-\/\s]+$/u', $description)) {
            return null;
        }

        $quantity   = is_numeric($raw['quantity'] ?? null)    ? (float) $raw['quantity']    : 1;
        $unitPrice  = is_numeric($raw['unit_price'] ?? null)  ? (float) $raw['unit_price']  : null;
        $totalPrice = is_numeric($raw['total_price'] ?? null) ? (float) $raw['total_price'] : null;

        if ($unitPrice === null && $totalPrice !== null && $quantity > 0) {
            $unitPrice = $totalPrice / $quantity;
        }

        $specs = is_array($raw['specifications'] ?? null) ? $raw['specifications'] : [];

        return [
            'description'          => $description,
            'quantity'             => $quantity,
            'unit'                 => (string) ($raw['unit'] ?? ''),
            'category'             => (string) ($raw['core_product'] ?? $raw['category'] ?? ''),
            'brand'                => (string) ($specs['brand'] ?? $raw['brand'] ?? ''),
            'status'               => 'pending',
            'engineering_required' => $this->boqCleaner->requiresEngineering($description),
            'unit_price'           => $unitPrice,
            'confidence'           => is_numeric($raw['confidence'] ?? null) ? (float) $raw['confidence'] : null,
            'needs_review'         => ! empty($raw['needs_review']),
            'raw_data'             => [
                'source_item_no'       => (string) ($raw['source_item_no'] ?? ''),
                'original_description' => (string) ($raw['original_description'] ?? $description),
                'cleaned_description'  => $description,
                'core_product'         => (string) ($raw['core_product'] ?? ''),
                'specifications'       => $specs,
                'extraction_type'      => (string) ($raw['extraction_type'] ?? 'supply_only'),
                'note'                 => (string) ($raw['note'] ?? $raw['notes'] ?? ''),
            ],
            'ai_extracted' => true,
        ];
    }

    private function buildSystemInstruction(): string
    {
        return <<<'TXT'
You are a strict BOQ Supply Product Extraction Engine.
Extract only purchasable physical supply products. Reject totals, headings, general requirements, installation-only, testing, commissioning, supervision, mobilization, civil works, labor, and vague rows.
For Supply & Install rows, keep only the supply product and remove install/labor wording.
Return only valid JSON: {"items":[{"source_item_no":null,"original_description":"","cleaned_description":"","core_product":"","specifications":{},"quantity":0,"unit":"","unit_price":0,"total_price":0,"extraction_type":"supply_only","needs_review":false,"note":"","rejected":false,"rejection_reason":null,"confidence":0.95}]}
TXT;
    }

    private function buildDeepSeekPrompt(array $context = [], string $mime = ''): string
    {
        $projectName   = (string) ($context['project_name'] ?? '');
        $projectStatus = (string) ($context['project_status'] ?? '');
        $contextLine   = $projectName !== ''
            ? "Project: {$projectName}" . ($projectStatus !== '' ? " ({$projectStatus})" : '')
            : '';
        $sourceHint = str_starts_with($mime, 'image/')
            ? 'The input is an image of a BOQ. OCR carefully.'
            : 'The input is a BOQ document/spreadsheet.';

        return trim(<<<PROMPT
You are a procurement specialist. Your task is to extract ONLY tangible, physical supply products from this Bill of Quantities (BOQ).

{$contextLine}
{$sourceHint}

STRICT RULES â€” include ONLY items that are ALL of the following:
1. A physical, tangible product that can be ordered from a supplier and delivered to site.
2. Has a real quantity (not 0 or "varies" with no meaning).
3. Has enough description to identify what the product is.

EXCLUDE all of the following (do NOT include them):
- Labor, installation, or workmanship items (e.g. "install", "fix", "erect", "dismantle", "painting", "cleaning service")
- Project management, overhead, or profit items
- Insurance, bonds, liability items (e.g. "bodily injury", "third party liability", "CAR insurance")
- Shop drawings, as-built drawings, method statements, documentation
- Preliminary items, mobilisation, demobilisation, provisional sums
- Housekeeping, site clearance, waste removal
- Floor/level labels (e.g. "GF Floor", "Level 3") â€” these are headers, not products
- Pure dimension specs without a product noun (e.g. "Dia 32 mm", "25 mm") â€” reject unless a product name is present
- Security services, night shifts, rentals, payments
- Any item whose description is too vague or fragmentary to identify a specific product

For each valid physical product return JSON with:
- description: clean product name only (strip "supply and install", "supply and fix", installation clauses, floor/spec suffixes)
- quantity: numeric value (use 1 if unclear but product is real)
- unit: unit of measure (pcs, m, m2, m3, set, no, kg, etc.)
- category: product category
- brand: brand/manufacturer if mentioned, else ""
- engineering_required: true if the item needs engineering design before procurement (panel boards, chillers, generators, fire suppression systems, BMS, pumps, AHUs, switchgear, transformers), else false

Return ONLY a valid JSON object: {"items": [...]}. No explanations, no markdown, no extra text.
PROMPT);
    }

    private function mockResponse(): array
    {
        return [
            'success' => true,
            'error'   => null,
            'items'   => [[
                'description'          => 'Sample BOQ Item',
                'quantity'             => 1,
                'unit'                 => 'No',
                'category'             => 'Sample',
                'brand'                => '',
                'status'               => 'pending',
                'engineering_required' => false,
                'unit_price'           => null,
                'confidence'           => 1,
                'raw_data'             => [],
                'ai_extracted'         => true,
            ]],
            'rejected' => [],
        ];
    }

    private function failure(string $message, bool $serviceUnavailable = false): array
    {
        return [
            'success'             => false,
            'items'               => [],
            'rejected'            => [],
            'error'               => $message,
            'service_unavailable' => $serviceUnavailable,
        ];
    }

    private function mimeForExtension(string $ext): string
    {
        return match (strtolower($ext)) {
            'pdf'         => 'application/pdf',
            'xlsx'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'        => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'        => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xls'         => 'application/vnd.ms-excel',
            'csv'         => 'text/csv',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'bmp'         => 'image/bmp',
            'webp'        => 'image/webp',
            'tiff', 'tif' => 'image/tiff',
            default       => 'application/octet-stream',
        };
    }
}
