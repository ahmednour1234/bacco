<?php

namespace App\Http\Controllers;

use App\Models\Article;
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
            $isAr = app()->getLocale() === 'ar';

            $divisions = $db->table('catalog_products')
                ->where(function ($q) {
                    $q->where('division', '!=', '')->orWhere('division_ar', '!=', '');
                })
                ->select(
                    'division',
                    'division_ar',
                    DB::raw('count(*) as products'),
                )
                ->groupBy('division', 'division_ar')
                ->get()
                ->map(function ($r) use ($isAr) {
                    $label = $isAr
                        ? ($r->division_ar ?: $r->division)
                        : ($r->division ?: $r->division_ar);

                    return (object) [
                        'name'     => $label,
                        // slug always from the English value when present, so the
                        // category route stays stable across locales.
                        'slug'     => \Illuminate\Support\Str::slug($r->division ?: $r->division_ar),
                        'products' => $r->products,
                    ];
                })
                ->filter(fn($d) => trim((string) $d->name) !== '')
                ->sortBy('name')
                ->values();
        } catch (\Exception $e) {
            // catalog DB unavailable — welcome page still renders, cards are hidden
        }

        $news = Article::where('active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('welcome', compact('divisions', 'news'));
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
                    'c.name_ar',
                    'c.slug',
                    DB::raw('count(p.id) as products'),
                    DB::raw('count(distinct p.item_description) as items'),
                    DB::raw('MAX(p.division) as division'),
                )
                ->groupBy('c.id', 'c.name', 'c.name_ar', 'c.slug')
                ->orderBy('c.name')
                ->get()
                ->map(function ($r) {
                    // Locale-aware label, cross-falling back to the other language.
                    $isAr  = app()->getLocale() === 'ar';
                    $label = $isAr
                        ? ($r->name_ar ?: $r->name)
                        : ($r->name ?: $r->name_ar);

                    return (object) array_merge((array) $r, [
                        'name' => $label,
                        'slug' => $r->slug ?: Str::slug($r->name ?: $r->name_ar),
                    ]);
                });

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

            $isAr = app()->getLocale() === 'ar';
            // Filter option labels prefer the Arabic column, cross-falling back to EN.
            $matCol  = $isAr ? 'COALESCE(NULLIF(type_of_material_ar,""), type_of_material)' : 'type_of_material';
            $sizeCol = $isAr ? 'COALESCE(NULLIF(size_ar,""), size)' : 'size';
            $leadCol = $isAr ? 'COALESCE(NULLIF(lead_time_ar,""), lead_time)' : 'lead_time';

            $materials = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('type_of_material')
                ->where('type_of_material', '!=', '')
                ->distinct()->orderBy('type_of_material')
                ->pluck(DB::raw($matCol . ' as label'), 'type_of_material');

            $sizes = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('size')->where('size', '!=', '')
                ->distinct()->orderBy('size')
                ->pluck(DB::raw($sizeCol . ' as label'), 'size');

            $leadTimes = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->whereNotNull('lead_time')->where('lead_time', '!=', '')
                ->distinct()->orderBy('lead_time')
                ->pluck(DB::raw($leadCol . ' as label'), 'lead_time');

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

            // Display the Arabic label when present; keep grouping/slug on the
            // English item_description so paginated URLs stay stable across locales.
            $itemLabel = $isAr
                ? 'MAX(COALESCE(NULLIF(item_description_ar,""), item_description))'
                : 'MAX(item_description)';
            $matConcat = $isAr
                ? 'GROUP_CONCAT(DISTINCT COALESCE(NULLIF(type_of_material_ar,""), type_of_material) ORDER BY type_of_material SEPARATOR ", ")'
                : 'GROUP_CONCAT(DISTINCT type_of_material ORDER BY type_of_material SEPARATOR ", ")';
            $leadLabel = $isAr
                ? 'MAX(COALESCE(NULLIF(lead_time_ar,""), lead_time))'
                : 'MAX(lead_time)';

            $items = $query
                ->select(
                    'item_description',
                    DB::raw($itemLabel . ' as item_label'),
                    DB::raw('count(*) as products'),
                    DB::raw($matConcat . ' as common_materials'),
                    DB::raw($leadLabel . ' as lead_time'),
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

            // Division label (English for the sibling query, Arabic for display).
            $divRow = $db->table('catalog_products')
                ->where('category_id', $category->id)
                ->select('division', 'division_ar')
                ->first();
            $division   = $divRow->division ?? '';
            $divisionAr = ($isAr && !empty($divRow->division_ar)) ? $divRow->division_ar : $division;

            // Related categories — siblings sharing the same division
            $relatedCatIds = $db->table('catalog_products')
                ->where('division', $division)
                ->whereNotNull('category_id')
                ->where('category_id', '!=', $category->id)
                ->distinct()->pluck('category_id');
            $relatedCategories = $relatedCatIds->isNotEmpty()
                ? $db->table('catalog_categories')->whereIn('id', $relatedCatIds)->limit(6)->get(['id', 'name', 'name_ar', 'slug'])
                    ->map(function ($c) use ($isAr) {
                        $c->name = ($isAr && !empty($c->name_ar)) ? $c->name_ar : $c->name;
                        return $c;
                    })
                : collect();

            $categoryLabel = ($isAr && !empty($category->name_ar)) ? $category->name_ar : $category->name;

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Catalog showCategory 503', ['slug' => $slug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.division', [
            'division'    => $divisionAr,
            'categoryLabel' => $categoryLabel,
            'category'    => $category,
            'slug'        => $slug,
            'filterRoute' => 'catalog.category',
            'items'       => $items,
            'stats'       => $stats,
            'materials'   => $materials,
            'sizes'       => $sizes,
            'leadTimes'   => $leadTimes,
            'relatedCategories' => $relatedCategories,
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
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Catalog show (division) 503', ['slug' => $slug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.division', compact('division', 'slug', 'items', 'stats', 'materials', 'sizes', 'leadTimes') + ['filterRoute' => 'catalog.division']);
    }

    public function showItem(string $divisionSlug, string $itemSlug)
    {
        try {
        $db = DB::connection('catalog');

        // Resolve division — first param may be a division slug OR a category slug
        $division = $db->table('catalog_products')
            ->whereNotNull('division')
            ->distinct()
            ->pluck('division')
            ->first(fn($d) => Str::slug($d) === $divisionSlug);

        if (!$division) {
            // Try resolving via category slug
            $cat = $db->table('catalog_categories')->where('slug', $divisionSlug)->first();
            if (!$cat) {
                $cat = $db->table('catalog_categories')
                    ->get()
                    ->first(fn($c) => Str::slug($c->name) === $divisionSlug);
            }
            if ($cat) {
                $division = $db->table('catalog_products')
                    ->where('category_id', $cat->id)
                    ->whereNotNull('division')
                    ->value('division');
            }
        }

        abort_if(!$division, 404);

        // Resolve item description
        $itemDescription = $db->table('catalog_products')
            ->where('division', $division)
            ->whereNotNull('item_description')
            ->distinct()
            ->pluck('item_description')
            ->first(fn($i) => Str::slug($i) === $itemSlug);

        abort_if(!$itemDescription, 404);

        $isAr = app()->getLocale() === 'ar';

        // Aggregate item data (Arabic-preferred labels with EN fallback)
        $product = $db->table('catalog_products as p')
            ->leftJoin('catalog_categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.division', $division)
            ->where('p.item_description', $itemDescription)
            ->select(
                DB::raw('MAX(COALESCE(NULLIF(c.name_ar,""), c.name)) as category'),
                DB::raw('MAX(p.lead_time) as lead_time'),
                DB::raw('MAX(COALESCE(NULLIF(p.sub_type_ar,""), p.sub_type)) as sub_type'),
                DB::raw('GROUP_CONCAT(DISTINCT COALESCE(NULLIF(p.type_of_material_ar,""), p.type_of_material) ORDER BY COALESCE(NULLIF(p.type_of_material_ar,""), p.type_of_material) SEPARATOR "|||") as materials'),
                DB::raw('GROUP_CONCAT(DISTINCT COALESCE(NULLIF(p.size_ar,""), p.size) ORDER BY COALESCE(NULLIF(p.size_ar,""), p.size) SEPARATOR "|||") as sizes'),
                DB::raw('count(*) as product_count'),
            )
            ->first();

        // Arabic display versions for this item (slug/grouping still use English)
        $arRow = $db->table('catalog_products')
            ->where('division', $division)
            ->where('item_description', $itemDescription)
            ->select('item_description_ar', 'division_ar')
            ->first();
        $itemLabel     = ($isAr && !empty($arRow->item_description_ar)) ? $arRow->item_description_ar : $itemDescription;
        $divisionLabel = ($isAr && !empty($arRow->division_ar)) ? $arRow->division_ar : $division;

        abort_if(!$product, 404);

        $materials = collect(array_filter(explode('|||', $product->materials ?? '')));
        $sizes     = collect(array_filter(explode('|||', $product->sizes ?? '')));

        // Related items: other item_descriptions in the same division
        $related = $db->table('catalog_products')
            ->where('division', $division)
            ->where('item_description', '!=', $itemDescription)
            ->whereNotNull('item_description')
            ->where('item_description', '!=', '')
            ->groupBy('item_description')
            ->orderBy('item_description')
            ->limit(12)
            ->select('item_description', DB::raw('MAX(item_description_ar) as item_description_ar'))
            ->get()
            ->map(fn($i) => (object) [
                'name' => ($isAr && !empty($i->item_description_ar)) ? $i->item_description_ar : $i->item_description,
                'slug' => Str::slug($i->item_description),
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Catalog showItem 503', ['divisionSlug' => $divisionSlug, 'itemSlug' => $itemSlug, 'error' => $e->getMessage()]);
            return response()->view('errors.503', [], 503);
        }

        return view('catalog.item', compact(
            'division', 'divisionSlug', 'itemDescription', 'itemSlug',
            'product', 'materials', 'sizes', 'related',
            'isAr', 'itemLabel', 'divisionLabel'
        ));
    }
}
