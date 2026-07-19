<?php

namespace App\Console\Commands;

use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reports duplicate rows on a quotation, and says which kind they are.
 *
 * "Duplicates" has meant three different things while chasing this:
 *
 *   1. Distinct products that merely look alike (Beam 400x800 vs 425x700).
 *      Not a bug — the differing part is at the end of a long description.
 *   2. The same line emitted twice by two different extraction parts.
 *   3. The same line legitimately appearing twice in the source sheet
 *      (same beam on two floors), which must NOT be merged.
 *
 * This separates them instead of guessing, so a fix targets the real one.
 */
class InspectQuotationDuplicates extends Command
{
    protected $signature = 'quotation:duplicates
                            {quotation? : Quotation id or uuid; defaults to the newest}
                            {--limit=20 : How many duplicate groups to print}';

    protected $description = 'Report duplicate item rows on a quotation and classify them';

    public function handle(): int
    {
        $quotation = $this->resolveQuotation();

        if (! $quotation) {
            $this->error('Quotation not found.');
            return self::FAILURE;
        }

        $total = QuotationItem::where('quotation_request_id', $quotation->id)->count();

        $this->info("Quotation #{$quotation->id} ({$quotation->quotation_no}) — {$total} rows");
        $this->newLine();

        if ($total === 0) {
            $this->warn('No rows to inspect.');
            return self::SUCCESS;
        }

        $this->reportExactDuplicates($quotation->id);
        $this->reportDescriptionOnlyDuplicates($quotation->id);
        $this->reportNearIdentical($quotation->id);

        return self::SUCCESS;
    }

    /**
     * Rows identical on description + unit + price — what the merge targets.
     * These are the real bug: one line stored twice.
     */
    private function reportExactDuplicates(int $quotationId): void
    {
        $groups = DB::table('quotation_items')
            ->selectRaw('description, unit_id, unit_price, COUNT(*) AS copies, SUM(quantity) AS total_qty, GROUP_CONCAT(id) AS ids')
            ->where('quotation_request_id', $quotationId)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->groupBy('description', 'unit_id', 'unit_price')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('copies')
            ->limit((int) $this->option('limit'))
            ->get();

        $this->line('<fg=yellow>── Exact duplicates (description + unit + price) ──</>');

        if ($groups->isEmpty()) {
            $this->line('  none — no row is stored twice.');
            $this->newLine();
            return;
        }

        $extra = $groups->sum(fn($g) => $g->copies - 1);
        $this->line("  {$groups->count()} groups, {$extra} redundant rows.");
        $this->newLine();

        $this->table(
            ['Copies', 'Qty total', 'Ids', 'Description'],
            $groups->map(fn($g) => [
                $g->copies,
                rtrim(rtrim(number_format((float) $g->total_qty, 2, '.', ''), '0'), '.'),
                mb_strimwidth((string) $g->ids, 0, 24, '…'),
                mb_strimwidth((string) $g->description, 0, 60, '…'),
            ])->all()
        );
        $this->newLine();
    }

    /**
     * Same description but a differing unit or price.
     *
     * Deliberately NOT merged: a differing unit or a differing quoted price
     * makes these separate commercial lines. Listed so the distinction is
     * visible rather than assumed.
     */
    private function reportDescriptionOnlyDuplicates(int $quotationId): void
    {
        $groups = DB::table('quotation_items')
            ->selectRaw('description, COUNT(*) AS copies, COUNT(DISTINCT unit_id) AS units, COUNT(DISTINCT unit_price) AS prices')
            ->where('quotation_request_id', $quotationId)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->groupBy('description')
            ->havingRaw('COUNT(*) > 1 AND (COUNT(DISTINCT unit_id) > 1 OR COUNT(DISTINCT unit_price) > 1)')
            ->orderByDesc('copies')
            ->limit((int) $this->option('limit'))
            ->get();

        $this->line('<fg=yellow>── Same description, different unit or price (kept apart on purpose) ──</>');

        if ($groups->isEmpty()) {
            $this->line('  none.');
            $this->newLine();
            return;
        }

        $this->table(
            ['Copies', 'Units', 'Prices', 'Description'],
            $groups->map(fn($g) => [
                $g->copies,
                $g->units,
                $g->prices,
                mb_strimwidth((string) $g->description, 0, 60, '…'),
            ])->all()
        );
        $this->newLine();
    }

    /**
     * Rows sharing a long leading prefix — the "looks duplicated" case.
     *
     * Precast beams differ only in their trailing dimensions, so a table of
     * them reads as repetition even though every row is a distinct product.
     */
    private function reportNearIdentical(int $quotationId): void
    {
        $rows = QuotationItem::where('quotation_request_id', $quotationId)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->orderBy('description')
            ->limit(5000)
            ->pluck('description');

        $buckets = [];
        foreach ($rows as $description) {
            $prefix = mb_substr(trim((string) $description), 0, 25);
            $buckets[$prefix][] = $description;
        }

        $buckets = array_filter($buckets, fn($g) => count($g) > 1);
        uasort($buckets, fn($a, $b) => count($b) <=> count($a));

        $this->line('<fg=yellow>── Similar-looking rows (same 25-char prefix, all distinct) ──</>');

        if ($buckets === []) {
            $this->line('  none.');
            $this->newLine();
            return;
        }

        $shown = array_slice($buckets, 0, 5, true);

        foreach ($shown as $prefix => $group) {
            $unique = count(array_unique($group));
            $this->line(sprintf('  <fg=cyan>%s…</> — %d rows, %d distinct', $prefix, count($group), $unique));

            foreach (array_slice(array_unique($group), 0, 4) as $description) {
                $this->line('      ' . mb_strimwidth((string) $description, 0, 70, '…'));
            }

            if ($unique > 4) {
                $this->line('      … ' . ($unique - 4) . ' more');
            }
        }

        $this->newLine();
        $this->line('  <fg=gray>Distinct rows here are separate products, not duplicates.</>');
        $this->newLine();
    }

    private function resolveQuotation(): ?QuotationRequest
    {
        $ref = $this->argument('quotation');

        if ($ref === null) {
            return QuotationRequest::latest('id')->first();
        }

        return is_numeric($ref)
            ? QuotationRequest::find((int) $ref)
            : QuotationRequest::where('uuid', $ref)->first();
    }
}
