<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function home()
    {
        $divisions = collect();
        try {
            $db = DB::connection('catalog');
            $divisions = $db->table('catalog_products')
                ->whereNotNull('division')
                ->where('division', '!=', '')
                ->select(
                    'division',
                    DB::raw('count(*) as products'),
                )
                ->groupBy('division')
                ->orderBy('division')
                ->get()
                ->map(fn($r) => (object)[
                    'name'     => $r->division,
                    'slug'     => \Illuminate\Support\Str::slug($r->division),
                    'products' => $r->products,
                ]);
        } catch (\Exception $e) {
            // catalog DB unavailable — welcome page still renders, cards are hidden
        }

        return view('welcome', compact('divisions'));
    }

    public function index()
    {
        try {
            $db = DB::connection('catalog');

            $rows = $db->table('catalog_categories as c')
                ->join('catalog_products as p', 'p.category_id', '=', 'c.id')
                ->whereNotNull('c.name')
                ->where('c.name', '!=', '')
                ->select(
                    'c.id',
                    'c.name',
                    'c.slug',
                    DB::raw('count(p.id) as products'),
                    DB::raw('count(distinct p.item_description) as items'),
                    DB::raw('MAX(p.division) as division'),
                )
                ->groupBy('c.id', 'c.name', 'c.slug')
                ->orderBy('c.name')
                ->get()
                ->map(fn($r) => (object) array_merge((array) $r, [
                    'slug' => $r->slug ?: Str::slug($r->name),
                ]));

            $totals = [
                'divisions'  => $db->table('catalog_products')->whereNotNull('division')->distinct()->count('division'),
                'categories' => $rows->count(),
                'items'      => $db->table('catalog_products')->whereNotNull('item_description')->where('item_description', '!=', '')->distinct()->count('item_description'),
                'products'   => $db->table('catalog_products')->count(),
            ];
        } catch (\Exception $e) {
            $rows   = collect();
            $totals = ['divisions' => 0, 'categories' => 0, 'items' => 0, 'products' => 0];
        }

        return view('catalog.index', compact('rows', 'totals'));
    }

    public function showCategory(Request $request, string $slug)
    {
        try {
            $db = DB::connection('catalog');

            $category = $db->table('catalog_categories')
                ->where('slug', $slug)
                ->first();

            if (!$category) {
                // fallback: resolve by slug-matching name
                $category = $db->table('catalog_categories')
                    ->get()
                    ->first(fn($c) => Str::slug($c->name) === $slug);
            }

            abort_if(!$category, 404);

            $materials = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('type_of_material')
                ->where('type_of_material', '!=', '')
                ->distinct()->orderBy('type_of_material')->pluck('type_of_material');

            $sizes = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('size')->where('size', '!=', '')
                ->distinct()->orderBy('size')->pluck('size');

            $leadTimes = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('lead_time')->where('lead_time', '!=', '')
                ->distinct()->orderBy('lead_time')->pluck('lead_time');

            $query = $db->table('catalog_products')
                ->where('category_id', $category->id)
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
                'products'   => $db->table('catalog_products')->where('category_id', $category->id)->count(),
                'items'      => $db->table('catalog_products')->where('category_id', $category->id)->whereNotNull('item_description')->where('item_description', '!=', '')->distinct()->count('item_description'),
                'categories' => 1,
            ];

            $division = $db->table('catalog_products')->where('category_id', $category->id)->value('division') ?? '';

        } catch (\Exception $e) {
            Log::error('Catalog showCategory 503', ['slug' => $slug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.division', [
            'division'  => $category->name,
            'slug'      => $slug,
            'items'     => $items,
            'stats'     => $stats,
            'materials' => $materials,
            'sizes'     => $sizes,
            'leadTimes' => $leadTimes,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Catalog show (division) 503', ['slug' => $slug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.division', compact('division', 'slug', 'items', 'stats', 'materials', 'sizes', 'leadTimes'));
    }

    public function showItem(string $divisionSlug, string $itemSlug)
    {
        try {
        $db = DB::connection('catalog');

        // Resolve division
        $division = $db->table('catalog_products')
            ->whereNotNull('division')
            ->distinct()
            ->pluck('division')
            ->first(fn($d) => Str::slug($d) === $divisionSlug);

        abort_if(!$division, 404);

        // Resolve item description
        $itemDescription = $db->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('item_description')
            ->distinct()
            ->pluck('item_description')
            ->first(fn($i) => Str::slug($i) === $itemSlug);

        abort_if(!$itemDescription, 404);

        // Aggregate item data
        $product = $db->table('catalog_products as p')
            ->leftJoin('catalog_categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.division', $division)
            ->where('p.item_description', $itemDescription)
            ->select(
                DB::raw('MAX(c.name) as category'),
                DB::raw('MAX(p.lead_time) as lead_time'),
                DB::raw('MAX(p.sub_type) as sub_type'),
                DB::raw('GROUP_CONCAT(DISTINCT p.type_of_material ORDER BY p.type_of_material SEPARATOR "|||") as materials'),
                DB::raw('GROUP_CONCAT(DISTINCT p.size ORDER BY p.size SEPARATOR "|||") as sizes'),
                DB::raw('count(*) as product_count'),
            )
            ->first();

        abort_if(!$product, 404);

        $materials = collect(array_filter(explode('|||', $product->materials ?? '')));
        $sizes     = collect(array_filter(explode('|||', $product->sizes ?? '')));

        // Related items: other item_descriptions in the same division
        $related = $db->table('catalog_products')
            ->where('division', $division)
            ->where('item_description', '!=', $itemDescription)
            ->whereNotNull('item_description')
            ->where('item_description', '!=', '')
            ->distinct()
            ->orderBy('item_description')
            ->limit(12)
            ->pluck('item_description')
            ->map(fn($i) => (object) [
                'name' => $i,
                'slug' => Str::slug($i),
            ]);
        } catch (\Exception $e) {
            Log::error('Catalog showItem 503', ['divisionSlug' => $divisionSlug, 'itemSlug' => $itemSlug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.item', compact(
            'division', 'divisionSlug', 'itemDescription', 'itemSlug',
            'product', 'materials', 'sizes', 'related'
        ));
    }
}
