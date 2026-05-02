<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index()
    {
        $rows = DB::connection('catalog')
            ->table('catalog_products')
            ->whereNotNull('division')
            ->select(
                'division',
                DB::raw('count(*) as products'),
                DB::raw('count(distinct item_description) as items'),
                DB::raw('count(distinct category_id) as cats'),
            )
            ->groupBy('division')
            ->orderBy('division')
            ->get()
            ->map(fn($d) => (object) array_merge((array) $d, [
                'slug' => Str::slug($d->division),
            ]));

        $totals = [
            'divisions' => $rows->count(),
            'categories' => DB::connection('catalog')->table('catalog_products')->whereNotNull('category_id')->distinct()->count('category_id'),
            'items' => DB::connection('catalog')->table('catalog_products')->whereNotNull('item_description')->distinct()->count('item_description'),
            'products' => DB::connection('catalog')->table('catalog_products')->count(),
        ];

        return view('catalog.index', compact('rows', 'totals'));
    }

    public function show(Request $request, string $slug)
    {
        // Resolve slug → actual division name
        $division = DB::connection('catalog')
            ->table('catalog_products')
            ->whereNotNull('division')
            ->distinct()
            ->pluck('division')
            ->first(fn($d) => Str::slug($d) === $slug);

        abort_if(!$division, 404);

        // Available filter options
        $materials = DB::connection('catalog')
            ->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('type_of_material')
            ->where('type_of_material', '!=', '')
            ->distinct()
            ->orderBy('type_of_material')
            ->pluck('type_of_material');

        $sizes = DB::connection('catalog')
            ->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->orderBy('size')
            ->pluck('size');

        $leadTimes = DB::connection('catalog')
            ->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('lead_time')
            ->where('lead_time', '!=', '')
            ->distinct()
            ->orderBy('lead_time')
            ->pluck('lead_time');

        // Base query with filters
        $query = DB::connection('catalog')
            ->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('item_description')
            ->where('item_description', '!=', '');

        if ($request->filled('material')) {
            $query->where('type_of_material', $request->material);
        }
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }
        if ($request->filled('lead_time')) {
            $query->where('lead_time', $request->lead_time);
        }
        if ($request->filled('q')) {
            $query->where('item_description', 'like', '%' . $request->q . '%');
        }

        $items = $query
            ->select(
                'item_description',
                DB::raw('count(*) as products'),
                DB::raw('GROUP_CONCAT(DISTINCT type_of_material ORDER BY type_of_material SEPARATOR ", ") as common_materials'),
                DB::raw('MAX(lead_time) as lead_time'),
            )
            ->groupBy('item_description')
            ->orderBy('item_description')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'products'   => DB::connection('catalog')->table('catalog_products')->where('division', $division)->count(),
            'items'      => DB::connection('catalog')->table('catalog_products')->where('division', $division)->whereNotNull('item_description')->where('item_description', '!=', '')->distinct()->count('item_description'),
            'categories' => DB::connection('catalog')->table('catalog_products')->where('division', $division)->whereNotNull('category_id')->distinct()->count('category_id'),
        ];

        return view('catalog.division', compact('division', 'slug', 'items', 'stats', 'materials', 'sizes', 'leadTimes'));
    }
}
