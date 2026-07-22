<?php

namespace App\Http\Controllers\Admin\Catalog\Research;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Research\ProductDuplicateCandidate;
use App\Models\Catalog\Research\ProductReviewQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewQueueController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('catalog.review.view');

        $items = ProductReviewQueue::query()
            ->when($request->input('reason'), fn ($q, $r) => $q->where('reason', $r))
            ->when($request->input('status', 'open'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $duplicates = ProductDuplicateCandidate::with(['first', 'second'])
            ->where('status', 'open')
            ->latest()
            ->paginate(15, ['*'], 'dup_page');

        return view('admin.catalog.research.review.index', compact('items', 'duplicates'));
    }

    public function resolve(Request $request, int $id): RedirectResponse
    {
        $this->authorize('catalog.review.resolve');

        $request->validate([
            'status' => 'required|in:resolved,dismissed',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $review = ProductReviewQueue::findOrFail($id);
        $review->update([
            'status'       => $request->input('status'),
            'review_notes' => $request->input('notes'),
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        return back()->with('success', 'Review item resolved.');
    }
}
