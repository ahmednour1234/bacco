<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
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
}

