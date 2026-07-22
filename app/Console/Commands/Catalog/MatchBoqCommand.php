<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\Catalog\Pricing\MatchBoqItemsJob;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Services\Catalog\Pricing\BoqMatchingService;
use App\Services\Catalog\Pricing\BoqSpecParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Matches a BOQ against the catalog and reports how much of it can be priced.
 *
 * The coverage figure is the honest measure of whether this whole pipeline is
 * paying off: matched lines are ones the catalog can identify, priced lines are
 * ones it can actually quote.
 */
class MatchBoqCommand extends Command
{
    protected $signature = 'catalog:match-boq
                            {boq? : BOQ id (defaults to the newest)}
                            {--limit=0 : Only process this many lines (0 = all)}
                            {--queue : Dispatch to the queue instead of inline}
                            {--parse-only : Show what the parser reads, without matching}';

    protected $description = 'Match BOQ lines to catalog products and report pricing coverage';

    public function handle(BoqMatchingService $matcher, BoqSpecParser $parser): int
    {
        $boq = $this->argument('boq')
            ? Boq::find((int) $this->argument('boq'))
            : Boq::latest('id')->first();

        if (! $boq) {
            $this->components->error('No BOQ found.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        $this->components->info("BOQ #{$boq->id}");
        $items = BoqItem::where('boq_id', $boq->id)
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->orderBy('id')->get();

        $this->line("  Lines: <info>{$items->count()}</info>");

        // Parse-only makes it obvious whether a poor match rate is the parser's
        // fault or the catalog's — they need opposite fixes.
        if ($this->option('parse-only')) {
            $this->newLine();
            $skipped = 0;

            foreach ($items->take(30) as $item) {
                $desc = (string) $item->description;

                if (! $parser->isProductLine($desc, (float) $item->quantity, $item->unit_id !== null)) {
                    $skipped++;
                    $this->line(sprintf(
                        '  <comment>skip</comment> %-58s %s',
                        mb_substr($desc, 0, 58),
                        $item->unit_id === null ? '<comment>(no unit)</comment>' : ''
                    ));
                    continue;
                }

                $s = $parser->parse($desc, $item->brand);
                $this->line(sprintf(
                    '  <info>item</info> %-42s size=%-8s mat=%-11s conn=%-9s sku=%s',
                    mb_substr($desc, 0, 42),
                    $s['size'] ?? '-', $s['material'] ?? '-',
                    $s['connection'] ?? '-', $s['sku'] ?? '-'
                ));
            }

            // The real product count is what matters — a BOQ is mostly prose.
            $products = $items->filter(fn ($i) => $parser->isProductLine((string) $i->description, (float) $i->quantity, $i->unit_id !== null))->count();

            $this->newLine();
            $this->line(sprintf(
                '  Product lines: <info>%d</info> of %d (%d headings/clauses skipped)',
                $products, $items->count(), $items->count() - $products
            ));

            return self::SUCCESS;
        }

        if ($this->option('queue')) {
            MatchBoqItemsJob::dispatch($boq->id)->onQueue(config('catalog_research.queue', 'default'));
            $this->components->info('Dispatched to the queue.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        $matched = 0;
        foreach ($items as $item) {
            try {
                if ($matcher->matchItem($item) > 0) {
                    $matched++;
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->components->warn("Line {$item->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // --- Coverage ------------------------------------------------------
        $catalog = DB::connection('catalog');
        $ids     = $items->pluck('id')->all();

        $withMatch = $catalog->table('boq_variant_matches')
            ->whereIn('boq_item_id', $ids)->distinct()->count('boq_item_id');

        $withPrice = $catalog->table('boq_variant_matches')
            ->whereIn('boq_item_id', $ids)->whereNotNull('unit_price')
            ->distinct()->count('boq_item_id');

        // Measure against real product lines. A BOQ is mostly headings and
        // clauses, so a percentage of ALL rows understates the true coverage.
        $products = $items->filter(
            fn ($i) => $parser->isProductLine((string) $i->description, (float) $i->quantity, $i->unit_id !== null)
        )->count();

        $total = max(1, $products);

        $this->components->info('Coverage');
        $this->line(sprintf('  Rows in BOQ   : %d (<comment>%d</comment> headings/clauses skipped)', $items->count(), $items->count() - $products));
        $this->line(sprintf('  Product lines : <info>%d</info>', $products));
        $this->line(sprintf('  Lines matched : <info>%d</info> / %d (%.1f%%)', $withMatch, $products, $withMatch / $total * 100));
        $this->line(sprintf('  Lines priced  : <info>%d</info> / %d (%.1f%%)', $withPrice, $products, $withPrice / $total * 100));

        $this->newLine();

        if ($withMatch === 0) {
            $this->components->warn(
                'Nothing matched. Run with --parse-only to see whether the BOQ text is being ' .
                'read correctly, or whether the catalog simply lacks these products.'
            );
        } elseif ($withPrice === 0) {
            $this->components->warn(
                'Products were identified but none carry a price. Add supplier prices under ' .
                'Catalog → Pricing; matching cannot quote what has no price.'
            );
        } else {
            $this->line('  Review and confirm matches under Catalog → Pricing → BOQ Matching.');
        }

        return self::SUCCESS;
    }
}
