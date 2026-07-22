<?php

namespace App\Http\Controllers\Admin\Catalog\Pricing;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Pricing\CatalogSupplier;
use App\Services\Catalog\Pricing\SupplierSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Suppliers who quote prices. Distinct from manufacturers, who make products.
 *
 * Scraper-derived suppliers are merged by host, so the same shop listed twice
 * in the scraper DB stays one supplier here.
 */
class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('catalog.price.view');

        $suppliers = CatalogSupplier::query()
            ->withCount('prices')
            ->when($request->string('q')->toString(), fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.catalog.pricing.suppliers', compact('suppliers'));
    }

    /** Re-read scraper sources, merging duplicates into one supplier each. */
    public function sync(SupplierSyncService $service): RedirectResponse
    {
        $this->authorize('catalog.price.manage');

        $result = $service->sync();

        return back()->with('success', __('app.suppliers_synced', [
            'created' => $result['created'],
            'merged'  => $result['merged'],
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('catalog.price.manage');

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'supplier_type' => ['nullable', 'string', 'max:40'],
            'website'       => ['nullable', 'url', 'max:255'],
            'country_code'  => ['nullable', 'string', 'max:8'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:64'],
        ]);

        // Key on the website host when given, so a manually added supplier
        // merges with its scraped counterpart instead of duplicating it.
        $normalized = CatalogSupplier::normalizeHost($data['website'] ?? '')
            ?: Str::slug($data['name']);

        CatalogSupplier::updateOrCreate(
            ['normalized_name' => $normalized],
            $data + ['slug' => Str::slug($data['name']), 'is_active' => true]
        );

        return back()->with('success', __('app.supplier_saved'));
    }
}
