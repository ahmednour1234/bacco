<?php

namespace App\Http\Controllers\Admin\Catalog\Pricing;

use App\Enums\Catalog\Pricing\PriceConfidenceEnum;
use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\Pricing\StorePriceRequest;
use App\Models\Catalog\Pricing\CatalogSupplier;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Prices for real catalog variants.
 *
 * Manual entry matters most here: scraped sources only cover part of the
 * catalog, so staff-entered supplier prices are the path to coverage for
 * everything else (fire protection, valves, plumbing).
 */
class PricingController extends Controller
{
    /** Priced and unpriced variants, filterable. */
    public function index(Request $request): View
    {
        $this->authorize('catalog.price.view');

        $variants = ProductVariant::query()
            ->with(['manufacturer:id,name'])
            ->withCount('prices')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(
                fn ($w) => $w->where('variant_name', 'like', "%{$term}%")
                    ->orWhere('manufacturer_sku', 'like', "%{$term}%")
            ))
            ->when($request->input('manufacturer'), fn ($q, $id) => $q->where('manufacturer_id', $id))
            // "unpriced" is the actionable view: these are what staff must quote.
            ->when($request->input('priced') === 'no', fn ($q) => $q->having('prices_count', '=', 0))
            ->when($request->input('priced') === 'yes', fn ($q) => $q->having('prices_count', '>', 0))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $stats = $this->stats();

        return view('admin.catalog.pricing.index', compact('variants', 'stats'));
    }

    /** All prices for one variant, across tiers and suppliers. */
    public function show(string $uuid): View
    {
        $this->authorize('catalog.price.view');

        $variant = ProductVariant::where('uuid', $uuid)
            ->with(['manufacturer:id,name'])
            ->firstOrFail();

        $prices = ProductVariantPrice::with('supplier:id,name')
            ->where('product_variant_id', $variant->id)
            ->orderBy('price_tier')
            ->orderBy('min_quantity')
            ->get();

        $suppliers = CatalogSupplier::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.catalog.pricing.show', [
            'variant'     => $variant,
            'prices'      => $prices,
            'suppliers'   => $suppliers,
            'tiers'       => PriceTierEnum::cases(),
            'sources'     => PriceSourceEnum::cases(),
            'confidences' => PriceConfidenceEnum::cases(),
        ]);
    }

    /** Record a manual price. */
    public function store(StorePriceRequest $request, string $uuid): RedirectResponse
    {
        $this->authorize('catalog.price.manage');

        $variant = ProductVariant::where('uuid', $uuid)->firstOrFail();
        $source  = PriceSourceEnum::from($request->string('source')->toString());

        // updateOrCreate matches the table's unique band so re-entering the
        // same tier updates it instead of failing on the constraint.
        ProductVariantPrice::updateOrCreate(
            [
                'product_variant_id' => $variant->id,
                'supplier_id'        => $request->input('supplier_id'),
                'price_tier'         => $request->string('price_tier')->toString(),
                'min_quantity'       => (int) $request->input('min_quantity', 1),
                'currency'           => $request->string('currency')->toString() ?: 'SAR',
            ],
            [
                'price'          => $request->input('price'),
                'max_quantity'   => $request->input('max_quantity'),
                'price_unit'     => $request->input('price_unit'),
                'source'         => $source,
                'source_url'     => $request->input('source_url'),
                // Trust follows the source type rather than the person typing.
                'confidence'     => $source->defaultConfidence(),
                'valid_from'     => $request->input('valid_from'),
                'valid_to'       => $request->input('valid_to'),
                'lead_time_days' => $request->input('lead_time_days'),
                'notes'          => $request->input('notes'),
                'captured_at'    => now(),
                'is_active'      => true,
                'created_by'     => $request->user()->id,
            ]
        );

        return back()->with('success', __('app.price_saved'));
    }

    public function destroy(Request $request, string $uuid, int $priceId): RedirectResponse
    {
        $this->authorize('catalog.price.manage');

        $variant = ProductVariant::where('uuid', $uuid)->firstOrFail();

        ProductVariantPrice::where('product_variant_id', $variant->id)
            ->where('id', $priceId)
            ->delete(); // soft delete keeps the history

        return back()->with('success', __('app.price_deleted'));
    }

    /** Coverage headline: how much of the catalog can actually be quoted. */
    private function stats(): array
    {
        $catalog = DB::connection('catalog');

        $variants = $catalog->table('product_variants')->whereNull('deleted_at')->count();
        $priced   = $catalog->table('product_variant_prices')
            ->whereNull('deleted_at')->distinct()->count('product_variant_id');

        return [
            'variants'  => $variants,
            'priced'    => $priced,
            'unpriced'  => max(0, $variants - $priced),
            'coverage'  => $variants > 0 ? round($priced / $variants * 100, 1) : 0.0,
            'prices'    => $catalog->table('product_variant_prices')->whereNull('deleted_at')->count(),
            'suppliers' => CatalogSupplier::count(),
        ];
    }
}
