<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Repositories\Catalog\Research\ResearchJobRepository;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchJobController extends Controller
{
    public function __construct(
        private ResearchJobRepository $jobs,
        private ResearchPlanService   $plan,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('catalog.research.start');

        $items = $this->jobs->paginate(25, $request->only(['status', 'job_type', 'product_family_id']));

        return view('admin.catalog.research.jobs.index', compact('items'));
    }

    public function show(string $uuid): View
    {
        $this->authorize('catalog.research.start');

        $job = $this->jobs->findByUuid($uuid);
        $job->load(['family', 'manufacturer', 'results']);

        return view('admin.catalog.research.jobs.show', compact('job'));
    }

    public function retry(string $uuid): RedirectResponse
    {
        $this->authorize('catalog.research.retry');

        $job = $this->jobs->findByUuid($uuid);

        try {
            $this->plan->retryJob($job);
            $flash = ['success' => 'Job re-queued.'];
        } catch (\Throwable $e) {
            $flash = ['error' => $e->getMessage()];
        }

        return redirect()->route('admin.catalog.research.jobs.show', $job->uuid)->with($flash);
    }
}
