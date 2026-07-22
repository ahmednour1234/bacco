<?php

namespace App\Http\Controllers\Api\Catalog\Research;

use App\Http\Controllers\Api\Catalog\Research\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Research\ProductReviewQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewQueueApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('catalog.review.view');

        $items = ProductReviewQueue::query()
            ->when($request->input('reason'), fn ($q, $r) => $q->where('reason', $r))
            ->when($request->input('status', 'open'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate((int) $request->integer('per_page', 25));

        return $this->ok($items);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $this->authorize('catalog.review.resolve');

        $data = $request->validate([
            'status' => 'required|in:resolved,dismissed',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $review = ProductReviewQueue::findOrFail($id);
        $review->update([
            'status'       => $data['status'],
            'review_notes' => $data['notes'] ?? null,
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        return $this->ok($review, 'Resolved.');
    }
}
