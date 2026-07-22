<?php

namespace App\Console\Commands\Catalog;

use App\Models\Catalog\Research\ResearchJob;
use App\Models\Catalog\Research\ResearchJobResult;
use App\Services\Catalog\Research\Contracts\ResearchResultPersister;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use App\Services\Catalog\Research\DeepSeek\ResearchResponseParser;
use App\Services\Catalog\Research\ResearchProgressService;
use Illuminate\Console\Command;

/**
 * Re-runs persistence over EXISTING research results using the raw responses
 * already stored — no new DeepSeek calls, so it costs nothing. Use this after
 * deploying parser/persister fixes to salvage results that were produced by the
 * old code (stuck in awaiting_validation or marked invalid over a shape/enum
 * mismatch the new parser now handles).
 *
 *   php artisan catalog:reprocess-results          # re-parse + persist all
 *   php artisan catalog:reprocess-results --only-invalid
 */
class ReprocessResearchResults extends Command
{
    protected $signature = 'catalog:reprocess-results
        {--only-invalid : Only reprocess results previously marked invalid}
        {--limit=0 : Max results to process (0 = all)}';

    protected $description = 'Re-parse and persist stored research results with the current parser (no new AI calls).';

    public function handle(
        ResearchResponseParser  $parser,
        ResearchResultPersister $persister,
        ResearchProgressService $progress,
    ): int {
        $query = ResearchJobResult::query()->whereNotNull('raw_response')->orderBy('id');

        if ($this->option('only-invalid')) {
            $query->where('validation_status', '!=', 'valid');
        }
        if (($limit = (int) $this->option('limit')) > 0) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        $this->info("Reprocessing {$total} research result(s)…");

        $revalidated = $persisted = $skipped = 0;
        $bar = $this->output->createProgressBar($total);

        $query->chunkById(100, function ($results) use ($parser, $persister, $progress, &$revalidated, &$persisted, &$skipped, $bar) {
            foreach ($results as $result) {
                $bar->advance();

                $response = $parser->parse((string) $result->raw_response);

                if (! $response->valid) {
                    $result->update([
                        'validation_status' => 'invalid',
                        'validation_errors' => $response->validationErrors,
                    ]);
                    $skipped++;
                    continue;
                }

                $revalidated++;

                $job = ResearchJob::find($result->research_job_id);
                if (! $job || ! $job->family) {
                    $result->update(['validation_status' => 'valid', 'validation_errors' => null]);
                    continue;
                }

                try {
                    $counts = $persister->persist($job, ResearchResponse::valid($response->data, (string) $result->raw_response));
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->warn("Job {$job->id}: persist failed — {$e->getMessage()}");
                    continue;
                }

                $result->update([
                    'validation_status' => 'valid',
                    'validation_errors' => null,
                    'accepted_count'    => $counts['accepted'],
                    'rejected_count'    => $counts['rejected'],
                    'duplicate_count'   => $counts['duplicate'],
                    'discovered_count'  => $result->discovered_count ?: array_sum($counts),
                ]);

                $job->update(['status' => 'completed', 'completed_at' => now()]);
                $progress->refreshFamilyStatus($job->family);

                $persisted += $counts['accepted'];
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Revalidated: {$revalidated} | still invalid: {$skipped} | variants persisted: {$persisted}");

        return self::SUCCESS;
    }
}
