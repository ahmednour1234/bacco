<?php

namespace App\Jobs\Concerns;

use App\Models\QuotationItem;
use Illuminate\Support\Facades\Log;

/**
 * Merges quotation rows that describe the same line.
 *
 * Shared because every path that writes rows can produce duplicates, and each
 * one previously either had its own copy or none at all:
 *
 *   - the batched extraction, where two parts emit the same row without either
 *     being able to see the other;
 *   - the single-job extraction, which streams slice by slice for the same
 *     reason;
 *   - a run the user stopped, or a batch that was cancelled — the case most
 *     likely to have raced, and the one that skipped merging entirely.
 */
trait MergesDuplicateQuotationRows
{
    /**
     * Merge duplicate rows on a quotation, summing their quantities.
     *
     * A row counts as the same only when description AND unit AND unit price
     * all match. That is deliberately strict:
     *
     *   - description alone would merge the same beam on two floors, which a
     *     contractor prices and schedules as separate lines;
     *   - ignoring price would merge rows a supplier quoted differently.
     *
     * Quantities are summed rather than discarded — dropping one would silently
     * halve the order.
     *
     * @return int  how many rows were absorbed
     */
    protected function mergeDuplicateQuotationRows(int $quotationId): int
    {
        $seen     = [];
        $absorb   = [];
        $toDelete = [];
        $sources  = [];

        QuotationItem::where('quotation_request_id', $quotationId)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$seen, &$absorb, &$toDelete, &$sources): void {
                foreach ($rows as $row) {
                    // A row with no description cannot be compared meaningfully;
                    // leave it alone rather than merging unrelated blanks.
                    if (trim((string) $row->description) === '') {
                        continue;
                    }

                    // Normalised so trivial whitespace/case differences do not
                    // read as separate products.
                    $key = implode('|', [
                        mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $row->description) ?? '')),
                        (string) ($row->unit_id ?? ''),
                        (string) ($row->unit_price ?? ''),
                    ]);

                    if (! isset($seen[$key])) {
                        $seen[$key] = $row->id;
                        continue;
                    }

                    $keepId          = $seen[$key];
                    $absorb[$keepId] = ($absorb[$keepId] ?? 0) + (float) $row->quantity;
                    $toDelete[]      = $row->id;

                    // Keep what was folded in. The descriptions are identical by
                    // the time they reach here, but they often were not in the
                    // sheet — "40 MPa for footings" and "40 MPa for columns" both
                    // clean down to "40 MPa". Recording the originals means a
                    // merged quantity can be traced back rather than taken on
                    // trust.
                    $sources[$keepId][] = [
                        'id'       => $row->id,
                        'quantity' => (float) $row->quantity,
                        'original' => $row->raw_data['original_description'] ?? null,
                    ];
                }
            });

        if ($toDelete === []) {
            return 0;
        }

        foreach ($absorb as $keepId => $addedQuantity) {
            $keeper = QuotationItem::find($keepId);

            if (! $keeper) {
                continue;
            }

            $rawData = is_array($keeper->raw_data) ? $keeper->raw_data : [];

            // Written onto the surviving row so the merge is inspectable: how
            // many lines were folded in, and what each contributed.
            $rawData['merged_from']  = $sources[$keepId] ?? [];
            $rawData['merged_count'] = count($sources[$keepId] ?? []);

            $keeper->quantity = (float) $keeper->quantity + $addedQuantity;
            $keeper->raw_data = $rawData;
            $keeper->save();
        }

        foreach (array_chunk($toDelete, 500) as $batch) {
            QuotationItem::whereIn('id', $batch)->delete();
        }

        Log::info('Merged duplicate quotation rows.', [
            'quotation_id' => $quotationId,
            'merged'       => count($toDelete),
        ]);

        return count($toDelete);
    }
}
