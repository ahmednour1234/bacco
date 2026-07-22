<?php

namespace App\Services\Catalog\Research;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Thin wrapper over PhpSpreadsheet (no maatwebsite/excel — it is not installed
 * in this project). Reads sheet names, a small preview, and streams data rows.
 *
 * All reads use setReadDataOnly(true) and cap columns so heavily-formatted
 * workbooks cannot blow up memory.
 */
class ExcelReaderService
{
    /** Hard cap on columns read from any sheet. */
    private const MAX_COLUMNS = 40;

    /** Header-ish tokens used to auto-detect which row is the header row. */
    private const HEADER_HINTS = [
        'qimta', 'code', 'division', 'category', 'item', 'description', 'product',
        'material', 'connection', 'pressure', 'rating', 'size', 'unit',
        'manufacturer', 'maker', 'brand', 'standard', 'approval', 'type',
    ];

    /** @return list<string> */
    public function sheetNames(string $absPath): array
    {
        $reader = $this->makeReader($absPath);
        $reader->setReadDataOnly(true);

        // listWorksheetNames avoids loading cell data just to enumerate tabs.
        return $reader->listWorksheetNames($absPath);
    }

    /**
     * Guess the header row by scanning the first $scan rows and scoring each by
     * how many of its non-empty cells look like column labels. Qimta workbooks
     * put a title/summary banner above the real header, so assuming row 1 is
     * wrong — this finds the actual header row instead.
     */
    public function detectHeaderRow(string $absPath, ?string $sheetName = null, int $scan = 15): int
    {
        $reader = $this->makeReader($absPath);
        $reader->setReadDataOnly(true);
        if ($sheetName !== null) {
            $reader->setLoadSheetsOnly([$sheetName]);
        }
        $spreadsheet = $reader->load($absPath);
        $sheet       = ($sheetName !== null ? $spreadsheet->getSheetByName($sheetName) : $spreadsheet->getSheet(0))
            ?? $spreadsheet->getActiveSheet();

        $highestRow    = min($sheet->getHighestDataRow(), $scan);
        $highestColIdx = min(Coordinate::columnIndexFromString($sheet->getHighestDataColumn()), self::MAX_COLUMNS);

        $bestRow   = 0;
        $bestScore = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $labelCells = 0; // short, label-like cells
            $hits       = 0; // cells that look like a known header
            $longCells  = 0; // sentence/paragraph cells (title/summary banners)

            for ($col = 1; $col <= $highestColIdx; $col++) {
                $val = strtolower(trim($this->cellToString($sheet->getCell([$col, $row])->getValue())));
                if ($val === '') {
                    continue;
                }

                $wordCount = str_word_count($val);

                // A genuine header cell is short (≤ 5 words) with no sentence
                // punctuation. Long cells are titles/summaries — penalise them.
                if (mb_strlen($val) > 60 || $wordCount > 6 || str_contains($val, '|') || str_contains($val, ':')) {
                    $longCells++;
                    continue;
                }

                $labelCells++;

                foreach (self::HEADER_HINTS as $hint) {
                    if (str_contains($val, $hint)) {
                        $hits++;
                        break;
                    }
                }
            }

            // Require several short label cells AND some keyword hits; subtract
            // heavily for long banner cells so a title row can never win.
            $score = ($hits * 10) + $labelCells - ($longCells * 20);

            if ($labelCells >= 3 && $hits >= 2 && $score > $bestScore) {
                $bestScore = $score;
                $bestRow   = $row;
            }
        }

        $spreadsheet->disconnectWorksheets();

        // Fall back to row 1 only if nothing looked like a header.
        return $bestRow ?: 1;
    }

    /**
     * First $limit rows of a sheet (default the whole first sheet) as a grid,
     * for the mapping UI preview. Row 0 is assumed to be (or contain) headers —
     * the caller decides which row is the header.
     *
     * @return array{sheet:string, headers:list<string>, rows:list<list<string>>}
     */
    public function preview(string $absPath, ?string $sheetName = null, int $limit = 20, int $headerRow = 1): array
    {
        $reader = $this->makeReader($absPath);
        $reader->setReadDataOnly(true);
        if ($sheetName !== null) {
            $reader->setLoadSheetsOnly([$sheetName]);
        }
        $spreadsheet = $reader->load($absPath);
        $sheet       = $sheetName !== null
            ? $spreadsheet->getSheetByName($sheetName)
            : $spreadsheet->getSheet(0);

        $sheet ??= $spreadsheet->getActiveSheet();

        $highestRow    = $sheet->getHighestDataRow();
        $highestColIdx = min(
            Coordinate::columnIndexFromString($sheet->getHighestDataColumn()),
            self::MAX_COLUMNS
        );

        $headers  = [];
        $rows     = [];
        $lastRow  = min($highestRow, $headerRow + $limit);

        for ($row = $headerRow; $row <= $lastRow; $row++) {
            $line = [];
            for ($col = 1; $col <= $highestColIdx; $col++) {
                $line[] = trim($this->cellToString($sheet->getCell([$col, $row])->getValue()));
            }

            if ($row === $headerRow) {
                $headers = $line;
            } else {
                $rows[] = $line;
            }
        }

        $title = $sheet->getTitle();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return ['sheet' => $title, 'headers' => $headers, 'rows' => $rows];
    }

    /**
     * Stream every data row of a sheet to $handler as an associative array
     * keyed by the header cells. Memory-safe for large files.
     *
     * @param  callable(array<string,string>, int $excelRowNumber): void  $handler
     */
    public function eachRow(string $absPath, string $sheetName, int $headerRow, callable $handler): void
    {
        $reader = $this->makeReader($absPath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);
        $spreadsheet = $reader->load($absPath);
        $sheet       = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();

        $highestRow    = $sheet->getHighestDataRow();
        $highestColIdx = min(
            Coordinate::columnIndexFromString($sheet->getHighestDataColumn()),
            self::MAX_COLUMNS
        );

        // Read the header row.
        $headers = [];
        for ($col = 1; $col <= $highestColIdx; $col++) {
            $headers[$col] = trim($this->cellToString($sheet->getCell([$col, $headerRow])->getValue()));
        }

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $assoc    = [];
            $hasValue = false;

            for ($col = 1; $col <= $highestColIdx; $col++) {
                $header = $headers[$col];
                if ($header === '') {
                    continue;
                }
                $value = trim($this->cellToString($sheet->getCell([$col, $row])->getValue()));
                if ($value !== '') {
                    $hasValue = true;
                }
                $assoc[$header] = $value;
            }

            if ($hasValue) {
                $handler($assoc, $row);
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function makeReader(string $absPath)
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        $type = match ($ext) {
            'csv' => 'Csv',
            'xls' => 'Xls',
            default => 'Xlsx',
        };

        return IOFactory::createReader($type);
    }

    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }
}
