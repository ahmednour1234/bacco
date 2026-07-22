<?php

namespace App\Http\Controllers\Api\Catalog\Research;

use App\Http\Controllers\Api\Catalog\Research\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Catalog\Research\ProductFamilyRepository;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductFamilyApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProductFamilyRepository $families,
        private ResearchPlanService     $plan,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('catalog.family.view');

        $page = $this->families->paginate((int) $request->integer('per_page', 25), $request->all());

        return $this->ok([
            'items' => $page->items(),
            'meta'  => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $this->authorize('catalog.family.view');

        $family = $this->families->findByUuid($uuid)->load(['manufacturers', 'division', 'category']);

        return $this->ok($family);
    }

    public function research(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.start');

        return $this->action($uuid, fn ($f) => $this->plan->start($f), 'Research started.');
    }

    public function pause(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.pause');

        return $this->action($uuid, fn ($f) => $this->plan->pause($f), 'Research paused.');
    }

    public function resume(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.start');

        return $this->action($uuid, fn ($f) => $this->plan->resume($f), 'Research resumed.');
    }

    public function cancel(string $uuid): JsonResponse
    {
        $this->authorize('catalog.research.cancel');

        return $this->action($uuid, fn ($f) => $this->plan->cancel($f), 'Research cancelled.');
    }

    private function action(string $uuid, callable $fn, string $message): JsonResponse
    {
        $family = $this->families->findByUuid($uuid);

        try {
            $fn($family);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->ok(['research_status' => $family->fresh()->research_status?->value], $message);
    }
}
