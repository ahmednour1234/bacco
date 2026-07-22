<?php

namespace App\Jobs\Catalog\Pricing;

use App\Services\Catalog\Pricing\ScraperPriceMatchingService;
use App\Services\Catalog\Pricing\SupplierSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Walks scraped products and links them to catalog variants.
 *
 * Chunked and resumable: the scraper table holds tens of thousands of rows, so
 * the job tracks the last id it handled and a failure on one row never aborts
 * the sweep — the same lesson the Excel import job had to learn.
 */
class MatchScraperPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;
    public int $tries   = 1;

    /** Rows pulled from the scraper DB per chunk. */
    private const CHUNK = 200;

    public function __construct(
        public readonly ?int $sourceId  = null,
        public readonly ?int $limit     = null,
        public readonly bool $pricedOnly = true,
    ) {}

    public function handle(
        ScraperPriceMatchingService $matcher,
        SupplierSyncService $suppliers,
    ): void {
        // Make sure suppliers exist and duplicate sources are merged first,
        // otherwise prices would attach to the wrong (or no) supplier.
        $suppliers->sync();
        $supplierMap = $suppliers->sourceToSupplierMap();

        $lastId    = 0;
        $seen      = 0;
        $matched   = 0;
        $accepted  = 0;
        $pending   = 0;
        $failed    = 0;

        while (true) {
            $rows = $this->fetchChunk($lastId);

            if ($rows === []) {
                break;
            }

            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                $seen++;

                try {
                    $match = $matcher->matchOne($row, $supplierMap);

                    if ($match !== null) {
                        $matched++;
                        $match->status->allowsPrice() ? $accepted++ : $pending++;
                    }
                } catch (\Throwable $e) {
                    // One bad row must never stop the sweep.
                    $failed++;
                    Log::warning('Price match failed for scraped row.', [
                        'scraper_product_id' => $row->id ?? null,
                        'message'            => $e->getMessage(),
                    ]);
                }

                if ($this->limit !== null && $seen >= $this->limit) {
                    break 2;
                }
            }
        }

        Log::info('Scraper price matching finished.', [
            'source_id' => $this->sourceId,
            'seen'      => $seen,
            'matched'   => $matched,
            'accepted'  => $accepted,
            'pending'   => $pending,
            'failed'    => $failed,
        ]);
    }

    /**
     * Keyset pagination over the scraper table — cheaper and stabler than
     * OFFSET across tens of thousands of rows.
     *
     * @return list<object>
     */
    private function fetchChunk(int $afterId): array
    {
        try {
            $query = DB::connection('scraper')
                ->table('scraper_products')
                ->where('id', '>', $afterId)
                ->orderBy('id')
                ->limit(self::CHUNK);

            if ($this->sourceId !== null) {
                $query->where('source_id', $this->sourceId);
            }

            if ($this->pricedOnly) {
                $query->whereNotNull('price');
            }

            return $query->get()->all();
        } catch (\Throwable $e) {
            Log::error('Could not read scraper_products chunk.', [
                'after_id' => $afterId,
                'message'  => $e->getMessage(),
            ]);

            return [];
        }
    }
}
