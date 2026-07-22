<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Research\Approval;
use App\Models\Catalog\Research\ConnectionType;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\ProductSize;
use App\Repositories\Catalog\Research\ProductVariantRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __construct(private ProductVariantRepository $variants) {}

    public function index(Request $request): View
    {
        $this->authorize('catalog.product.view');

        $filters  = $this->filters($request);
        $variants = $this->variants->filtered($filters, 25);

        return view('admin.catalog.research.products.index', [
            'variants'      => $variants,
            'filters'       => $filters,
            'manufacturers' => Manufacturer::orderBy('name')->get(['id', 'name']),
            'sizes'         => ProductSize::orderBy('sort_order')->get(['id', 'display_value']),
            'connections'   => ConnectionType::orderBy('name')->get(['id', 'name']),
            'approvals'     => Approval::orderBy('name')->get(['id', 'name', 'approval_code']),
        ]);
    }

    public function show(string $uuid): View
    {
        $this->authorize('catalog.product.view');

        $variant = $this->variants->findByUuid($uuid);
        $variant->load(['manufacturer', 'model.series', 'model.materials', 'family',
            'size', 'connectionType', 'connectionStandard', 'pressureRating',
            'approvals', 'standards', 'evidence.source']);

        return view('admin.catalog.research.products.show', compact('variant'));
    }

    /** @return array<string,mixed> */
    private function filters(Request $request): array
    {
        return $request->only([
            'search', 'family_id', 'manufacturer_id', 'model_id', 'manufacturer_sku',
            'size_id', 'connection_type_id', 'pressure_rating_id', 'approval_id',
            'verification_level', 'verification_status', 'availability_status',
            'market_scope', 'fire_protection',
        ]);
    }
}
