<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Research\SourceDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SourceRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('catalog.source.view');

        $sources = SourceDocument::query()
            ->with('manufacturer')
            ->when($request->input('source_type'), fn ($q, $t) => $q->where('source_type', $t))
            ->when($request->input('manufacturer_id'), fn ($q, $m) => $q->where('manufacturer_id', $m))
            ->when($request->boolean('official_only'), fn ($q) => $q->where('is_official', true))
            ->latest('checked_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.catalog.research.sources.index', compact('sources'));
    }
}
