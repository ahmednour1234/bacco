<?php

namespace App\Console\Commands\Catalog;

use App\Models\Catalog\Research\CatalogImport;
use App\Services\Catalog\Research\ExcelReaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Prints why a research import failed: its stored status/reason, whether the
 * file exists on disk, its sheets and header row — everything needed to
 * diagnose a "Failed / 0 rows" import without shell-diving the logs.
 *
 *   php artisan catalog:diagnose-import           # newest import
 *   php artisan catalog:diagnose-import {uuid}
 */
class DiagnoseResearchImport extends Command
{
    protected $signature = 'catalog:diagnose-import {uuid? : Import UUID (defaults to the newest)}';

    protected $description = 'Diagnose a failed Product Catalog Research import.';

    public function handle(ExcelReaderService $reader): int
    {
        $import = $this->argument('uuid')
            ? CatalogImport::where('uuid', $this->argument('uuid'))->first()
            : CatalogImport::latest()->first();

        if (! $import) {
            $this->error('No import found.');

            return self::FAILURE;
        }

        $this->info("Import #{$import->id}  ({$import->uuid})");
        $this->line("File:      {$import->original_file_name}");
        $this->line("Stored at: {$import->stored_file_path}");
        $this->line('Status:    ' . $import->status->value);
        $this->line('Reason:    ' . ($import->error_message ?? '(none recorded)'));
        $this->line('Mapping:   ' . json_encode($import->column_mapping));
        $this->newLine();

        // Does the file exist?
        $disk = config('catalog_research.storage.disk', 'local');
        $candidates = [
            Storage::disk($disk)->path($import->stored_file_path),
            storage_path('app/' . $import->stored_file_path),
            storage_path('app/private/' . $import->stored_file_path),
        ];

        $found = null;
        foreach ($candidates as $p) {
            $exists = is_file($p);
            $this->line(($exists ? '<info>[FOUND]</info> ' : '[missing] ') . $p);
            if ($exists && ! $found) {
                $found = $p;
            }
        }

        if (! $found) {
            $this->error('The uploaded file is not on disk. Re-upload it (check storage/ permissions).');

            return self::SUCCESS;
        }

        // Read sheets + first headers.
        try {
            $sheets = $reader->sheetNames($found);
            $this->newLine();
            $this->info('Sheets: ' . implode(', ', $sheets));

            $headerRow = (int) ($import->column_mapping['header_row'] ?? 1);
            $sheet     = $import->column_mapping['sheet'] ?? ($sheets[0] ?? null);
            $preview   = $reader->preview($found, $sheet, 3, $headerRow);
            $this->line("Header row {$headerRow} on sheet '{$sheet}':");
            $this->line('  ' . implode(' | ', array_filter($preview['headers'])));
            $this->line('First data row:');
            $this->line('  ' . implode(' | ', array_map(fn ($r) => implode(', ', $r), array_slice($preview['rows'], 0, 1))));
        } catch (\Throwable $e) {
            $this->error('Reading the workbook throws: ' . $e->getMessage());
            $this->line('→ The header row or sheet is probably wrong, or the file is corrupt/merged. Re-map with the correct header row.');
        }

        $this->diagnoseResearch();

        return self::SUCCESS;
    }

    /** Summarise research jobs + results so it's clear why the catalog is empty. */
    private function diagnoseResearch(): void
    {
        $this->newLine();
        $this->info('── Research pipeline ──');

        $conn = config('catalog_research.connection', 'catalog');
        $db   = \Illuminate\Support\Facades\DB::connection($conn);

        $this->line('DeepSeek API key set: ' . (config('services.deepseek.key') ? 'YES' : '<error>NO — research cannot run</error>'));
        $this->line('Provider: ' . config('catalog_research.provider', 'deepseek'));
        $this->line('Queue: ' . config('catalog_research.queue', 'default'));
        $this->newLine();

        foreach (['research_jobs' => 'status', 'research_job_results' => 'validation_status'] as $table => $col) {
            if (! $db->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $counts = $db->table($table)->selectRaw("{$col}, COUNT(*) as c")->groupBy($col)->pluck('c', $col);
            $this->line("{$table} by {$col}: " . json_encode($counts));
        }

        // Show the most recent failed/invalid result so the cause is visible.
        if ($db->getSchemaBuilder()->hasTable('research_job_results')) {
            $bad = $db->table('research_job_results')
                ->where('validation_status', '!=', 'valid')
                ->latest('id')->first();
            if ($bad) {
                $this->newLine();
                $this->warn('Latest non-valid result:');
                $this->line('  validation_errors: ' . mb_strimwidth((string) $bad->validation_errors, 0, 400, '…'));
                $this->line('  raw_response: ' . mb_strimwidth((string) $bad->raw_response, 0, 400, '…'));
            }

            $counts = $db->table('research_job_results')->selectRaw('SUM(accepted_count) a, SUM(discovered_count) d')->first();
            $this->newLine();
            $this->line('Total discovered variants: ' . ($counts->d ?? 0) . ' | accepted (persisted): ' . ($counts->a ?? 0));
        }

        foreach (['product_variants', 'source_documents', 'product_models'] as $t) {
            if ($db->getSchemaBuilder()->hasTable($t)) {
                $this->line("{$t}: " . $db->table($t)->count());
            }
        }
    }
}
