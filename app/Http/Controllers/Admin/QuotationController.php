<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Mpdf\Mpdf;

class QuotationController extends Controller
{
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
        $quotation = QuotationRequest::with(['client', 'project', 'items.unit'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans']);
        $mpdf->WriteHTML(view('enduser.quotations.pdf', compact('quotation'))->render());
        $filename = 'quotation-' . $quotation->quotation_no . '.pdf';
        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
