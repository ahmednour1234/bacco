<?php

namespace App\Http\Controllers\Api\Catalog\Research;

use App\Enums\Catalog\Research\VerificationStatusEnum;
use App\Http\Controllers\Api\Catalog\Research\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\Research\ProductVariantResource;
use App\Repositories\Catalog\Research\ProductVariantRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantApiController extends Controller
{
    use ApiResponse;

    public function __construct(private ProductVariantRepository $variants) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('catalog.product.view');

        $page = $this->variants->filtered($request->all(), (int) $request->integer('per_page', 25));

        return $this->ok([
            'items' => ProductVariantResource::collection($page->items()),
            'meta'  => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $this->authorize('catalog.product.view');

        $variant = $this->variants->findByUuid($uuid)->load([
            'manufacturer', 'size', 'connectionType', 'pressureRating', 'approvals',
        ]);

        return $this->ok(new ProductVariantResource($variant));
    }

    public function verify(string $uuid, Request $request): JsonResponse
    {
        $this->authorize('catalog.product.verify');

        $variant = $this->variants->findByUuid($uuid);
        $variant->update(['verification_status' => VerificationStatusEnum::Verified]);

        return $this->ok(new ProductVariantResource($variant), 'Variant verified.');
    }

    public function reject(string $uuid, Request $request): JsonResponse
    {
        $this->authorize('catalog.product.reject');

        $variant = $this->variants->findByUuid($uuid);
        $variant->update([
            'verification_status' => VerificationStatusEnum::Rejected,
            'technical_notes'     => trim(($variant->technical_notes ?? '') . ' ' . $request->string('reason')),
        ]);

        return $this->ok(new ProductVariantResource($variant), 'Variant rejected.');
    }
}
