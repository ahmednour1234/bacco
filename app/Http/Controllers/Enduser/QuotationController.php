<?php

namespace App\Http\Controllers\Enduser;

use App\Enums\QuotationRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mpdf\Mpdf;

class QuotationController extends Controller
{
    /** Marker the PDF template emits where the BOQ rows belong. */
    private const PDF_ROWS_MARKER = '<!--QIMTA_ROWS-->';

    /** Rows written to the PDF per mPDF call. */
    private const PDF_ROWS_PER_BATCH = 250;

    public function index(): View
    {
        return view('enduser.quotations.index');
    }

    public function create(Request $request): View
    {
        // ?resume=1 comes from the background-job popup ("view data"). The
        // extraction wrote its rows against a draft quotation, so returning to a
        // blank page would hide work that is already done — reopen that draft.
        $quotationId = null;

        if ($request->boolean('resume')) {
            // Prefer the newest draft that actually has rows. Every upload
            // creates a fresh draft, so "newest draft" alone can easily be an
            // empty one from a later attempt — which lands the user back on the
            // upload step with the extracted rows still hidden.
            $base = fn() => QuotationRequest::where('client_id', Auth::id())
                ->where('status', QuotationRequestStatusEnum::Draft->value);

            $quotationId = $base()->has('items')->latest('id')->value('id')
                ?? $base()->latest('id')->value('id');
        }

        return view('enduser.quotations.create', ['quotationId' => $quotationId]);
    }

    public function show(string $uuid): View
    {
        $quotation = QuotationRequest::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        return view('enduser.quotations.show', compact('quotation'));
    }

    public function edit(string $uuid): View
    {
        $quotation = QuotationRequest::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->whereIn('status', ['draft', 'tender'])
            ->firstOrFail();

        return view('enduser.quotations.edit', compact('quotation'));
    }

    public function pdf(string $uuid): Response
    {
        // Deliberately not eager-loading items: they are streamed in batches
        // below, and loading them here would put every row in memory anyway.
        $quotation = QuotationRequest::with(['client', 'project'])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        // A real BOQ runs to thousands of rows. Rendering the whole document as
        // one HTML string and handing it to mPDF in a single call exhausts
        // memory — a 5,000-row quotation returned a 500. Split the template at
        // the row marker and stream the rows through in batches instead, so
        // neither the HTML nor the hydrated models are ever all in memory.
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $html  = view('enduser.quotations.pdf', compact('quotation'))->render();
        $parts = explode(self::PDF_ROWS_MARKER, $html, 2);

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans']);
        $mpdf->WriteHTML($parts[0]);

        if (count($parts) === 2) {
            $offset = 0;

            // One complete table per batch. mPDF buffers a table until its
            // closing tag, so each call must contain whole markup — splitting a
            // single table across calls would silently drop the rows.
            $quotation->items()
                ->with('unit')
                ->orderBy('id')
                ->chunk(self::PDF_ROWS_PER_BATCH, function ($rows) use ($mpdf, &$offset): void {
                    $mpdf->WriteHTML(
                        view('enduser.quotations._pdf_rows', [
                            'rows'   => $rows,
                            'offset' => $offset,
                        ])->render()
                    );
                    $offset += $rows->count();

                    // Release the batch before the next query runs.
                    unset($rows);
                });

            $mpdf->WriteHTML($parts[1]);
        }

        $filename = 'quotation-' . $quotation->quotation_no . '.pdf';
        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

