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
    /** Increment this whenever BOQ parsing logic changes to invalidate old caches. */
    private const PARSER_VERSION = 10;
    private const LARGE_BOQ_VERIFICATION_THRESHOLD = 2000;
    private const LARGE_BOQ_VERIFICATION_CHUNK_SIZE = 120;

    /**
     * Characters of document text sent per AI call.
     *
     * Above this the document is split and parsed over several calls. Sized well
     * under the model's context so the prompt, the slice, and a full JSON answer
     * for every row in it all fit — the binding limit is the *output* budget, not
     * the input, since each input row produces a JSON object.
     */
    private const TEXT_CHUNK_CHARS = 60000;

    /**
     * A workbook with more sheets than this is split regardless of its size.
     *
     * Multiple tabs almost always mean multiple disciplines (electrical, HVAC,
     * civil…). Sending them in one call makes the model summarise across
     * sections and silently drop rows, so each slice gets a narrower scope.
     */
    private const MAX_SHEETS_PER_CALL = 2;

    /** A PDF longer than this is split regardless of its extracted text length. */
    private const MAX_PDF_PAGES_PER_CALL = 5;

    /**
     * Largest PDF we will hand to the parser (12 MB).
     *
     * smalot/pdfparser decompresses every stream into memory with no ceiling of
     * its own, and overrunning the limit there is a fatal error that kills the
     * worker rather than an exception we can recover from.
     */
    private const MAX_PDF_BYTES = 12582912;

    /**
     * Memory the parser is assumed to need, as a multiple of the file size.
     *
     * Decompressed content streams plus the object graph run well past the raw
     * bytes; 8x is conservative enough to catch the cases that were dying.
     */
    private const PDF_MEMORY_FACTOR = 8;

    private string $baseUrl;
    private string $parseEndpoint;
    private string $apiKey;
    private int $timeout;
    private bool $testMode;
    private BoqCleaningService $boqCleaner;

    /** Page count of the most recently parsed PDF; 0 when unknown. */
    private int $lastPdfPageCount = 0;

    /**
     * Optional progress reporter for chunked parsing, called as ($part, $total).
     *
     * Set by the queue job so the polling UI can show real progress on a large
     * BOQ instead of a spinner that appears frozen for many minutes.
     *
     * @var (callable(int, int): void)|null
     */
    private $onChunkProgress = null;

    /** @param  callable(int, int): void  $callback */
    public function onChunkProgress(callable $callback): self
    {
        $this->onChunkProgress = $callback;
        return $this;
    }

    /**
     * Optional per-chunk item sink, called as ($items, $part, $total).
     *
     * Lets the caller persist each slice's rows the moment they arrive instead
     * of waiting for the whole document. On a large BOQ this is the difference
     * between a table that fills in progressively and one that stays empty for
     * many minutes.
     *
     * @var (callable(array, int, int): void)|null
     */
    private $onChunkItems = null;

    /** @param  callable(array, int, int): void  $callback */
    public function onChunkItems(callable $callback): self
    {
        $this->onChunkItems = $callback;
        return $this;
    }

    /**
     * Report a non-chunk stage (local parse, handing off to AI) to the caller.
     *
     * A spreadsheet is read locally first and only sent to the AI when that
     * fails, so a large file can spend minutes before any chunk exists. Without
     * these the UI shows a generic spinner for that whole period.
     */
    private function reportStage(string $message): void
    {
        if ($this->onStage !== null) {
            ($this->onStage)($message);
        }
    }

    /**
     * Report a workbook's shape from grids that are already in memory.
     *
     * Deliberately takes the parsed grids rather than a path: re-reading a large
     * spreadsheet just to count its sheets would double the slowest step of the
     * whole extraction.
     *
     * @param  array<int, array{name: string, grid: array}>  $sheets
     */
    private function reportWorkbookShape(array $sheets): void
    {
        if ($this->onStage === null || empty($sheets)) {
            return;
        }

        $rows = 0;
        foreach ($sheets as $sheet) {
            $rows += count($sheet['grid']);
        }

        $count = count($sheets);

        $this->reportStage($count > 1
            ? "Found {$count} sheets, {$rows} rows in total. Reading…"
            : "Found {$rows} rows. Reading…");
    }

    /** @var (callable(string): void)|null */
    private $onStage = null;

    /** @param  callable(string): void  $callback */
    public function onStage(callable $callback): self
    {
        $this->onStage = $callback;
        return $this;
    }

    public function __construct(BoqCleaningService $boqCleaner)
    {
        $this->boqCleaner = $boqCleaner;
        $this->baseUrl = rtrim((string) config('services.ai_quotation.base_url', ''), '/');
        $this->parseEndpoint = ltrim((string) config('services.ai_quotation.parse_endpoint', 'parse'), '/');
        $this->apiKey = (string) config('services.ai_quotation.api_key', '');
        $this->timeout = (int) config('services.ai_quotation.timeout', 300);
        $this->testMode = (bool) config('services.ai_quotation.test_mode', false);
    }

    /**
     * Give the running script enough head-room to outlast the AI HTTP call.
     *
     * The web request / queue worker must not be killed before the HTTP timeout
     * completes, otherwise the user sees a generic "timeout or unavailable"
     * error even though the AI may have responded. We allow the HTTP timeout
     * plus a 120s margin for connecting, response parsing and DB writes.
     */
    private function extendExecutionTime(): void
    {
        // Ignored on some SAPIs (e.g. when running under fastcgi with a hard
        // limit), but harmless to attempt.
        @set_time_limit($this->timeout + 120);
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
        // Bump PARSER_VERSION whenever the extraction logic changes so that
        // previously-uploaded files are re-parsed instead of returning a stale
        // cached result produced by the old code.
        $cacheKey     = 'boq_analysis_v' . self::PARSER_VERSION . '_' . $fileHash;
        $forceRefresh = (bool) ($context['force_refresh'] ?? false);

        if (! $forceRefresh && ($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        // Clear stale cached result when force-refreshing
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        if (in_array($ext, ['xlsx', 'xlsm', 'xlsb', 'xls', 'csv'], true)) {
            // Read Excel locally first. Fall back to AI if local parse failed,
            // found 0 items, OR could not read one of the sheets (so multi-sheet
            // files never silently drop a sheet the local parser didn't understand).
            $this->reportStage('Reading the spreadsheet…');

            $result = $this->parseSpreadsheetDirect($absPath);

            $hasSkippedSheets = ! empty($result['skipped_sheets']);

            if (! $result['success'] || empty($result['items']) || $hasSkippedSheets) {
                // The local pass could not handle this file on its own. Say so:
                // otherwise the UI sits on a generic spinner through the local
                // parse AND the whole AI parse with nothing to show for either.
                $this->reportStage($hasSkippedSheets
                    ? 'Some sheets need AI to read. Sending to AI…'
                    : 'Spreadsheet needs AI to read. Sending to AI…');

                $deepSeekResult = $this->parseBoqWithDeepSeek($file, $context);
                // AI sees every sheet at once, so prefer its result whenever it
                // actually returned items (covers the skipped-sheet case too).
                if ($deepSeekResult['success'] && ! empty($deepSeekResult['items'])) {
                    // Keep the larger of the two item sets so we never lose the
                    // rows the local parser already extracted correctly.
                    if (count($deepSeekResult['items']) >= count($result['items'] ?? [])) {
                        $result = $deepSeekResult;
                    }
                } elseif (! $result['success']) {
                    $result = $deepSeekResult;
                }
                // else: keep local result if AI also failed/returned nothing
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

        if ($result['success'] && count($result['items'] ?? []) > self::LARGE_BOQ_VERIFICATION_THRESHOLD) {
            $result = $this->verifyLargeBoqItemsWithAi($result);
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
        ini_set('memory_limit', '2048M');

        // 180s was not enough for a real BOQ: an 11 200-row workbook was killed
        // mid-parse, and because set_time_limit() kills the process outright the
        // job reported a bare failure with nothing in the log to explain it.
        // This runs on the queue, where wall time is cheap.
        set_time_limit(1800);

        try {
            $sheets = $this->spreadsheetToGrids($absPath);

            if (empty($sheets)) {
                return $this->failure('Could not read the Excel file.');
            }

            $this->reportWorkbookShape($sheets);

            $items = [];
            $rejected = [];
            $skippedSheets = [];   // sheets we could not parse locally (header undetected)

            foreach ($sheets as $sheet) {
                $grid = $sheet['grid'];
                if (count($grid) < 2) {
                    continue;
                }

                // Skip dedicated summary / cost-abstract sheets entirely. Their rows
                // are per-discipline totals (Structural Concrete, Electrical, …) and
                // grand totals, not procurable products.
                if ($this->isSummarySheet($sheet['name'])) {
                    Log::info('QuotationAiService: skipped summary sheet.', ['sheet' => $sheet['name']]);
                    continue;
                }

                $header = $this->detectHeader($grid);

                if ($header === null || ! isset($header['map']['description'])) {
                    Log::warning('QuotationAiService: header not detected in sheet.', ['sheet' => $sheet['name']]);
                    $skippedSheets[] = $sheet['name'];
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

                    if (! isset($map['quantity'])) {
                        $quantity = 1.0;
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

                    $supply = $this->boqCleaner->filterItem($description, $unitPrice, $quantity);

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
                'success'        => true,
                'items'          => $items,
                'rejected'       => $rejected,
                'error'          => null,
                'skipped_sheets' => $skippedSheets,
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
                    $value = $this->cellValueToString($value);
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

        if ($bestScore >= 3) {
            return $best;
        }

        // Allow simpler BOQ sheets that only define a description column plus one
        // other BOQ-related column, e.g. Item + Description or Description + Qty.
        if ($bestScore === 2 && isset($best['map']['description']) && (
            isset($best['map']['item_code']) ||
            isset($best['map']['quantity']) ||
            isset($best['map']['unit'])
        )) {
            return $best;
        }

        return null;
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

        // Bare "Total" lines, optionally with a currency suffix, e.g. "Total",
        // "Total (SAR)", "Total cost", "Total (SAR) unit cost".
        if (preg_match('/^total\b[\s\(\):a-z]*$/iu', $d)) {
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

    /**
     * Detect dedicated summary / cost-abstract sheets by their name. These hold
     * per-discipline rollups and grand totals (e.g. a "Summary (Dry Cost)" tab),
     * never procurable supply items, so they are skipped wholesale.
     */
    private function isSummarySheet(string $name): bool
    {
        $n = mb_strtolower(trim($name));

        if ($n === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(summary|abstract|cost\s*summary|grand\s*summary|recap(?:itulation)?|bill\s*summary|boq\s*summary|dry\s*cost)\b|Ù…Ù„Ø®Øµ|Ø§Ù„Ù…Ù„Ø®Øµ|Ø§Ù„Ù…Ù„Ø®Ù‘Øµ|Ù…Ù„Ø®Ù‘Øµ|Ø§Ø¬Ù…Ø§Ù„ÙŠ|Ø¥Ø¬Ù…Ø§Ù„ÙŠ/iu',
            $n
        );
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

                $text = $this->sanitizeUtf8($text);

                // A large BOQ exceeds what one request can carry, so send it in
                // sequential slices and merge. Anything that fits goes as-is.
                //
                // Size alone is not a good enough signal: a workbook with several
                // sheets is structurally a big BOQ even when the text is compact,
                // and asking the model to hold every discipline at once is where
                // rows start getting dropped. So split on either signal.
                $sheetCount = substr_count($text, "\nSheet: ") + (str_starts_with($text, 'Sheet: ') ? 1 : 0);

                if (mb_strlen($text) > self::TEXT_CHUNK_CHARS || $sheetCount > self::MAX_SHEETS_PER_CALL) {
                    Log::info('QuotationAiService: spreadsheet routed to chunked parsing.', [
                        'chars'  => mb_strlen($text),
                        'sheets' => $sheetCount,
                    ]);
                    return $this->parseTextInChunks($text, 'BOQ spreadsheet converted to CSV', $apiKey, $context);
                }

                $userContent = "BOQ spreadsheet converted to CSV:\n\n" . $text . "\n\n" . $this->buildDeepSeekPrompt($context, 'text/plain');
                return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
            }

            // â”€â”€ PDF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if ($ext === 'pdf') {
                $extracted = $this->extractPdfText($absPath);
                if ($extracted !== null && mb_strlen(trim($extracted)) > 50) {
                    $extracted = $this->sanitizeUtf8($extracted);

                    // Split on either signal — long text, or many pages. A BOQ
                    // spanning several pages is a multi-section document even if
                    // each page is sparse.
                    if (mb_strlen($extracted) > self::TEXT_CHUNK_CHARS
                        || $this->lastPdfPageCount > self::MAX_PDF_PAGES_PER_CALL) {
                        Log::info('QuotationAiService: PDF routed to chunked parsing.', [
                            'chars' => mb_strlen($extracted),
                            'pages' => $this->lastPdfPageCount,
                        ]);
                        return $this->parseTextInChunks($extracted, 'BOQ PDF extracted text', $apiKey, $context, 'application/pdf');
                    }

                    $userContent = "BOQ PDF extracted text:\n\n" . $extracted . "\n\n" . $this->buildDeepSeekPrompt($context, 'application/pdf');
                    return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
                }
                return $this->failure('Could not extract text from the PDF. Please make sure it is a text-based PDF (not a scanned image), or convert it to Excel or CSV.');
            }

            // -- Word documents (docx) ---------------------------------------------------
            if (in_array($ext, ['docx', 'doc'], true)) {
                $text = $this->extractDocxText($absPath);
                if ($text === null || trim($text) === '') {
                    return $this->failure('Could not extract text from the Word document. Please convert it to Excel, CSV, or PDF.');
                }
                $text = $this->sanitizeUtf8($text);

                if (mb_strlen($text) > self::TEXT_CHUNK_CHARS) {
                    return $this->parseTextInChunks($text, 'BOQ Word document extracted text', $apiKey, $context);
                }

                $userContent = "BOQ Word document extracted text:\n\n" . $text . "\n\n" . $this->buildDeepSeekPrompt($context, 'text/plain');
                return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
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
     * Extract plain text from a Word docx file using ZipArchive (no external library needed).
     * Reads word/document.xml and strips XML tags, preserving paragraph and table structure.
     */
    private function extractDocxText(string $absPath): ?string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($absPath) !== true) {
                return null;
            }

            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xml === false || $xml === '') {
                return null;
            }

            // Add newlines before paragraph/table-row/table-cell tags so structure is preserved
            $xml = preg_replace('/<w:p[ >]/', "\n<w:p>", $xml) ?? $xml;
            $xml = preg_replace('/<w:tr[ >]/', "\n<w:tr>", $xml) ?? $xml;
            $xml = preg_replace('/<\/w:tc>/', "\t", $xml) ?? $xml;

            $text = strip_tags($xml);
            $text = (string) preg_replace('/[ \t]{2,}/', ' ', $text);
            $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);
            return trim($text) !== '' ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('QuotationAiService: DOCX parsing failed.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Extract plain text from a PDF using smalot/pdfparser (pure PHP, no binary required).
     */
    /**
     * The process memory limit in bytes, or 0 when unlimited.
     *
     * memory_limit is a shorthand string ("128M", "1G", "-1"), so it has to be
     * expanded before it can be compared against anything.
     */
    private function memoryLimitBytes(): int
    {
        $raw = trim((string) ini_get('memory_limit'));

        if ($raw === '' || $raw === '-1') {
            return 0;
        }

        $value = (int) $raw;

        return match (strtolower(substr($raw, -1))) {
            'g'     => $value * 1024 * 1024 * 1024,
            'm'     => $value * 1024 * 1024,
            'k'     => $value * 1024,
            default => $value,
        };
    }

    private function extractPdfText(string $absPath): ?string
    {
        // The parser loads and decompresses the whole document before any of our
        // chunking gets a look at it, and it does so with no memory ceiling of
        // its own. A big PDF exhausts the limit inside gzuncompress() — a fatal
        // error, not an exception, so the catch below cannot help and the worker
        // dies outright. Refuse the file first instead.
        $size = @filesize($absPath) ?: 0;

        if ($size > self::MAX_PDF_BYTES) {
            Log::warning('QuotationAiService: PDF too large to parse in-process.', [
                'bytes' => $size,
                'limit' => self::MAX_PDF_BYTES,
            ]);

            return null;
        }

        // Headroom check: parsing routinely needs several times the file size
        // once streams are decompressed. If that would not fit in what is left,
        // stop here rather than taking the worker down with us.
        $limit = $this->memoryLimitBytes();

        if ($limit > 0) {
            $available = $limit - memory_get_usage(true);

            if ($available < $size * self::PDF_MEMORY_FACTOR) {
                Log::warning('QuotationAiService: not enough memory left to parse this PDF.', [
                    'bytes'     => $size,
                    'available' => $available,
                    'needed'    => $size * self::PDF_MEMORY_FACTOR,
                ]);

                return null;
            }
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($absPath);

            // Recorded for the chunking decision: a long PDF is structurally a
            // big BOQ even when its extracted text is short.
            try {
                $this->lastPdfPageCount = count($pdf->getPages());
            } catch (\Throwable) {
                $this->lastPdfPageCount = 0;
            }

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
     * DeepSeek's commercial API (api.deepseek.com) does NOT support image inputs — any model.
     * Priority order (first configured key wins):
     *   1. GROQ_API_KEY    → Groq llama-4-scout  (free: https://console.groq.com)
     *   2. GEMINI_API_KEY  → Google Gemini Flash  (free: https://aistudio.google.com/apikey)
     *   3. VISION_API_KEY  → any OpenAI-compatible endpoint (OpenAI, OpenRouter …)
     */
    private function callDeepSeekVision(string $bytes, string $mime, array $context): array
    {
        // ── 1. Groq (free, reliable vision) ─────────────────────────────────────
        if ((string) config('services.groq.key', '') !== '') {
            $visionKey     = (string) config('services.groq.key');
            $visionBaseUrl = 'https://api.groq.com/openai/v1';
            $visionModel   = (string) config('services.groq.model', 'meta-llama/llama-4-scout-17b-16e-instruct');
        }
        // ── 2. Google Gemini ─────────────────────────────────────────────────────
        elseif ((string) config('services.gemini.key', '') !== '') {
            $visionKey     = (string) config('services.gemini.key');
            $visionBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
            $visionModel   = (string) config('services.gemini.model', 'gemini-2.0-flash');
        }
        // ── 3. Generic VISION_API_KEY (OpenAI, OpenRouter, …) ───────────────────
        elseif ((string) config('services.vision.key', '') !== '') {
            $visionKey     = (string) config('services.vision.key');
            $visionBaseUrl = rtrim((string) config('services.vision.base_url', 'https://openrouter.ai/api/v1'), '/');
            $visionModel   = (string) config('services.vision.model', 'google/gemini-flash-1.5-8b');
        }
        else {
            return $this->failure(
                'Image processing is not available. DeepSeek\'s API does not support images. ' .
                'Get a free key at https://console.groq.com and add GROQ_API_KEY to your .env file.'
            );
        }

        $dataUrl = "data:{$mime};base64," . base64_encode($bytes);
        $prompt  = $this->buildDeepSeekPrompt($context, $mime);

        $userContent = [
            ['type' => 'text',      'text'      => $prompt],
            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
        ];

        $this->extendExecutionTime();

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
                    'max_tokens'  => 16384,
                    'temperature' => 0,
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
        // Ensure the PHP script outlives the HTTP call (+ a margin for parsing the
        // response and writing to the DB) so the worker/web request is not killed
        // mid-flight, which surfaces to users as a generic "timeout or unavailable".
        $this->extendExecutionTime();

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
                    'max_tokens'      => 32768,
                    'temperature'     => 0,
                    'response_format' => ['type' => 'json_object'],
                    'user'            => 'Qimta_Platform',
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
     * Parse a document too large for one AI call by sending it in slices.
     *
     * A 20 000-row BOQ is several megabytes of text — far beyond what a single
     * request can carry, and beyond what the model can answer without hitting its
     * output token limit. Previously the text was simply cut at 180 000 chars and
     * the remainder discarded: the parse "succeeded" while silently dropping most
     * of the file. Now the text is split and each slice parsed on its own call,
     * with the items merged.
     *
     * Splitting happens on line boundaries so a BOQ row is never cut in half.
     * Slices run sequentially rather than pooled: DeepSeek rate-limits hard on
     * concurrent large prompts, and this already runs on the queue where wall
     * time is cheap.
     *
     * A slice that fails does not fail the document — its rows are lost, but the
     * rest are kept and the shortfall is logged. Total failure is reported only
     * when every slice fails.
     *
     * @param  string  $label   How the payload is introduced to the model.
     * @return array{success:bool,items:array,rejected:array,error:?string}
     */
    /**
     * Split a spreadsheet into the slices the AI will be asked to read.
     *
     * Exposed so the work can be spread over several queue jobs instead of one
     * job walking every slice in sequence. Returns an empty array when the file
     * is small enough to go in a single call, or cannot be read.
     *
     * @return array<int, string>
     */
    public function chunkSpreadsheet(string $absPath): array
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);

        $text = $this->spreadsheetToCompactCsvText($absPath);

        if ($text === null || trim($text) === '') {
            return [];
        }

        $text       = $this->sanitizeUtf8($text);
        $sheetCount = substr_count($text, "\nSheet: ") + (str_starts_with($text, 'Sheet: ') ? 1 : 0);

        if (mb_strlen($text) <= self::TEXT_CHUNK_CHARS && $sheetCount <= self::MAX_SHEETS_PER_CALL) {
            return [];
        }

        $chunks = $this->splitOnLineBoundaries($text, self::TEXT_CHUNK_CHARS);

        // Free the source text: the slices are a second full copy of it, and on
        // a large workbook that doubling — on top of PhpSpreadsheet's grids —
        // is what exhausted memory here.
        unset($text);

        Log::info('QuotationAiService: chunked spreadsheet.', [
            'chunks'  => count($chunks),
            'peak_mb' => round(memory_get_peak_usage(true) / 1048576),
        ]);

        return $chunks;
    }

    /**
     * Split a spreadsheet straight to disk, one file per slice.
     *
     * chunkSpreadsheet() returns every slice as an array, which is a second
     * full copy of the document in memory on top of PhpSpreadsheet's grids.
     * This hands each slice to $write and drops it immediately, so peak memory
     * is one slice rather than the whole file twice.
     *
     * @param  callable(int, string): void  $write  ($part, $chunk)
     * @return int  number of slices written; 0 when the file needs no split
     */
    public function chunkSpreadsheetToDisk(string $absPath, callable $write): int
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);

        $text = $this->spreadsheetToCompactCsvText($absPath);

        if ($text === null || trim($text) === '') {
            return 0;
        }

        $text       = $this->sanitizeUtf8($text);
        $sheetCount = substr_count($text, "\nSheet: ") + (str_starts_with($text, 'Sheet: ') ? 1 : 0);

        if (mb_strlen($text) <= self::TEXT_CHUNK_CHARS && $sheetCount <= self::MAX_SHEETS_PER_CALL) {
            return 0;
        }

        $part = 0;
        foreach ($this->splitOnLineBoundaries($text, self::TEXT_CHUNK_CHARS) as $chunk) {
            $write(++$part, $chunk);
        }

        unset($text);

        Log::info('QuotationAiService: chunked spreadsheet to disk.', [
            'chunks'  => $part,
            'peak_mb' => round(memory_get_peak_usage(true) / 1048576),
        ]);

        return $part;
    }

    /**
     * Parse one slice produced by chunkSpreadsheet().
     *
     * @return array{success:bool,items:array,rejected:array,error:?string}
     */
    public function parseChunk(string $chunk, int $part, int $total, array $context = []): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            return $this->failure('AI service is not configured.');
        }

        // Same framing parseTextInChunks() uses, so a slice is never mistaken
        // for a complete BOQ and totals are not inferred from a fragment.
        $userContent = "BOQ spreadsheet converted to CSV (part {$part} of {$total}):\n\n"
            . $chunk . "\n\n"
            . "NOTE: This is part {$part} of {$total} of a larger document. Extract only the rows present in this part. Do not infer totals for the whole document.\n\n"
            . $this->buildDeepSeekPrompt($context, 'text/plain');

        return $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());
    }

    private function parseTextInChunks(
        string $text,
        string $label,
        string $apiKey,
        array $context,
        string $mime = 'text/plain',
    ): array {
        $chunks = $this->splitOnLineBoundaries($text, self::TEXT_CHUNK_CHARS);
        $total  = count($chunks);

        Log::info('QuotationAiService: large document split for parsing.', [
            'chars'  => mb_strlen($text),
            'chunks' => $total,
        ]);

        // Announce the split before the first slice is sent, so the UI can show
        // how many parts there are instead of sitting on a generic message for
        // however long the first AI call takes.
        if ($this->onChunkProgress !== null) {
            ($this->onChunkProgress)(0, $total);
        }

        $items    = [];
        $rejected = [];
        $failed   = 0;
        $lastError = null;

        foreach ($chunks as $index => $chunk) {
            $part = $index + 1;

            // Report progress so a 28-slice parse does not look frozen to the user.
            if ($this->onChunkProgress !== null) {
                ($this->onChunkProgress)($part, $total);
            }

            // Tell the model this is a fragment, so it does not try to reconcile
            // totals or treat a mid-document slice as a complete BOQ.
            $userContent = "{$label} (part {$part} of {$total}):\n\n"
                . $chunk . "\n\n"
                . "NOTE: This is part {$part} of {$total} of a larger document. Extract only the rows present in this part. Do not infer totals for the whole document.\n\n"
                . $this->buildDeepSeekPrompt($context, $mime);

            $result = $this->callDeepSeekChat($userContent, $apiKey, $this->deepSeekModel());

            if (! ($result['success'] ?? false)) {
                $failed++;
                $lastError = $result['error'] ?? 'Chunk parsing failed.';
                Log::warning('QuotationAiService: chunk failed, continuing.', [
                    'part'  => $part,
                    'of'    => $total,
                    'error' => $lastError,
                ]);
                continue;
            }

            foreach ($result['items'] as $item) {
                $items[] = $item;
            }
            foreach ($result['rejected'] ?? [] as $row) {
                $rejected[] = $row;
            }

            // Hand this slice's rows straight to the caller so they can be shown
            // while the remaining slices are still being parsed.
            if ($this->onChunkItems !== null && $result['items'] !== []) {
                ($this->onChunkItems)($result['items'], $part, $total);
            }
        }

        // Every slice failed — there is nothing to show, so report the failure.
        if ($failed === $total) {
            return $this->failure($lastError ?? 'Could not parse the document.');
        }

        if ($failed > 0) {
            Log::error('QuotationAiService: document parsed with missing parts.', [
                'failed_chunks' => $failed,
                'total_chunks'  => $total,
                'items_kept'    => count($items),
            ]);
        }

        return [
            'success'             => true,
            'items'               => $items,
            'rejected'            => $rejected,
            'error'               => null,
            'service_unavailable' => false,
            'partial'             => $failed > 0,
            'failed_chunks'       => $failed,
            'total_chunks'        => $total,
        ];
    }

    /**
     * Split text into slices of at most $limit characters, breaking only at
     * newlines so no BOQ row is ever cut in half.
     *
     * A single line longer than the limit (rare — a pathological cell) is passed
     * through oversized rather than severed mid-row.
     *
     * @return array<int, string>
     */
    private function splitOnLineBoundaries(string $text, int $limit): array
    {
        $chunks  = [];
        $current = '';

        // Sheet a chunk starts in the middle of. Repeated at the top of the next
        // chunk so the model always knows which sheet the rows belong to — the
        // header would otherwise be stranded in the previous slice, and rows
        // would arrive with no discipline context.
        $currentSheet = null;

        foreach (explode("\n", $text) as $line) {
            $isSheetHeader = str_starts_with($line, 'Sheet: ');

            // Start a new chunk at every sheet boundary. Sheets are usually
            // separate disciplines, and the whole point of splitting a multi-tab
            // workbook is to give the model one discipline at a time — a size-only
            // split would leave a compact 3-tab file as a single chunk, silently
            // undoing the sheet gate that routed it here.
            if ($isSheetHeader && $current !== '') {
                $chunks[]     = $current;
                $current      = $line;
                $currentSheet = $line;
                continue;
            }

            if ($isSheetHeader) {
                $currentSheet = $line;
            }

            $candidate = $current === '' ? $line : $current . "\n" . $line;

            if (mb_strlen($candidate) > $limit && $current !== '') {
                $chunks[] = $current;

                // Carry the sheet header over, unless this line is itself one.
                $current = ($currentSheet !== null && ! str_starts_with($line, 'Sheet: '))
                    ? $currentSheet . " (continued)\n" . $line
                    : $line;
                continue;
            }

            $current = $candidate;
        }

        if (trim($current) !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Shared JSON response parser used by both chat and vision paths.
     */
    private function parseDeepSeekJsonResponse(string $text): array
    {
        if ($text === '') {
            return $this->failure('DeepSeek returned an empty response.');
        }

        // Strip markdown code fences if the model wrapped the JSON.
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $text, $m)) {
            $text = $m[1];
        }
        // Narrow to the outermost JSON object/array.
        if (preg_match('/[\{\[][\s\S]*[\}\]]/s', $text, $m)) {
            $text = $m[0];
        }
        $text = trim($text);

        $decoded = json_decode($text, true);

        // The response can be truncated when the model hits the token limit,
        // leaving an unterminated JSON string/object. Try to repair it before
        // giving up so we can still salvage the items extracted so far.
        if (! is_array($decoded)) {
            $repaired = $this->repairTruncatedJson($text);
            if ($repaired !== null) {
                $decoded = json_decode($repaired, true);
            }
        }

        if (! is_array($decoded)) {
            Log::error('QuotationAiService: DeepSeek non-JSON response.', ['text' => mb_substr($text, 0, 1000)]);
            return $this->failure('DeepSeek returned a non-JSON response. The file may be too large — try splitting it into smaller files.');
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

    /**
     * Best-effort repair of a truncated JSON string produced when the model hits
     * its token limit mid-output. Drops any incomplete trailing object, then
     * closes the open string/array/object brackets so the result is valid JSON.
     *
     * Returns the repaired JSON string, or null if it cannot be salvaged.
     */
    private function repairTruncatedJson(string $text): ?string
    {
        if ($text === '') {
            return null;
        }

        // Walk the string tracking structural state, ignoring everything inside
        // string literals (and their escapes). Remember the last position where
        // we were back at the top of the "items" array between complete elements,
        // so we can cut a half-written trailing object.
        $stack          = [];
        $inString       = false;
        $escaped        = false;
        $len            = strlen($text);
        $lastSafeCut    = null; // index just after a completed array element

        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === '\\') {
                    $escaped = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($ch === '"') {
                $inString = true;
            } elseif ($ch === '{' || $ch === '[') {
                $stack[] = $ch;
            } elseif ($ch === '}' || $ch === ']') {
                array_pop($stack);
            } elseif ($ch === ',' && ! empty($stack) && end($stack) === '[') {
                // Separator directly inside an array: a clean boundary between
                // elements (the items array, whatever its nesting depth).
                $lastSafeCut = $i;
            }
        }

        // If we are still inside a string, the last element is incomplete — fall
        // back to the last clean array boundary if we have one.
        $candidate = $text;
        if ($inString && $lastSafeCut !== null) {
            $candidate = substr($text, 0, $lastSafeCut);
        } else {
            // Drop a dangling partial object after the last completed element.
            $candidate = rtrim($candidate);
            if ($lastSafeCut !== null && substr(rtrim($candidate), -1) !== '}' && substr(rtrim($candidate), -1) !== ']') {
                $candidate = substr($text, 0, $lastSafeCut);
            }
        }

        // Recompute the open brackets for the (possibly trimmed) candidate and
        // append the matching closers in reverse order.
        $stack    = [];
        $inString = false;
        $escaped  = false;
        $len      = strlen($candidate);
        for ($i = 0; $i < $len; $i++) {
            $ch = $candidate[$i];
            if ($inString) {
                if ($escaped)            { $escaped = false; }
                elseif ($ch === '\\')    { $escaped = true; }
                elseif ($ch === '"')     { $inString = false; }
                continue;
            }
            if ($ch === '"')                       { $inString = true; }
            elseif ($ch === '{' || $ch === '[')    { $stack[] = $ch; }
            elseif ($ch === '}' || $ch === ']')    { array_pop($stack); }
        }

        $candidate = rtrim($candidate);
        // Remove a trailing comma left after cutting an element.
        $candidate = rtrim($candidate, ',');

        $closers = '';
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $closers .= ($stack[$i] === '{') ? '}' : ']';
        }

        $repaired = $candidate . $closers;

        // Validate before returning.
        return is_array(json_decode($repaired, true)) ? $repaired : null;
    }

    private function spreadsheetToCompactCsvText(string $absPath): ?string
    {
        $sheets = $this->spreadsheetToGrids($absPath);
        if (empty($sheets)) {
            return null;
        }

        $output     = '';
        $kept       = 0;
        $dropped    = 0;
        $nonSupply  = 0;
        $duplicates = 0;

        /** @var array<string, true> Fingerprints of rows already emitted. */
        $seenRows = [];

        foreach ($sheets as $sheet) {
            // Don't feed summary / cost-abstract sheets to the AI either.
            if ($this->isSummarySheet($sheet['name'])) {
                continue;
            }
            $sheetRows = '';
            foreach ($sheet['grid'] as $row) {
                $row = $this->trimTrailingEmptyCells($row);

                if ($this->isNoiseRow($row)) {
                    $dropped++;
                    continue;
                }

                // Drop rows that are definitely not procurable before they cost
                // a single token: section totals, discipline headings,
                // preliminaries, labour-only lines. The same classifier already
                // runs on the AI's output, but by then the tokens are spent — on
                // an 11 200-row BOQ that is most of the bill.
                if ($this->isNonSupplyRow($row)) {
                    $dropped++;
                    $nonSupply++;
                    continue;
                }

                // Byte-identical rows tell the AI nothing new. Only exact
                // duplicates are collapsed — description, quantity, unit and all
                // — so nothing is lost: two rows differing in quantity are two
                // different line items and both are sent.
                $fingerprint = $this->rowFingerprint($row);

                if (isset($seenRows[$fingerprint])) {
                    $duplicates++;
                    $dropped++;
                    continue;
                }
                $seenRows[$fingerprint] = true;

                $sheetRows .= implode(',', array_map([$this, 'csvEscape'], $this->stripBoilerplate($row))) . "\n";
                $kept++;
            }

            // A sheet whose rows were all noise contributes nothing but its own
            // header, which would just be another thing for the model to explain.
            if ($sheetRows === '') {
                continue;
            }

            $output .= 'Sheet: ' . $sheet['name'] . "\n" . $sheetRows . "\n";
        }

        $blank = $dropped - $nonSupply - $duplicates;

        Log::info('QuotationAiService: filtered rows before sending to AI.', [
            'kept'       => $kept,
            'blank'      => $blank,
            'non_supply' => $nonSupply,
            'duplicates' => $duplicates,
        ]);

        if ($dropped > 0) {
            $parts = [];
            if ($blank > 0)      { $parts[] = "{$blank} blank"; }
            if ($nonSupply > 0)  { $parts[] = "{$nonSupply} non-supply"; }
            if ($duplicates > 0) { $parts[] = "{$duplicates} duplicate"; }

            $this->reportStage('Skipped ' . implode(', ', $parts) . " rows. Sending {$kept}…");
        }

        return $output;
    }

    /**
     * True when a row carries no information worth sending to the AI.
     *
     * A real BOQ is mostly formatting: spacer rows, stray borders, a lone
     * section number, a leftover cell from a merged block. They survive
     * trimTrailingEmptyCells() because they are not *entirely* empty, then cost
     * tokens, dilute the prompt, and inflate the number of parts a large file is
     * split into.
     *
     * Deliberately conservative — a row is dropped only when nothing in it could
     * name a product. Anything with letters is kept, because a description is
     * the one field an item cannot do without.
     *
     * @param  array<int, mixed>  $row
     */
    /**
     * True when a row is definitely not a procurable supply item.
     *
     * Runs BoqCleaningService over the row's longest text cell — the one most
     * likely to be the description — so headings, totals, preliminaries and
     * labour-only lines never reach the AI. The same classifier already vets the
     * AI's *output*; doing it here as well means we stop paying to have those
     * rows read in the first place.
     *
     * Conservative by construction: only rows the classifier positively rejects
     * are dropped. Anything it is unsure about goes to the AI, which is the
     * better judge — a wrongly dropped row is invisible, a wrongly kept one only
     * costs tokens.
     *
     * @param  array<int, mixed>  $row
     */
    /**
     * Stable identity for a row, used to collapse exact duplicates.
     *
     * Whitespace and case are normalised so "DN50 PIPE" and "dn50 pipe " are one
     * row, but nothing else is: a differing quantity or unit produces a
     * different fingerprint and both rows are sent.
     *
     * @param  array<int, mixed>  $row
     */
    private function rowFingerprint(array $row): string
    {
        $parts = array_map(
            fn($cell) => mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $cell)) ?? ''),
            $row,
        );

        // Drop a leading BOQ sequence number. Otherwise "1,Pipe,m,100" and
        // "2,Pipe,m,100" fingerprint differently and the dedup never fires —
        // which is exactly the shape every real BOQ has.
        if (isset($parts[0]) && $parts[0] !== '' && preg_match('/^[\d.]+$/', $parts[0])) {
            array_shift($parts);
        }

        return md5(implode('|', $parts));
    }

    /**
     * Strip contractual boilerplate from a row's description cell.
     *
     * "as per drawings", "complete with all necessary accessories" and friends
     * repeat on nearly every line of a real BOQ and carry no information the
     * pricing engine can use — but they are a large share of the tokens.
     *
     * Only the longest text cell is touched, and only when the result is still a
     * usable description: if stripping would leave too little to identify the
     * product, the original is kept. A shorter-but-wrong description is far
     * worse than a verbose one.
     *
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    private function stripBoilerplate(array $row): array
    {
        $index  = null;
        $longest = '';

        foreach ($row as $i => $cell) {
            $text = trim((string) $cell);
            if (preg_match('/\p{L}/u', $text) && mb_strlen($text) > mb_strlen($longest)) {
                $longest = $text;
                $index   = $i;
            }
        }

        // Nothing worth cleaning, or too short to risk it.
        if ($index === null || mb_strlen($longest) < 40) {
            return $row;
        }

        try {
            $cleaned = $this->boqCleaner->cleanDescription($longest);
        } catch (\Throwable $e) {
            Log::warning('QuotationAiService: boilerplate strip failed, keeping row as-is.', [
                'error' => $e->getMessage(),
            ]);
            return $row;
        }

        // Guard against an over-eager pattern eating the product name: keep the
        // original unless a recognisable description survives.
        if (mb_strlen($cleaned) < 12 || mb_strlen($cleaned) < mb_strlen($longest) * 0.3) {
            return $row;
        }

        $row[$index] = $cleaned;

        return $row;
    }

    private function isNonSupplyRow(array $row): bool
    {
        $description = '';
        foreach ($row as $cell) {
            $text = trim((string) $cell);
            // Longest cell containing letters — numbers are qty/rate columns.
            if (preg_match('/\p{L}/u', $text) && mb_strlen($text) > mb_strlen($description)) {
                $description = $text;
            }
        }

        // No text at all: leave it to isNoiseRow()/the AI rather than guessing.
        if ($description === '' || mb_strlen($description) < 3) {
            return false;
        }

        try {
            $verdict = $this->boqCleaner->filterItem($description);
        } catch (\Throwable $e) {
            // A classifier failure must never silently drop a real product.
            Log::warning('QuotationAiService: pre-filter classifier failed, keeping row.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        return ! ($verdict['keep'] ?? true);
    }

    private function isNoiseRow(array $row): bool
    {
        if (empty($row)) {
            return true;
        }

        $joined = trim(implode('', array_map(fn($c) => trim((string) $c), $row)));

        // Nothing but separators/whitespace.
        if ($joined === '') {
            return true;
        }

        // Ruled lines and decorative fills ("-----", "====", "___").
        if (preg_match('/^[\-_=.*\s|]+$/u', $joined)) {
            return true;
        }

        // Any letter — Latin or Arabic — means this could be a description.
        if (preg_match('/\p{L}/u', $joined)) {
            return false;
        }

        // No letters at all: numbers only. A single number is a stray row
        // number or a dangling total; two or more could be qty/rate/amount
        // belonging to a real line, so keep those for the AI to judge.
        $numericCells = 0;
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                $numericCells++;
            }
        }

        return $numericCells <= 1;
    }

    /**
     * Safely convert a PhpSpreadsheet cell value to a string.
     *
     * Cells may contain RichText or, in malformed/heavily-formatted files,
     * embedded objects such as Worksheet\Drawing that have no __toString().
     * Casting those directly throws "Object ... could not be converted to string".
     */
    private function cellValueToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            return $value->getPlainText();
        }

        if (is_object($value)) {
            // Only stringify objects that explicitly support it; ignore the rest
            // (e.g. Worksheet\Drawing) instead of crashing.
            return method_exists($value, '__toString') ? (string) $value : '';
        }

        if (is_array($value)) {
            return '';
        }

        return (string) $value;
    }

    private function csvEscape(mixed $value): string
    {
        $value = $this->cellValueToString($value);
        return str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")
            ? '"' . str_replace('"', '""', $value) . '"'
            : $value;
    }

    private function verifyLargeBoqItemsWithAi(array $result): array
    {
        $apiKey = (string) config('services.deepseek.key', '');
        if ($apiKey === '') {
            Log::warning('QuotationAiService: large BOQ verification skipped, AI key is not configured.');
            return $result;
        }

        $items = array_values(array_filter($result['items'] ?? [], 'is_array'));
        if (count($items) <= self::LARGE_BOQ_VERIFICATION_THRESHOLD) {
            return $result;
        }

        Log::info('QuotationAiService: starting second-pass physical item verification.', [
            'items_count' => count($items),
            'threshold'   => self::LARGE_BOQ_VERIFICATION_THRESHOLD,
        ]);

        $verifiedItems = [];
        $rejectedItems = $result['rejected'] ?? [];

        foreach (array_chunk($items, self::LARGE_BOQ_VERIFICATION_CHUNK_SIZE) as $chunkIndex => $chunk) {
            $payload = array_map(fn(array $item) => [
                'description' => (string) ($item['description'] ?? ''),
                'quantity'    => $item['quantity'] ?? null,
                'unit'        => (string) ($item['unit'] ?? ''),
                'unit_price'  => $item['unit_price'] ?? null,
                'category'    => (string) ($item['category'] ?? ''),
                'brand'       => (string) ($item['brand'] ?? ''),
            ], $chunk);

            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (! is_string($json)) {
                Log::warning('QuotationAiService: large BOQ verification chunk JSON encode failed.', [
                    'chunk' => $chunkIndex + 1,
                ]);
                array_push($verifiedItems, ...$chunk);
                continue;
            }

            $prompt = <<<PROMPT
You are doing a SECOND PASS verification on a BOQ extraction result.

The first pass may hallucinate non-products when the BOQ has thousands of rows.
Your job is to keep ONLY physical, tangible, procurable supply products.

Rules:
- Keep items only if a supplier can actually deliver them as materials/equipment.
- Reject headings, trade names, systems, scopes, prose, specifications-only rows, totals, labor, installation, testing, commissioning, supervision, drawings, insurance, preliminaries, mobilisation, site works, and vague fragments.
- Do not invent new products.
- Do not add items that are not in the input.
- You may clean a product description, but keep the original quantity, unit, unit_price, category, and brand when available.

Input JSON array:
{$json}

Return ONLY valid JSON in this shape:
{"items":[{"description":"","quantity":1,"unit":"","unit_price":null,"category":"","brand":"","confidence":0.95}]}
PROMPT;

            try {
                $chunkResult = $this->callDeepSeekChat($prompt, $apiKey, $this->deepSeekModel());
            } catch (\Throwable $e) {
                Log::warning('QuotationAiService: large BOQ verification chunk threw an exception.', [
                    'chunk'   => $chunkIndex + 1,
                    'message' => $e->getMessage(),
                ]);
                array_push($verifiedItems, ...$chunk);
                continue;
            }

            if ($chunkResult['success'] ?? false) {
                array_push($verifiedItems, ...($chunkResult['items'] ?? []));
                $rejectedItems = array_merge($rejectedItems, $chunkResult['rejected'] ?? []);
                continue;
            }

            Log::warning('QuotationAiService: large BOQ verification chunk failed, keeping original chunk.', [
                'chunk' => $chunkIndex + 1,
                'error' => $chunkResult['error'] ?? null,
            ]);
            array_push($verifiedItems, ...$chunk);
        }

        if (empty($verifiedItems)) {
            Log::warning('QuotationAiService: second-pass verification returned no items, keeping first-pass result.', [
                'original_items_count' => count($items),
            ]);
            return $result;
        }

        $result['items'] = $verifiedItems;
        $result['rejected'] = $rejectedItems;
        $result['large_boq_verified'] = true;

        Log::info('QuotationAiService: second-pass physical item verification completed.', [
            'before' => count($items),
            'after'  => count($verifiedItems),
        ]);

        return $result;
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
Extract ONLY concrete, tangible, physical products that a supplier can box up and deliver to site — a specific item you could point at and say "this is the thing being bought" (e.g. "Wall mounted LED luminaire 36W", "PVC pipe 110mm", "Single phase distribution board 12-way", "Fire extinguisher CO2 6kg", "CCTV dome camera 4MP").

ABSOLUTELY REJECT abstract concepts, disciplines, categories, section names, and trade headings. These are NOT products even though they appear as rows:
- Bare discipline/trade words: "Electrical", "Mechanical", "Plumbing", "HVAC", "Firefighting", "Low current", "Architectural", "Structural Concrete", "Elevators", "Security", "Civil", "Finishing", "كهرباء", "ميكانيكا", "سباكة", "تكييف", "حريق", "أمن", "إنشائي".
- Summary / total / abstract / "dry cost" rows of any kind.
- Anything that names a system, scope, or category instead of one buyable item.

Rule of thumb: if you cannot name a unit and physically hand the item over (a piece, a box, a length, a unit of equipment), REJECT it. A word that describes a field of work, a system, or a section of the BOQ is NEVER a product.
Reject totals, headings, general requirements, installation-only, testing, commissioning, supervision, mobilization, civil works, labor, and vague rows.
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
            ? 'The input is an image of a BOQ table. OCR every cell carefully — read the quantity/count column (العدد, الكمية, Qty) and extract the EXACT numeric value from each row.'
            : 'The input is a BOQ document/spreadsheet.';

        return trim(<<<PROMPT
You are a procurement specialist. Your task is to extract ONLY tangible, physical supply products from this Bill of Quantities (BOQ).

{$contextLine}
{$sourceHint}

CRITICAL â€” MERGE WRAPPED / SPLIT ROWS INTO ONE ITEM:
BOQ tables extracted from PDF often break ONE item's long description across several
lines/rows. These continuation lines repeat the SAME quantity, unit and category, and
their text is a grammatical continuation of the previous line (it starts mid-sentence,
lowercase, or with "the/and/-", or is a spec/instruction clause). You MUST reconstruct
the original single item:
- Treat consecutive rows that share the SAME quantity + unit + category as ONE product
  when the later rows are clearly continuation text of the first, NOT new products.
- Emit ONE item with the merged description and the price counted ONCE. Do NOT repeat
  the same quantity×price for every wrapped line — that multiplies the total wrongly.
- Example: rows "The asphalt flooring is to be supplied" / "installed in accordance" /
  "standard road specifications..." all with 7,100 m2 are ONE asphalt item, not 3+.

CRITICAL â€” UNDERSTAND INTENT, DO NOT COPY ROWS LITERALLY:
Your job is to output WHAT A SUPPLIER MUST ACTUALLY DELIVER, not a copy of the row text.
Read each description for MEANING and decide how many real, separately-buyable products it
implies. One BOQ row can imply SEVERAL products, or a spread of rows can imply ONE product.
- SPLIT a row into multiple items when it lists distinct products that a supplier would
  buy/price separately — even when joined by commas, "with", "and", "including", "c/w",
  "consisting of", "comprising", or ":" then a list.
  Example: "Fire pump set consisting of: fire pump, electric motor, control panel, diesel
  engine" → FOUR items: (1) fire pump, (2) electric motor, (3) control panel, (4) diesel
  engine — each with its own quantity and priced on its own.
  Example: "Wash basin complete with mixer tap and bottle trap" → THREE items: wash basin,
  mixer tap, bottle trap.
- Give each split product the correct quantity implied by the text (usually the parent
  quantity, unless the text states a different count for a sub-item). Each product is
  priced individually; the line total becomes the SUM of its products.
- DO NOT split when the extra words are just specifications, dimensions, colours, ratings,
  standards, or a single product's integral parts. "Steel door 900x2100 with vision panel
  and SS ironmongery" is usually ONE door product — vision panel and ironmongery are part
  of that door, not separately-bought items. Use judgement: would a supplier issue a
  separate price line for it? If no, keep it as one.
- Balanced approach: split only when genuinely separate products are present; when in doubt
  about whether something is a separate product vs. an accessory/spec of the main one, keep
  it as a single item. Do not invent products that are not in the text.

STRICT RULES â€” include ONLY items that are ALL of the following:
1. A specific, concrete, tangible product that can be boxed up by a supplier and delivered to site — something you can point at and say "this exact thing is being bought" (e.g. "LED luminaire 36W", "PVC pipe 110mm", "Distribution board 12-way", "CO2 fire extinguisher 6kg").
2. Has a real quantity (not 0 or "varies" with no meaning).
3. Has enough description to identify what the product is.
4. Is an actual PRODUCT, not a sentence, instruction, or specification clause. REJECT rows that are prose such as "the supervising engineer's instructions", "The price includes all materials, labor", "Measurement will be based on the geometric cubic meter", "necessary steps to complete the work", "soil-contacting elements", "in compliance", "everything necessary". These are description fragments, not buyable items.

NEVER extract abstract concepts, disciplines, trades, systems, or section/category names — they are NOT products even when they appear as their own row:
- Bare discipline/trade/system words such as: "Electrical", "Mechanical", "Plumbing", "HVAC", "Firefighting", "Low current", "Architectural", "Structural Concrete", "Elevators", "Security", "Civil", "Finishing", and their Arabic equivalents ("كهرباء", "ميكانيكا", "سباكة", "تكييف", "حريق", "أمن", "إنشائي", "تشطيبات").
- Test: if the word names a FIELD OF WORK or a SYSTEM rather than one buyable item with a unit (pcs, m, set, kg…), REJECT it.

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

Emit ONE JSON item PER real product. A single source row may therefore produce several
items (when it lists multiple products) or several rows may collapse into one item (when
they are wrapped fragments of the same product). The "items" array is your final delivery
list, not a row-by-row transcript.

For each valid physical product return JSON with:
- description: clean product name only (strip "supply and install", "supply and fix", installation clauses, floor/spec suffixes). For a split product, use just that product's own name.
- quantity: the EXACT numeric value from the quantity/count column (العدد, الكمية, Qty, Quantity, No.). NEVER default to 1 unless the quantity column is truly absent or empty.
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
            'error'               => $this->userFacingError($message, $serviceUnavailable),
            'service_unavailable' => $serviceUnavailable,
        ];
    }

    /**
     * Translate any internal error into a neutral, user-safe message.
     *
     * The user must never see the name of the underlying AI/processing engine
     * (DeepSeek, Groq, Gemini, Vision, etc.) or configuration hints like API
     * keys. The detailed message is kept in the logs for debugging only.
     */
    private function userFacingError(string $message, bool $serviceUnavailable = false): string
    {
        // Detect any reference to an AI provider / engine / API config so we can
        // swap it for a generic, branded-neutral message.
        $sensitive = '/deepseek|groq|gemini|vision|openai|openrouter|api[\s_-]?key|\.env|non-?json|token limit/i';

        if (preg_match($sensitive, $message)) {
            Log::warning('QuotationAiService: suppressed technical error from user.', ['internal' => $message]);

            return $serviceUnavailable
                ? 'The file could not be processed right now. Please try again in a moment.'
                : 'We could not read this file automatically. Please make sure it is a clear BOQ (Excel, CSV, PDF, or image) and try again, or add the items manually.';
        }

        return $message;
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
            'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'         => 'application/msword',
            default       => 'application/octet-stream',
        };
    }
}
