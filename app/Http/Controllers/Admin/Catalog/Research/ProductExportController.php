<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Exports\Catalog\Research\ProductVariantsExport;
use App\Http\Controllers\Controller;
use App\Repositories\Catalog\Research\ProductVariantRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExportController extends Controller
{
    public function __construct(private ProductVariantRepository $variants) {}

    /** Export the (filtered) catalog to XLSX — one row per variant, no prices. */
    public function products(Request $request): BinaryFileResponse
    {
        $this->authorize('catalog.export');

        $filters = $request->only([
            'search', 'family_id', 'manufacturer_id', 'size_id', 'connection_type_id',
            'pressure_rating_id', 'approval_id', 'verification_level',
            'verification_status', 'availability_status', 'market_scope', 'fire_protection',
        ]);

        $export = new ProductVariantsExport($this->variants->baseQuery($filters));
        $path   = $export->toTempFile();

        return response()
            ->download($path, 'product-catalog-' . now()->format('Ymd-His') . '.xlsx')
            ->deleteFileAfterSend(true);
    }
}
