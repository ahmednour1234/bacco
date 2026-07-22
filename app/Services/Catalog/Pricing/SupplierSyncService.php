<?php

namespace App\Services\Catalog\Pricing;

use App\Models\Catalog\Pricing\CatalogSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Turns scraper sources into catalog suppliers, merging duplicates.
 *
 * The scraper DB lists the same merchant more than once (elburoj, kmco and
 * zorins each appear twice under different casing). Left alone that would give
 * one product two "different" prices from what is really one shop, quietly
 * corrupting any cheapest-price comparison. Sources are therefore keyed on the
 * normalized host of base_url, and every source id that shares a host maps to
 * the same supplier.
 */
class SupplierSyncService
{
    /**
     * Sync all active scraper sources into suppliers.
     *
     * @return array{created:int, merged:int, skipped:int, map:array<int,int>}
     *         map = scraper_source_id => supplier_id
     */
    public function sync(): array
    {
        $sources = $this->fetchScraperSources();

        $created = 0;
        $merged  = 0;
        $skipped = 0;
        $map     = [];

        // Group source ids by normalized host so duplicates collapse together.
        $byHost = [];
        foreach ($sources as $source) {
            $host = CatalogSupplier::normalizeHost($source->base_url ?? null);
            if ($host === '') {
                $skipped++;
                continue;
            }
            $byHost[$host][] = $source;
        }

        foreach ($byHost as $host => $group) {
            // Prefer the best-looking display name in the group: longest name
            // usually carries proper casing ("El Buroj" over "elburoj").
            usort($group, fn ($a, $b) => mb_strlen((string) $b->name) <=> mb_strlen((string) $a->name));
            $primary = $group[0];

            $supplier = CatalogSupplier::firstOrNew(['normalized_name' => $host]);
            $isNew    = ! $supplier->exists;

            $supplier->fill([
                'name'         => $primary->name ?: $host,
                'slug'         => Str::slug($host),
                'website'      => $primary->base_url,
                'country_code' => $this->guessCountry($host, $primary->base_url ?? ''),
                'is_active'    => true,
            ]);

            // Keep the lowest source id as the canonical link; the full set is
            // recorded in notes so the merge stays auditable.
            $ids = array_map(fn ($s) => (int) $s->id, $group);
            sort($ids);
            $supplier->scraper_source_id = $ids[0];

            if (count($ids) > 1) {
                $supplier->notes = 'Merged scraper sources: ' . implode(', ', $ids);
                $merged++;
            }

            $supplier->save();

            foreach ($ids as $id) {
                $map[$id] = $supplier->id;
            }

            $isNew ? $created++ : null;
        }

        Log::info('Supplier sync finished.', [
            'sources' => count($sources),
            'created' => $created,
            'merged'  => $merged,
            'skipped' => $skipped,
        ]);

        return ['created' => $created, 'merged' => $merged, 'skipped' => $skipped, 'map' => $map];
    }

    /**
     * Map every scraper source id to its supplier id, without re-syncing.
     * Used by the matcher to attach prices to the merged supplier.
     *
     * @return array<int,int>
     */
    public function sourceToSupplierMap(): array
    {
        $map = [];

        foreach (CatalogSupplier::query()->get(['id', 'normalized_name', 'notes', 'scraper_source_id']) as $supplier) {
            if ($supplier->scraper_source_id) {
                $map[(int) $supplier->scraper_source_id] = $supplier->id;
            }

            // Re-read the merged ids recorded during sync.
            if ($supplier->notes && preg_match('/Merged scraper sources:\s*([\d,\s]+)/', $supplier->notes, $m)) {
                foreach (preg_split('/[,\s]+/', trim($m[1])) as $id) {
                    if ($id !== '') {
                        $map[(int) $id] = $supplier->id;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Read sources from the scraper connection defensively — the column set
     * there is outside our control (it has no `type` column, for instance).
     *
     * @return list<object>
     */
    private function fetchScraperSources(): array
    {
        try {
            return DB::connection('scraper')
                ->table('scraper_sources')
                ->get()
                ->all();
        } catch (\Throwable $e) {
            Log::error('Could not read scraper_sources.', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /** Cheap country hint from the domain; refined manually when it matters. */
    private function guessCountry(string $host, string $url): ?string
    {
        if (Str::endsWith($host, '.sa') || Str::contains($url, '/sa')) {
            return 'SA';
        }

        return null;
    }
}
