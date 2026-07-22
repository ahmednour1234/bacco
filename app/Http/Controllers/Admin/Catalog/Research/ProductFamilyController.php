<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Repositories\Catalog\Research\ProductFamilyRepository;
use App\Repositories\Catalog\Research\ResearchJobRepository;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductFamilyController extends Controller
{
    public function __construct(
        private ProductFamilyRepository $families,
        private ResearchPlanService     $plan,
        private ResearchJobRepository   $jobs,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('catalog.family.view');

        $items = $this->families->paginate(20, $request->only(['search', 'division_id', 'research_status']));

        return view('admin.catalog.research.families.index', compact('items'));
    }

    public function show(string $uuid): View
    {
        $this->authorize('catalog.family.view');

        $family   = $this->families->findByUuid($uuid);
        $progress = $this->plan->progress($family);
        $family->load(['manufacturers', 'variants.manufacturer', 'sourceRows']);
        $jobs     = $this->jobs->paginate(15, ['product_family_id' => $family->id]);

        return view('admin.catalog.research.families.show', compact('family', 'progress', 'jobs'));
    }

    public function startResearch(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.research.start');

        return $this->guard($uuid, fn ($family) => $this->plan->start($family), 'Research started.');
    }

    public function pauseResearch(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.research.pause');

        return $this->guard($uuid, fn ($family) => $this->plan->pause($family), 'Research paused.');
    }

    public function resumeResearch(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.research.start');

        return $this->guard($uuid, fn ($family) => $this->plan->resume($family), 'Research resumed.');
    }

    public function cancelResearch(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.research.cancel');

        return $this->guard($uuid, fn ($family) => $this->plan->cancel($family), 'Research cancelled.');
    }

    /** Run an action against a family and flash success/error. */
    private function guard(string $uuid, callable $action, string $success): RedirectResponse
    {
        $family = $this->families->findByUuid($uuid);

        try {
            $action($family);
            $flash = ['success' => $success];
        } catch (\Throwable $e) {
            $flash = ['error' => $e->getMessage()];
        }

        return redirect()
            ->route('admin.catalog.research.families.show', $family->uuid)
            ->with($flash);
    }
}
