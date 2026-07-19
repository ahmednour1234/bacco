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
            $quotationId = QuotationRequest::where('client_id', Auth::id())
                ->where('status', QuotationRequestStatusEnum::Draft->value)
                ->latest('id')
                ->value('id');
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

