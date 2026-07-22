<?php

namespace App\Console\Commands\Catalog;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Answers one question before anyone tunes the matcher: is there any real
 * overlap between what the scraper sells and what the catalog contains?
 *
 * If the two datasets cover different product domains, no amount of matching
 * logic will produce prices — and knowing that is worth more than a smarter
 * fuzzy match.
 */
class ProbePriceOverlapCommand extends Command
{
    protected $signature   = 'catalog:probe-overlap {--samples=8}';
    protected $description = 'Measure the real overlap between scraped products and catalog variants';

    public function handle(): int
    {
        $catalog = DB::connection('catalog');
        $scraper = DB::connection('scraper');
        $samples = (int) $this->option('samples');

        // --- 1. Exact SKU overlap ----------------------------------------
        $this->components->info('SKU overlap');

        $catalogSkus = $catalog->table('product_variants')
            ->whereNotNull('manufacturer_sku')
            ->pluck('manufacturer_sku')
            ->map(fn ($x) => strtolower(trim((string) $x)))
            ->filter()->unique();

        $scrapedSkus = $scraper->table('scraper_products')
            ->whereNotNull('sku')
            ->pluck('sku')
            ->map(fn ($x) => strtolower(trim((string) $x)))
            ->filter()->unique();

        $exact = $catalogSkus->intersect($scrapedSkus);

        $this->line("  catalog SKUs : <info>{$catalogSkus->count()}</info>");
        $this->line("  scraped SKUs : <info>{$scrapedSkus->count()}</info>");
        $this->line('  exact matches: ' . $this->highlight($exact->count()));

        foreach ($exact->take($samples) as $sku) {
            $this->line("     → {$sku}");
        }

        // --- 2. Normalized SKU overlap -----------------------------------
        $strip = fn ($x) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $x));

        $normalized = $catalogSkus->map($strip)->filter()->unique()
            ->intersect($scrapedSkus->map($strip)->filter()->unique());

        $this->line('  normalized   : ' . $this->highlight($normalized->count()) . ' (ignoring dashes/spaces)');

        foreach ($normalized->take($samples) as $sku) {
            $this->line("     → {$sku}");
        }

        // --- 3. What the catalog covers ----------------------------------
        $this->newLine();
        $this->components->info('Catalog contents (top divisions)');

        $divisions = $catalog->table('product_families')
            ->selectRaw('division_id, COUNT(*) cnt')
            ->groupBy('division_id')->orderByDesc('cnt')->limit(8)->get();

        foreach ($divisions as $row) {
            $name = $catalog->table('catalog_research_divisions')
                ->where('id', $row->division_id)->value('name') ?? '(none)';
            $this->line('  ' . str_pad(mb_substr($name, 0, 40), 42) . $row->cnt);
        }

        // --- 4. What the scraper sells -----------------------------------
        $this->newLine();
        $this->components->info('Scraped products (random sample)');

        try {
            $rows = $scraper->table('scraper_products')
                ->whereNotNull('price')->inRandomOrder()->limit($samples)->get();

            foreach ($rows as $row) {
                $this->line('  ' . mb_substr((string) ($row->name ?? ''), 0, 70));
            }
        } catch (\Throwable $e) {
            $this->components->error('Could not sample scraper products: ' . $e->getMessage());
        }

        // --- Verdict ------------------------------------------------------
        $this->newLine();
        $total = $exact->count() + $normalized->count();

        if ($total === 0) {
            $this->components->warn(
                'No SKU overlap at all. The scraper covers a different product domain than the ' .
                'catalog, so scraped prices cannot be matched. Manual pricing (or new scraper ' .
                'sources for these categories) is the way forward — not a looser matcher, which ' .
                'would only invent wrong links.'
            );
        } else {
            $this->components->info("Overlap found — the matcher has something real to work with ({$total} candidates).");
        }

        return self::SUCCESS;
    }

    private function highlight(int $n): string
    {
        return $n > 0 ? "<info>{$n}</info>" : "<comment>{$n}</comment>";
    }
}
