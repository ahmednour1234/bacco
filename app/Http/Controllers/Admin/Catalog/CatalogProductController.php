<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogProductController extends Controller
{
    public function __construct(private CatalogProductService $productService) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'catalog_id', 'category_id', 'division', 'sub_type',
            'unit', 'type_of_material', 'search',
        ]);

        $products  = $this->productService->filter($filters, 30);
        $catalogs  = $this->productService->allCatalogs();

        $catalogId = $filters['catalog_id'] ?? null;

        $categories     = $catalogId ? $this->productService->categoriesForCatalog($catalogId) : collect();
        $divisions      = $this->productService->distinctValues('division', $catalogId);
        $subTypes       = $this->productService->distinctValues('sub_type', $catalogId);
        $units          = $this->productService->distinctValues('unit', $catalogId);
        $materials      = $this->productService->distinctValues('type_of_material', $catalogId);

        return view('admin.catalog.products.index', compact(
            'products', 'catalogs', 'categories',
            'divisions', 'subTypes', 'units', 'materials', 'filters'
        ));
    }
}
