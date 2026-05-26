<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Mpdf\Mpdf;

class QuotationController extends Controller
{
    public function index(): View
    {
        return view('enduser.quotations.index');
    }

    public function create(): View
    {
        return view('enduser.quotations.create');
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
        $quotation = QuotationRequest::with(['client', 'project', 'items.unit'])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans']);
        $mpdf->WriteHTML(view('enduser.quotations.pdf', compact('quotation'))->render());
        $filename = 'quotation-' . $quotation->quotation_no . '.pdf';
        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

