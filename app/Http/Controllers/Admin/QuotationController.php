<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
}
