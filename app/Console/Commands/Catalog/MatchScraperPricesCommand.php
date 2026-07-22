<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\Catalog\Pricing\MatchScraperPricesJob;
use App\Models\Catalog\Pricing\CatalogSupplier;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Models\Catalog\Pricing\ScraperPriceMatch;
use App\Services\Catalog\Pricing\SupplierSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Runs (or previews) the scraped-price → catalog matching sweep.
 *
 * Defaults to a small dry run so the first thing anyone does on a live server
 * is look, not write.
 */
class MatchScraperPricesCommand extends Command
{
    protected $signature = 'catalog:match-prices
                            {--source= : Only this scraper source id}
                            {--limit=50 : Max scraped rows to process}
                            {--all : Process every priced row (ignores --limit)}
                            {--sync-only : Only sync/merge suppliers, do not match}
                            {--queue : Dispatch to the queue instead of running inline}';

    protected $description = 'Match scraped products to catalog variants and record their prices';

    public function handle(SupplierSyncService $suppliers): int
    {
        $this->components->info('Catalog price matching');

        // --- Suppliers ----------------------------------------------------
        $result = $suppliers->sync();
        $this->line(sprintf(
            '  Suppliers: <info>%d</info> created, <info>%d</info> merged, %d skipped — %d total',
            $result['created'],
            $result['merged'],
            $result['skipped'],
            CatalogSupplier::count()
        ));

        foreach (CatalogSupplier::orderBy('name')->get() as $s) {
            $merged = $s->notes && str_contains($s->notes, 'Merged') ? ' <comment>[merged]</comment>' : '';
            $this->line("    - {$s->name} ({$s->normalized_name}){$merged}");
        }

        if ($this->option('sync-only')) {
            return self::SUCCESS;
        }

        // --- Scraper reachability ----------------------------------------
        try {
            $available = DB::connection('scraper')->table('scraper_products')->whereNotNull('price')->count();
        } catch (\Throwable $e) {
            $this->components->error('Cannot reach the scraper database: ' . $e->getMessage());

            return self::FAILURE;
        }

        $limit = $this->option('all') ? null : (int) $this->option('limit');
        $this->line(sprintf('  Priced scraped rows available: <info>%d</info>', $available));
        $this->line('  Processing: ' . ($limit === null ? '<info>ALL</info>' : "<info>{$limit}</info>"));

        $sourceId = $this->option('source') !== null ? (int) $this->option('source') : null;

        if ($this->option('queue')) {
            MatchScraperPricesJob::dispatch($sourceId, $limit);
            $this->components->info('Dispatched to the queue.');

            return self::SUCCESS;
        }

        $before = ['matches' => ScraperPriceMatch::count(), 'prices' => ProductVariantPrice::count()];

        dispatch_sync(new MatchScraperPricesJob($sourceId, $limit));

        $after = ['matches' => ScraperPriceMatch::count(), 'prices' => ProductVariantPrice::count()];

        // --- Outcome ------------------------------------------------------
        $this->newLine();
        $this->components->info('Results');
        $this->line(sprintf('  Matches created: <info>%d</info>', $after['matches'] - $before['matches']));
        $this->line(sprintf('  Prices created:  <info>%d</info>', $after['prices'] - $before['prices']));

        $byStatus = ScraperPriceMatch::query()
            ->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');

        foreach ($byStatus as $status => $count) {
            $this->line("    {$status}: {$count}");
        }

        if (($after['matches'] - $before['matches']) === 0) {
            $this->newLine();
            $this->components->warn(
                'No matches. Expected while the catalog holds few variants — ' .
                'the scraper sites are electrical, so only electrical families will match.'
            );
        }

        return self::SUCCESS;
    }
}
