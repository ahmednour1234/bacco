<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\View\View;

class SeoMetaController extends Controller
{
    public function index(): View
    {
        return view('admin.seo.index');
    }

    public function edit(SeoMeta $seo): View
    {
        return view('admin.seo.edit', compact('seo'));
    }
}
