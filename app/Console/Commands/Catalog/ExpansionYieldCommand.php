<?php

namespace App\Console\Commands\Catalog;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Explains why expansion is or is not adding products.
 *
 * The variant count alone cannot distinguish "the model returned nothing" from
 * "it returned products we already had" — and those call for opposite fixes.
 * This separates them.
 */
class ExpansionYieldCommand extends Command
{
    protected $signature   = 'catalog:expansion-yield {--recent=50}';
    protected $description = 'Diagnose the real yield of catalog expansion sweeps';

    public function handle(): int
    {
        $catalog = DB::connection('catalog');
        $recent  = (int) $this->option('recent');

        $this->components->info('Expansion yield');

        // Overall sweep outcomes
        $sweeps = $catalog->table('research_jobs')
            ->where('job_type', 'manufacturer_catalog_sweep')
            ->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');

        $this->line('  Sweeps: ' . json_encode($sweeps));

        // Discovered vs accepted across recent results
        $results = $catalog->table('research_job_results as r')
            ->join('research_jobs as j', 'j.id', '=', 'r.research_job_id')
            ->where('j.job_type', 'manufacturer_catalog_sweep')
            ->orderByDesc('r.id')->limit($recent)
            ->get(['r.discovered_count', 'r.accepted_count', 'r.duplicate_count', 'r.rejected_count', 'r.validation_status']);

        $discovered = $results->sum('discovered_count');
        $accepted   = $results->sum('accepted_count');
        $duplicate  = $results->sum('duplicate_count');
        $rejected   = $results->sum('rejected_count');
        $invalid    = $results->where('validation_status', 'invalid')->count();

        $this->newLine();
        $this->line(sprintf('  Last %d results:', $results->count()));
        $this->line(sprintf('    discovered : <info>%d</info>', $discovered));
        $this->line(sprintf('    accepted   : <info>%d</info>', $accepted));
        $this->line(sprintf('    duplicates : <comment>%d</comment>', $duplicate));
        $this->line(sprintf('    rejected   : <comment>%d</comment>', $rejected));
        $this->line(sprintf('    invalid    : <comment>%d</comment>', $invalid));

        // The verdict is what matters: which lever to pull next.
        $this->newLine();
        $this->components->info('Verdict');

        if ($discovered === 0) {
            $this->line('  The model returned NO products. Either the categories are too narrow,');
            $this->line('  or these manufacturers have no catalog it can enumerate.');
            $this->line('  → Try different manufacturers, or broader categories.');
        } elseif ($duplicate > $accepted) {
            $this->line('  Most results were products we ALREADY have — expansion has saturated');
            $this->line('  these manufacturer/category pairs.');
            $this->line('  → Sweep NEW manufacturers or NEW categories; re-sweeping adds nothing.');
        } elseif ($accepted > 0 && $accepted >= $discovered * 0.5) {
            $this->line('  Healthy yield — most discovered products were accepted.');
            $this->line('  → Keep expanding; scale up the number of sweeps.');
        } else {
            $this->line('  Products were discovered but mostly not accepted (no source, or');
            $this->line('  failed verification).');
            $this->line('  → Quality issue, not a volume issue. Inspect a rejected result.');
        }

        // Coverage: how much of the eligible ground has been swept at all
        $swept = $catalog->table('research_jobs')
            ->where('job_type', 'manufacturer_catalog_sweep')
            ->distinct()->count('manufacturer_id');

        $eligible = $catalog->table('manufacturers')
            ->where('is_active', true)
            ->whereNotNull('official_website')->where('official_website', '!=', '')
            ->count();

        $this->newLine();
        $this->line(sprintf(
            '  Manufacturers swept: <info>%d</info> of <info>%d</info> with a website (%.1f%%)',
            $swept, $eligible, $eligible > 0 ? $swept / $eligible * 100 : 0
        ));

        if ($swept < $eligible) {
            $this->line('  → ' . number_format($eligible - $swept) . ' manufacturers never swept. Room to grow:');
            $this->line('    <comment>php artisan catalog:expand-mass --min-variants=1 --skip-done</comment>');
        }

        return self::SUCCESS;
    }
}
