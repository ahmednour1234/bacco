<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

        $pdf = Pdf::loadView('enduser.quotations.pdf', compact('quotation'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('quotation-' . $quotation->quotation_no . '.pdf');
    }
}

