<?php

namespace App\Http\Controllers\Admin\Catalog\Pricing;

use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Models\Catalog\Pricing\ScraperPriceMatch;
use App\Services\Catalog\Pricing\SupplierSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Human review of scraped-product → catalog-variant links.
 *
 * Only exact-SKU and normalized-key matches auto-accept; everything weaker
 * lands here. A wrong confirmation attaches a real price to the wrong product,
 * so the screen shows both sides side by side and confirming is an explicit
 * act, never a default.
 */
class PriceMatchReviewController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('catalog.price.review');

        $matches = ScraperPriceMatch::query()
            ->with(['variant:id,uuid,variant_name,manufacturer_sku,manufacturer_id', 'variant.manufacturer:id,name'])
            ->when(
                $request->input('status', MatchStatusEnum::Pending->value),
                fn ($q, $s) => $q->where('status', $s)
            )
            ->orderByDesc('confidence_score')
            ->paginate(20)
            ->withQueryString();

        $counts = ScraperPriceMatch::query()
            ->selectRaw('status, COUNT(*) c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('admin.catalog.pricing.matches', compact('matches', 'counts'));
    }

    /** Confirm a link and create the price it implies. */
    public function confirm(Request $request, int $id, SupplierSyncService $suppliers): RedirectResponse
    {
        $this->authorize('catalog.price.review');

        $match = ScraperPriceMatch::findOrFail($id);

        if ($match->product_variant_id === null) {
            return back()->with('error', __('app.match_has_no_variant'));
        }

        $match->update([
            'status'      => MatchStatusEnum::Confirmed,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // A confirmed link is what authorises the price to exist.
        if ($match->scraped_price !== null && (float) $match->scraped_price > 0) {
            $supplierId = $suppliers->sourceToSupplierMap()[(int) $match->scraper_source_id] ?? null;

            $price = ProductVariantPrice::updateOrCreate(
                [
                    'product_variant_id' => $match->product_variant_id,
                    'supplier_id'        => $supplierId,
                    'price_tier'         => PriceTierEnum::Retail->value,
                    'min_quantity'       => 1,
                    'currency'           => $match->scraped_currency ?: 'SAR',
                ],
                [
                    'price'              => $match->scraped_price,
                    'source'             => PriceSourceEnum::Scraped,
                    'confidence'         => PriceSourceEnum::Scraped->defaultConfidence(),
                    'source_url'         => $match->scraped_url,
                    'scraper_product_id' => $match->scraper_product_id,
                    'scraper_source_id'  => $match->scraper_source_id,
                    'captured_at'        => now(),
                    'is_active'          => true,
                    'created_by'         => $request->user()->id,
                ]
            );

            $match->update(['price_id' => $price->id]);
        }

        return back()->with('success', __('app.match_confirmed'));
    }

    /** Reject a link and retire any price it created. */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $this->authorize('catalog.price.review');

        $match = ScraperPriceMatch::findOrFail($id);

        $match->update([
            'status'       => MatchStatusEnum::Rejected,
            'review_notes' => $request->input('notes'),
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        // A rejected link must not leave a live price behind.
        if ($match->price_id) {
            ProductVariantPrice::where('id', $match->price_id)->update(['is_active' => false]);
        }

        return back()->with('success', __('app.match_rejected'));
    }
}
