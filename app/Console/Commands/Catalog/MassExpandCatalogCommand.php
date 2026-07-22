<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\Catalog\Research\SweepManufacturerCatalogJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Bulk expansion: sweep every productive manufacturer across every category
 * it serves, so the catalog grows toward six/seven figures.
 *
 * Differs from catalog:expand in scale and resumability — it walks the whole
 * manufacturer list, covers all of a maker's categories rather than one, and
 * skips pairs that were already swept so it can be run repeatedly until the
 * catalog stops growing.
 */
class MassExpandCatalogCommand extends Command
{
    protected $signature = 'catalog:expand-mass
                            {--min-variants=5 : Only makers with at least this many variants}
                            {--categories=10 : Categories per manufacturer}
                            {--per-page=25 : Series requested per API call}
                            {--max-pages=10 : Page cap per manufacturer/category}
                            {--limit=0 : Cap total sweeps (0 = no cap)}
                            {--skip-done : Skip manufacturer/category pairs already swept}
                            {--dry-run : Show the plan and cost without dispatching}';

    protected $description = 'Mass-expand the catalog across all productive manufacturers and categories';

    public function handle(): int
    {
        $minVariants = (int) $this->option('min-variants');
        $perMaker    = (int) $this->option('categories');
        $perPage     = (int) $this->option('per-page');
        $maxPages    = (int) $this->option('max-pages');
        $cap         = (int) $this->option('limit');
        $skipDone    = (bool) $this->option('skip-done');
        $dry         = (bool) $this->option('dry-run');

        $catalog = DB::connection('catalog');
        $queue   = config('catalog_research.queue', 'default');

        $this->components->info('Mass Catalog Expansion');
        $this->line(sprintf('  Variants now : <info>%s</info>', number_format($catalog->table('product_variants')->count())));

        // Only makers that have proved they have a real, findable catalog.
        $makers = $catalog->table('manufacturers as m')
            ->selectRaw('m.id, m.name, m.official_website, '
                . '(SELECT COUNT(*) FROM product_variants v WHERE v.manufacturer_id = m.id) AS variant_count')
            ->where('m.is_active', true)
            ->whereNotNull('m.official_website')
            ->where('m.official_website', '!=', '')
            ->whereRaw('CHAR_LENGTH(m.name) >= 4')
            ->havingRaw('variant_count >= ?', [$minVariants])
            ->orderByDesc('variant_count')
            ->get();

        $this->line(sprintf('  Eligible makers : <info>%d</info>', $makers->count()));

        // Pairs already swept, so repeat runs extend coverage instead of
        // paying again for the same ground.
        $done = [];
        if ($skipDone) {
            $done = $catalog->table('research_jobs')
                ->where('job_type', 'manufacturer_catalog_sweep')
                ->pluck('research_query')
                ->map(fn ($q) => $this->pairKeyFromQuery((string) $q))
                ->filter()->flip()->all();

            $this->line(sprintf('  Already swept   : <info>%d</info> pairs', count($done)));
        }

        $plan = [];

        foreach ($makers as $maker) {
            foreach ($this->categoriesFor((int) $maker->id, $perMaker) as $category) {
                if ($skipDone && isset($done[$this->pairKey($maker->name, $category)])) {
                    continue;
                }

                $plan[] = [$maker, $category];

                if ($cap > 0 && count($plan) >= $cap) {
                    break 2;
                }
            }
        }

        if ($plan === []) {
            $this->components->warn('Nothing left to sweep with these filters.');

            return self::SUCCESS;
        }

        // Cost and time must be visible BEFORE spending, not after.
        $calls   = count($plan) * $maxPages;
        $minutes = (int) round($calls * 6 / 60);

        $this->newLine();
        $this->components->info('Plan');
        $this->line(sprintf('  Sweeps          : <info>%s</info>', number_format(count($plan))));
        $this->line(sprintf('  Max API calls   : <info>%s</info> (%d sweeps x %d pages)', number_format($calls), count($plan), $maxPages));
        $this->line(sprintf('  Est. products   : <info>~%s</info> (at ~%d per call)', number_format((int) ($calls * $perPage * 0.4)), (int) ($perPage * 0.4)));
        $this->line(sprintf('  Est. time       : ~%s hours on one worker', number_format($minutes / 60, 1)));
        $this->line(sprintf('  Est. cost       : ~$%s', number_format($calls * 0.003, 2)));

        if ($dry) {
            $this->newLine();
            $this->components->info('Dry run — nothing dispatched.');
            foreach (array_slice($plan, 0, 15) as [$maker, $category]) {
                $this->line(sprintf('  %s <comment>%s</comment>', str_pad(mb_substr($maker->name, 0, 26), 28), mb_substr($category, 0, 40)));
            }
            if (count($plan) > 15) {
                $this->line('  … and ' . number_format(count($plan) - 15) . ' more');
            }

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($plan));
        $bar->start();

        foreach ($plan as [$maker, $category]) {
            SweepManufacturerCatalogJob::dispatch((int) $maker->id, $category, 1, $perPage, $maxPages)
                ->onQueue($queue);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->components->info(sprintf('Dispatched %s sweeps to "%s".', number_format(count($plan)), $queue));
        $this->line('  Run several workers in parallel to finish sooner:');
        $this->line("    <comment>php artisan queue:work --queue={$queue} --stop-when-empty --timeout=900 --tries=1</comment>");
        $this->line('  (open 5–10 terminals, or use a process manager, for 5–10x throughput)');

        return self::SUCCESS;
    }

    /**
     * All categories a manufacturer serves — sweeping every one is what turns
     * a maker with 400 variants into one with thousands.
     *
     * @return list<string>
     */
    private function categoriesFor(int $manufacturerId, int $limit): array
    {
        return DB::connection('catalog')
            ->table('product_family_manufacturers as pfm')
            ->join('product_families as f', 'f.id', '=', 'pfm.product_family_id')
            ->where('pfm.manufacturer_id', $manufacturerId)
            ->distinct()
            ->orderBy('f.name')
            ->limit($limit)
            ->pluck('f.name')
            ->filter()
            ->values()
            ->all();
    }

    private function pairKey(string $maker, string $category): string
    {
        return mb_strtolower(trim($maker) . '|' . trim($category));
    }

    /** research_query is stored as "Maker — Category (page N)". */
    private function pairKeyFromQuery(string $query): ?string
    {
        if (! str_contains($query, '—')) {
            return null;
        }

        [$maker, $rest] = explode('—', $query, 2);
        $category = trim(preg_replace('/\(page \d+\)\s*$/', '', $rest) ?? '');

        return $category === '' ? null : $this->pairKey(trim($maker), $category);
    }
}
