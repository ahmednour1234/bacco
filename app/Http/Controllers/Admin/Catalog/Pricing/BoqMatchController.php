<?php

namespace App\Http\Controllers\Admin\Catalog\Pricing;

use App\Enums\Catalog\Pricing\MatchStatusEnum;
use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use App\Http\Controllers\Controller;
use App\Jobs\Catalog\Pricing\MatchBoqItemsJob;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\Catalog\Pricing\BoqVariantMatch;
use App\Models\Catalog\Pricing\CatalogSupplier;
use App\Models\Catalog\Pricing\ProductVariantPrice;
use App\Services\Catalog\Pricing\BoqSpecParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Review screen where a BOQ line is tied to a real catalog product and priced.
 *
 * This is where the whole pipeline pays off: the engineer sees the BOQ text
 * beside the products that could satisfy it, picks one, and enters its price
 * without hunting through a catalog of tens of thousands of items.
 */
class BoqMatchController extends Controller
{
    /** BOQs with their match/price coverage. */
    public function index(): View
    {
        $this->authorize('catalog.price.view');

        $boqs = Boq::query()->latest('id')->paginate(20);

        // Coverage per BOQ, read in one query rather than per row.
        $ids = $boqs->pluck('id')->all();

        $stats = $ids === [] ? collect() : collect(
            DB::connection('catalog')->table('boq_variant_matches')
                ->selectRaw('boq_id, COUNT(DISTINCT boq_item_id) matched, '
                    . 'COUNT(DISTINCT CASE WHEN unit_price IS NOT NULL THEN boq_item_id END) priced, '
                    . 'COUNT(DISTINCT CASE WHEN is_selected = 1 THEN boq_item_id END) selected')
                ->whereIn('boq_id', $ids)->groupBy('boq_id')->get()
        )->keyBy('boq_id');

        return view('admin.catalog.pricing.boq.index', compact('boqs', 'stats'));
    }

    /** Line-by-line review of one BOQ. */
    public function show(Request $request, int $boq, BoqSpecParser $parser): View
    {
        $this->authorize('catalog.price.view');

        $boqModel = Boq::findOrFail($boq);

        $items = BoqItem::query()
            ->where('boq_id', $boq)
            ->when($request->string('q')->toString(), fn ($q, $t) => $q->where('description', 'like', "%{$t}%"))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        // Drop headings and clauses so the reviewer only sees real work.
        $productItems = $items->getCollection()->filter(
            fn ($i) => $parser->isProductLine((string) $i->description, (float) $i->quantity, $i->unit_id !== null)
        );

        $matches = BoqVariantMatch::query()
            ->with(['variant:id,uuid,variant_name,manufacturer_sku,manufacturer_id,product_family_id', 'variant.manufacturer:id,name'])
            ->whereIn('boq_item_id', $productItems->pluck('id'))
            ->orderBy('rank')
            ->get()
            ->groupBy('boq_item_id');

        $suppliers = CatalogSupplier::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.catalog.pricing.boq.show', [
            'boq'          => $boqModel,
            'items'        => $items,
            'productItems' => $productItems,
            'matches'      => $matches,
            'suppliers'    => $suppliers,
            'tiers'        => PriceTierEnum::cases(),
            'sources'      => PriceSourceEnum::cases(),
        ]);
    }

    /** Queue a (re)match of the whole BOQ. */
    public function rematch(int $boq): RedirectResponse
    {
        $this->authorize('catalog.price.match');

        MatchBoqItemsJob::dispatch($boq)->onQueue(config('catalog_research.queue', 'default'));

        return back()->with('success', __('app.boq_match_queued'));
    }

    /** Choose one candidate as the answer for its BOQ line. */
    public function select(Request $request, int $matchId): RedirectResponse
    {
        $this->authorize('catalog.price.review');

        $match = BoqVariantMatch::findOrFail($matchId);

        // Exactly one selection per line — picking a new one clears the rest.
        DB::connection('catalog')->transaction(function () use ($match, $request) {
            BoqVariantMatch::where('boq_item_id', $match->boq_item_id)
                ->update(['is_selected' => false]);

            $match->update([
                'is_selected' => true,
                'status'      => MatchStatusEnum::Confirmed,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', __('app.boq_match_selected'));
    }

    public function reject(Request $request, int $matchId): RedirectResponse
    {
        $this->authorize('catalog.price.review');

        BoqVariantMatch::findOrFail($matchId)->update([
            'status'       => MatchStatusEnum::Rejected,
            'is_selected'  => false,
            'review_notes' => $request->input('notes'),
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        return back()->with('success', __('app.boq_match_rejected'));
    }

    /**
     * Price the matched product without leaving the review screen.
     *
     * Entering the price here — next to the BOQ line that needs it — is what
     * makes pricing tractable: the alternative is finding the same product
     * again among tens of thousands.
     */
    public function priceMatch(Request $request, int $matchId): RedirectResponse
    {
        $this->authorize('catalog.price.manage');

        $data = $request->validate([
            'price'       => ['required', 'numeric', 'min:0'],
            'currency'    => ['nullable', 'string', 'size:3'],
            'price_tier'  => ['required', 'string'],
            'supplier_id' => ['nullable', 'integer'],
            'source'      => ['required', 'string'],
            'source_url'  => ['nullable', 'url', 'max:2048'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $match = BoqVariantMatch::findOrFail($matchId);

        if ($match->product_variant_id === null) {
            return back()->with('error', __('app.match_has_no_variant'));
        }

        $source = PriceSourceEnum::from($data['source']);

        $price = ProductVariantPrice::updateOrCreate(
            [
                'product_variant_id' => $match->product_variant_id,
                'supplier_id'        => $data['supplier_id'] ?? null,
                'price_tier'         => $data['price_tier'],
                'min_quantity'       => 1,
                'currency'           => $data['currency'] ?? 'SAR',
            ],
            [
                'price'      => $data['price'],
                'source'     => $source,
                'confidence' => $source->defaultConfidence(),
                'source_url' => $data['source_url'] ?? null,
                'notes'      => $data['notes'] ?? null,
                'captured_at' => now(),
                'is_active'  => true,
                'created_by' => $request->user()->id,
            ]
        );

        // Reflect the new price on the match straight away, and treat pricing a
        // candidate as choosing it — that is what the user meant.
        DB::connection('catalog')->transaction(function () use ($match, $price, $request) {
            BoqVariantMatch::where('boq_item_id', $match->boq_item_id)->update(['is_selected' => false]);

            $match->update([
                'price_id'     => $price->id,
                'unit_price'   => $price->price,
                'currency'     => $price->currency,
                'price_tier'   => $price->price_tier?->value,
                'price_source' => $price->source?->value,
                'is_selected'  => true,
                'status'       => MatchStatusEnum::Confirmed,
                'reviewed_by'  => $request->user()->id,
                'reviewed_at'  => now(),
            ]);
        });

        return back()->with('success', __('app.price_saved'));
    }
}
