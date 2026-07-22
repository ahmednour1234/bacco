<?php

namespace App\Http\Controllers\Api\Catalog\Research;

use App\Http\Controllers\Api\Catalog\Research\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Catalog\Research\ResearchJobRepository;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchJobApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ResearchJobRepository $jobs,
        private ResearchPlanService   $plan,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('catalog.research.start');

        return $this->ok($this->jobs->paginate(25, $request->only(['status', 'job_type', 'product_family_id'])));
    }

    public function show(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.start');

        return $this->ok($this->jobs->findByUuid($uuid)->load(['results', 'family', 'manufacturer']));
    }

    public function retry(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.retry');

        $job = $this->jobs->findByUuid($uuid);

        try {
            $this->plan->retryJob($job);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->ok(['uuid' => $job->uuid], 'Job re-queued.');
    }
}
