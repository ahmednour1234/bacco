<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Mpdf\Mpdf;

class QuotationController extends Controller
{
    private const PDF_ROWS_MARKER = '<!--QIMTA_ROWS-->';

    private const PDF_ROWS_PER_BATCH = 250;

    public function index()
    {
        return view('admin.quotations.index');
    }

    public function show(string $uuid)
    {
        return view('admin.quotations.show', compact('uuid'));
    }

    public function pdf(string $uuid)
    {
        // Items are streamed in batches below, so they are deliberately not
        // eager-loaded here.
        $quotation = QuotationRequest::with(['client', 'project'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        // The template emits a row marker instead of the rows themselves; split
        // there and stream the batches through, mirroring the enduser export.
        $html  = view('enduser.quotations.pdf', compact('quotation'))->render();
        $parts = explode(self::PDF_ROWS_MARKER, $html, 2);

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans']);
        $mpdf->WriteHTML($parts[0]);

        if (count($parts) === 2) {
            $offset = 0;

            // One complete table per batch: mPDF buffers a table until its
            // closing tag, so a table split across calls would drop its rows.
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
