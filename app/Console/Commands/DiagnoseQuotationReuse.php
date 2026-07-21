<?php

namespace App\Console\Commands;

use App\Models\BoqAnswerResult;
use App\Models\BoqParseResult;
use App\Models\QuotationRequest;
use Illuminate\Console\Command;

/**
 * Explains, for the most recent quotations, why their prices did or did not
 * come from a stored result.
 *
 * Every previous round of this investigation needed a hand-written tinker
 * one-liner, and each answered only one link of the chain. This walks the whole
 * chain for real quotations and names the first thing that breaks.
 */
class DiagnoseQuotationReuse extends Command
{
    protected $signature = 'quotation:diagnose {--limit=5 : How many recent quotations to inspect}';

    protected $description = 'Explain why recent quotations did or did not reuse a stored price';

    public function handle(): int
    {
        $quotations = QuotationRequest::latest('id')
            ->limit((int) $this->option('limit'))
            ->get(['id', 'quotation_no', 'boq_file_hash', 'answers_hash']);

        if ($quotations->isEmpty()) {
            $this->warn('No quotations found.');

            return self::SUCCESS;
        }

        $emptyAnswers = BoqAnswerResult::hashAnswers([]);
        $this->line('Empty-answer key: ' . substr($emptyAnswers, 0, 12));
        $this->newLine();

        $seenFiles = [];

        foreach ($quotations as $quotation) {
            $this->line("── Quotation #{$quotation->id} ({$quotation->quotation_no})");

            // The document is the source of truth for the file hash; the column
            // on the quotation is only a convenience copy.
            $docHash = $quotation->uploadedDocuments()
                ->where('file_type', 'boq')
                ->latest()
                ->value('file_hash');

            $fileHash = $quotation->boq_file_hash ?: $docHash;

            if (! $fileHash) {
                $this->error('   no file hash — reuse is skipped entirely');
                $this->line('   cause: the upload never recorded one (old quotation, or a non-BOQ upload)');
                $this->newLine();
                continue;
            }

            $this->line('   file:    ' . substr($fileHash, 0, 12) . ($docHash && $docHash !== $quotation->boq_file_hash ? '  (from document)' : ''));
            $this->line('   answers: ' . substr((string) $quotation->answers_hash, 0, 12)
                . ($quotation->answers_hash === $emptyAnswers ? '  (no questions)' : ''));

            // Two quotations sharing a file hash are genuinely the same document.
            // Different hashes mean different files, whatever the names suggest.
            if (isset($seenFiles[$fileHash])) {
                $this->info('   same file as quotation #' . $seenFiles[$fileHash]);
            } else {
                $seenFiles[$fileHash] = $quotation->id;
            }

            $parse = BoqParseResult::where('file_hash', $fileHash)->first();

            $this->line($parse
                ? '   parse:   stored, ' . count($parse->items ?? []) . ' rows, reused ' . $parse->hit_count . '×'
                : '   parse:   NOT stored — every upload re-extracts');

            $priced = BoqAnswerResult::where('file_hash', $fileHash)->get();

            if ($priced->isEmpty()) {
                $this->line('   priced:  nothing stored for this file yet');
            } else {
                $this->line('   priced:  ' . $priced->count() . ' result(s) stored');

                foreach ($priced as $row) {
                    $this->line('            ' . substr($row->answers_hash, 0, 12)
                        . '  ' . count($row->priced_items ?? []) . ' rows, reused ' . $row->hit_count . '×');
                }

                // More than one row for one file means the answers differed, so
                // each was priced separately — which is correct, but worth
                // seeing when someone expects a single stable price.
                if ($priced->count() > 1) {
                    $this->warn('            more than one answer set — each prices separately');
                }
            }

            $this->newLine();
        }

        $this->line('Same file uploaded twice should show: the same file hash, a stored parse');
        $this->line('with hit_count above 1, and one priced result reused more than once.');

        return self::SUCCESS;
    }
}
