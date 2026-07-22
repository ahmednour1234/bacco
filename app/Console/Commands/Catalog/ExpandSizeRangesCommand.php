<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\Catalog\Research\ExpandOfficialSizeRangeJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Expands models whose officially published size range has not been read yet.
 *
 * Targets thin models first — a model with one variant usually means only its
 * range was recorded, so asking the manufacturer's own size table is where the
 * safe growth is.
 */
class ExpandSizeRangesCommand extends Command
{
    protected $signature = 'catalog:expand-sizes
                            {--models=25 : How many models to expand}
                            {--max-variants=1 : Only models with at most this many variants}
                            {--dry-run : Show the plan without dispatching}';

    protected $description = 'Expand product models using their officially published size ranges';

    public function handle(): int
    {
        $limit       = (int) $this->option('models');
        $maxVariants = (int) $this->option('max-variants');
        $dry         = (bool) $this->option('dry-run');

        $catalog = DB::connection('catalog');

        $this->components->info('Official size range expansion');
        $this->line(sprintf('  Models   : <info>%d</info>', $catalog->table('product_models')->count()));
        $this->line(sprintf('  Variants : <info>%d</info>', $catalog->table('product_variants')->count()));
        $this->newLine();

        // Models carrying few variants are the ones whose published range has
        // not been enumerated yet.
        $models = $catalog->table('product_models as m')
            ->leftJoin('manufacturers as mf', 'mf.id', '=', 'm.manufacturer_id')
            ->selectRaw('m.id, m.model_number, mf.name AS manufacturer, '
                . '(SELECT COUNT(*) FROM product_variants v WHERE v.product_model_id = m.id) AS variant_count')
            ->whereNotNull('m.model_number')
            ->havingRaw('variant_count <= ?', [$maxVariants])
            ->orderBy('m.id')
            ->limit($limit)
            ->get();

        if ($models->isEmpty()) {
            $this->components->warn('No models matched. Try raising --max-variants.');

            return self::SUCCESS;
        }

        $queue      = config('catalog_research.queue', 'default');
        $dispatched = 0;

        foreach ($models as $model) {
            $this->line(sprintf(
                '  %s %s <comment>(%d variants)</comment>',
                str_pad(mb_substr((string) ($model->manufacturer ?? '—'), 0, 24), 26),
                str_pad(mb_substr((string) $model->model_number, 0, 26), 28),
                $model->variant_count
            ));

            if (! $dry) {
                ExpandOfficialSizeRangeJob::dispatch((int) $model->id)->onQueue($queue);
                $dispatched++;
            }
        }

        $this->newLine();

        if ($dry) {
            $this->components->info('Dry run — nothing dispatched.');

            return self::SUCCESS;
        }

        $this->components->info("Dispatched {$dispatched} size expansions to '{$queue}'.");
        $this->line("  <comment>php artisan queue:work --queue={$queue} --stop-when-empty</comment>");
        $this->newLine();
        $this->components->warn(
            "About {$dispatched} API calls. Only sizes the manufacturer actually publishes " .
            'are stored — ranges are never expanded automatically.'
        );

        return self::SUCCESS;
    }
}
