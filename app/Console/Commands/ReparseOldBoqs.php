<?php

namespace App\Console\Commands;

use App\Models\BoqItem;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\QuotationAiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReparseOldBoqs extends Command
{
    protected $signature = 'boq:reparse-old
                            {--before= : Re-parse BOQs uploaded before this date (Y-m-d). Defaults to today.}
                            {--boq-id= : Re-parse a single BOQ by ID.}
                            {--dry-run : Show what would be processed without actually running.}';

    protected $description = 'Re-parse BOQ files uploaded before a given date using the latest AI prompt.';

    public function handle(QuotationAiService $ai): int
    {
        $before  = $this->option('before') ? now()->parse($this->option('before')) : now()->startOfDay();
        $singleId = $this->option('boq-id');
        $dryRun  = $this->option('dry-run');

        $query = UploadedDocument::query()
            ->whereNotNull('boq_id')
            ->where('created_at', '<', $before)
            ->with('boq')
            ->orderBy('boq_id');

        if ($singleId) {
            $query->where('boq_id', $singleId);
        }

        // Get one doc per BOQ (the latest)
        $docs = $query->get()->groupBy('boq_id')->map(fn($g) => $g->sortByDesc('created_at')->first());

        if ($docs->isEmpty()) {
            $this->info('No BOQ documents found matching criteria.');
            return self::SUCCESS;
        }

        $this->info("Found {$docs->count()} BOQ(s) to re-parse." . ($dryRun ? ' [DRY RUN]' : ''));

        $bar = $this->output->createProgressBar($docs->count());
        $bar->start();

        $success = 0;
        $failed  = 0;

        foreach ($docs as $boqId => $doc) {
            $bar->advance();

            if ($dryRun) {
                $this->newLine();
                $this->line("  BOQ #{$boqId} → {$doc->file_path}");
                continue;
            }

            $absPath = Storage::disk('local')->path($doc->file_path);

            if (! file_exists($absPath)) {
                $this->newLine();
                $this->warn("  BOQ #{$boqId} — file not found: {$doc->file_path}");
                $failed++;
                continue;
            }

            $boq     = $doc->boq;
            $context = [
                'boq_id'        => $boqId,
                'project_name'  => $boq?->project?->name ?? '',
                'force_refresh' => true,
            ];

            $result = $ai->parseBoq($absPath, $context);

            if (! $result['success']) {
                $this->newLine();
                $this->warn("  BOQ #{$boqId} — AI failed: " . ($result['error'] ?? 'unknown'));
                $failed++;
                continue;
            }

            // Wipe old items and persist new ones
            BoqItem::where('boq_id', $boqId)->delete();

            foreach ($result['items'] as $aiItem) {
                BoqItem::create([
                    'boq_id'               => $boqId,
                    'description'          => (string) ($aiItem['description'] ?? ''),
                    'quantity'             => is_numeric($aiItem['quantity'] ?? null) ? (float) $aiItem['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($aiItem['unit'] ?? null),
                    'category'             => (string) ($aiItem['category'] ?? ''),
                    'brand'                => (string) ($aiItem['brand'] ?? ''),
                    'status'               => 'pending',
                    'engineering_required' => (bool) ($aiItem['engineering_required'] ?? false),
                    'confidence'           => is_numeric($aiItem['confidence'] ?? null) ? (float) $aiItem['confidence'] : null,
                    'unit_price'           => is_numeric($aiItem['unit_price'] ?? null) ? (float) $aiItem['unit_price'] : null,
                    'raw_data'             => $aiItem['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ]);
            }

            foreach ($result['rejected'] ?? [] as $rejItem) {
                BoqItem::create([
                    'boq_id'               => $boqId,
                    'description'          => (string) ($rejItem['description'] ?? ''),
                    'quantity'             => is_numeric($rejItem['quantity'] ?? null) ? (float) $rejItem['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($rejItem['unit'] ?? null),
                    'category'             => (string) ($rejItem['category'] ?? ''),
                    'brand'                => (string) ($rejItem['brand'] ?? ''),
                    'status'               => 'rejected',
                    'engineering_required' => false,
                    'confidence'           => null,
                    'unit_price'           => null,
                    'raw_data'             => is_array($rejItem['raw_data'] ?? null) ? $rejItem['raw_data'] : [],
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ]);
            }

            $itemCount = count($result['items']);
            $rejCount  = count($result['rejected'] ?? []);
            $this->newLine();
            $this->line("  BOQ #{$boqId} ✓  {$itemCount} items extracted, {$rejCount} rejected.");
            $success++;
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Success: {$success}  Failed: {$failed}");

        return self::SUCCESS;
    }

    private function resolveUnitId(?string $unitText): ?int
    {
        $label = trim((string) ($unitText ?? ''));
        if ($label === '') {
            return null;
        }

        return Unit::firstOrCreate(
            ['name' => $label],
            ['symbol' => mb_strtolower(mb_substr($label, 0, 20))]
        )->id;
    }
}
