<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $pdf = Pdf::loadView('enduser.quotations.pdf', compact('quotation'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('quotation-' . $quotation->quotation_no . '.pdf');
    }
}
