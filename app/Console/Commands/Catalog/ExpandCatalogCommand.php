<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\Catalog\Research\SweepManufacturerCatalogJob;
use App\Models\Catalog\Research\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Drives Deep Catalog Expansion: sweep each manufacturer's published catalog
 * so the variant count grows from real, sourced products.
 *
 * Defaults are deliberately small — expansion costs API calls, so the first
 * run should be a visible, bounded trial rather than 2,855 manufacturers at
 * once.
 */
class ExpandCatalogCommand extends Command
{
    protected $signature = 'catalog:expand
                            {--manufacturers=10 : How many manufacturers to sweep}
                            {--categories=1 : Categories per manufacturer}
                            {--per-page=10 : Series requested per page}
                            {--max-pages=5 : Page cap per manufacturer/category}
                            {--min-variants=0 : Only sweep makers with at least this many variants}
                            {--dry-run : Show the plan without dispatching}';

    protected $description = 'Expand the catalog by sweeping manufacturers\' published product ranges';

    public function handle(): int
    {
        $limit       = (int) $this->option('manufacturers');
        $perCategory = (int) $this->option('categories');
        $perPage     = (int) $this->option('per-page');
        $maxPages    = (int) $this->option('max-pages');
        $minVariants = (int) $this->option('min-variants');
        $dry         = (bool) $this->option('dry-run');

        $catalog = DB::connection('catalog');

        $this->components->info('Deep Catalog Expansion');
        $this->line(sprintf('  Manufacturers in catalog : <info>%d</info>', Manufacturer::count()));
        $this->line(sprintf('  Variants now             : <info>%d</info>', $catalog->table('product_variants')->count()));
        $this->newLine();

        // Prioritise manufacturers that already proved productive — they have
        // real published catalogs, so sweeping them yields the most real rows.
        $makers = Manufacturer::query()
            ->select('manufacturers.*')
            ->selectSub(
                $catalog->table('product_variants')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('product_variants.manufacturer_id', 'manufacturers.id'),
                'variant_count'
            )
            ->where('is_active', true)
            ->havingRaw('variant_count >= ?', [$minVariants])
            ->orderByDesc('variant_count')
            ->limit($limit)
            ->get();

        if ($makers->isEmpty()) {
            $this->components->warn('No manufacturers matched. Lower --min-variants.');

            return self::SUCCESS;
        }

        $queue      = config('catalog_research.queue', 'catalog-research');
        $dispatched = 0;

        foreach ($makers as $maker) {
            $categories = $this->categoriesFor($maker->id, $perCategory);

            if ($categories === []) {
                continue;
            }

            foreach ($categories as $category) {
                $this->line(sprintf(
                    '  %s <comment>%s</comment> — %s',
                    str_pad(mb_substr($maker->name, 0, 28), 30),
                    str_pad((string) $maker->variant_count, 5, ' ', STR_PAD_LEFT),
                    $category
                ));

                if (! $dry) {
                    SweepManufacturerCatalogJob::dispatch(
                        $maker->id, $category, 1, $perPage, $maxPages
                    )->onQueue($queue);

                    $dispatched++;
                }
            }
        }

        $this->newLine();

        if ($dry) {
            $this->components->info('Dry run — nothing dispatched.');

            return self::SUCCESS;
        }

        $this->components->info("Dispatched {$dispatched} sweeps to the '{$queue}' queue.");
        $this->line('  Run a worker to process them:');
        $this->line("    <comment>php artisan queue:work --queue={$queue} --stop-when-empty</comment>");

        // Make the cost visible up front rather than after the bill.
        $calls = $dispatched * $maxPages;
        $this->newLine();
        $this->components->warn(sprintf(
            'Up to %d API calls if every sweep runs to its page cap (%d sweeps x %d pages).',
            $calls, $dispatched, $maxPages
        ));

        return self::SUCCESS;
    }

    /**
     * Categories this manufacturer already appears in — sweeping a maker inside
     * a category it actually serves keeps the prompt grounded.
     *
     * @return list<string>
     */
    private function categoriesFor(int $manufacturerId, int $limit): array
    {
        return DB::connection('catalog')
            ->table('product_family_manufacturers as pfm')
            ->join('product_families as f', 'f.id', '=', 'pfm.product_family_id')
            ->where('pfm.manufacturer_id', $manufacturerId)
            ->orderByDesc('f.id')
            ->limit($limit)
            ->pluck('f.name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
