<?php

namespace App\Http\Controllers\Enduser;

use App\Enums\BoqStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BoqController extends Controller
{
    public function index(): View
    {
        return view('enduser.boqs.index');
    }

    public function create(?string $projectUuid = null): View
    {
        return view('enduser.boqs.create', compact('projectUuid'));
    }

    public function show(string $uuid): View
    {
        $boq = Boq::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        return view('enduser.boqs.show', compact('boq'));
    }

    /**
     * Returns the item count of the latest Draft BOQ for the current user.
     * Used by the floating pill to poll whether AI processing is done.
     */
    public function draftStatus(): JsonResponse
    {
        $boq = Boq::where('client_id', Auth::id())
            ->where('status', BoqStatusEnum::Draft)
            ->withCount('items')
            ->latest()
            ->first();

        $aiStatus = Cache::get('boq_ai_status_' . Auth::id(), 'unknown');

        return response()->json([
            'items_count' => $boq?->items_count ?? 0,
            'ai_status'   => $aiStatus,
            'boq_uuid'    => $boq?->uuid,
        ]);
    }
}
