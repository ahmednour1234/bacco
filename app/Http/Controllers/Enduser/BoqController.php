<?php

namespace App\Http\Controllers\Enduser;

use App\Enums\BoqStatusEnum;
use App\Enums\BoqTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BoqController extends Controller
{
    public function index(): View
    {
        return view('enduser.boqs.index');
    }

    public function data(Request $request): JsonResponse
    {
        $clientId = Auth::id();

        $allBoqs = Boq::query()->where('client_id', $clientId);
        $stats = [
            'total'     => (clone $allBoqs)->count(),
            'draft'     => (clone $allBoqs)->where('status', 'draft')->count(),
            'submitted' => (clone $allBoqs)->where('status', 'submitted')->count(),
            'completed' => (clone $allBoqs)->where('status', 'completed')->count(),
        ];

        $query = Boq::query()->with(['project', 'items'])->where('client_id', $clientId);

        $sort = $request->input('sort', 'newest');
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        if ($search = $request->input('search', '')) {
            $query->where(function ($q) use ($search) {
                $q->where('boq_no', 'like', '%' . $search . '%')
                  ->orWhereHas('project', fn($r) => $r->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($status = $request->input('status', '')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type', '')) {
            $query->where('type', $type);
        }

        if ($from = $request->input('created_from', '')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('created_to', '')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50]) ? $perPage : 10;
        $page    = max(1, (int) $request->input('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $boqs = $paginator->getCollection()->map(function (Boq $boq) {
            $sv         = $boq->status->value ?? 'draft';
            $isDraft    = $sv === 'draft';
            $itemCount  = $boq->items->count();

            return [
                'id'           => $boq->id,
                'uuid'         => $boq->uuid,
                'boq_no'       => $boq->boq_no,
                'status'       => $sv,
                'status_label' => $boq->status->label(),
                'type'         => $boq->type?->value ?? '',
                'type_label'   => $boq->type?->label() ?? '—',
                'project_name' => $boq->project?->name ?? '—',
                'items_count'  => $itemCount,
                'created_at'   => $boq->created_at?->format('M d, Y') ?? '',
                'is_draft'     => $isDraft,
                'progress'     => match($sv) { 'completed' => 100, 'submitted' => 50, default => 0 },
                'view_url'     => route('enduser.boqs.show', $boq->uuid),
                'edit_url'     => route('enduser.boqs.create') . '?draft=' . $boq->uuid,
                'convert_url'  => route('enduser.boqs.convert', $boq->uuid),
                'duplicate_url'=> route('enduser.boqs.duplicate', $boq->id),
                'delete_url'   => route('enduser.boqs.destroy', $boq->id),
            ];
        });

        return response()->json([
            'stats' => $stats,
            'boqs'  => $boqs,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $boq = Boq::where('id', $id)->where('client_id', Auth::id())->firstOrFail();

        if ($boq->status !== BoqStatusEnum::Draft) {
            return response()->json(['message' => 'Only draft BOQs can be deleted.'], 422);
        }

        $boq->delete();

        return response()->json(['message' => 'BOQ deleted successfully.']);
    }

    public function duplicate(int $id): JsonResponse
    {
        $original = Boq::with('items')->where('id', $id)->where('client_id', Auth::id())->firstOrFail();

        DB::transaction(function () use ($original) {
            $prefix = 'BOQ-' . now()->format('Ymd') . '-';
            do {
                $boqNo = $prefix . strtoupper(Str::random(4));
            } while (Boq::where('boq_no', $boqNo)->exists());

            $newBoq         = $original->replicate(['boq_no', 'uuid', 'status']);
            $newBoq->boq_no = $boqNo;
            $newBoq->uuid   = (string) Str::uuid();
            $newBoq->status = BoqStatusEnum::Draft;
            $newBoq->save();

            foreach ($original->items as $item) {
                $newItem         = $item->replicate(['boq_id']);
                $newItem->boq_id = $newBoq->id;
                $newItem->save();
            }
        });

        return response()->json(['message' => 'BOQ duplicated successfully.']);
    }

    public function convert(string $uuid): JsonResponse
    {
        $boq = Boq::with(['project', 'items'])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if ($boq->status !== BoqStatusEnum::Draft) {
            return response()->json(['message' => 'Only draft BOQs can be converted.'], 422);
        }

        if ($boq->items->isEmpty()) {
            return response()->json(['message' => 'This BOQ has no items. Add items before converting.'], 422);
        }

        try {
            $quotation = DB::transaction(function () use ($boq) {
                $prefix = 'QR-' . now()->format('Ymd') . '-';
                do {
                    $candidate = $prefix . strtoupper(Str::random(4));
                } while (QuotationRequest::where('quotation_no', $candidate)->exists());

                $quotation = QuotationRequest::create([
                    'client_id'    => Auth::id(),
                    'project_id'   => $boq->project_id,
                    'boq_id'       => $boq->id,
                    'quotation_no' => $candidate,
                    'project_name' => $boq->project?->name,
                    'status'       => QuotationRequestStatusEnum::Tender,
                    'source_type'  => QuotationSourceTypeEnum::Manual,
                ]);

                foreach ($boq->items as $item) {
                    QuotationItem::create([
                        'quotation_request_id' => $quotation->id,
                        'product_id'           => $item->product_id,
                        'description'          => (string) $item->description,
                        'quantity'             => (float) $item->quantity,
                        'unit_id'              => $item->unit_id,
                        'category'             => (string) ($item->category ?? ''),
                        'brand'                => (string) ($item->brand ?? ''),
                        'status'               => 'pending',
                        'engineering_required' => (bool) $item->engineering_required,
                        'confidence'           => $item->confidence,
                        'ai_extracted'         => (bool) $item->ai_extracted,
                        'is_selected'          => true,
                    ]);
                }

                $boq->update(['status' => BoqStatusEnum::Completed]);

                return $quotation;
            });

            return response()->json([
                'message'  => 'Converted successfully.',
                'redirect' => route('enduser.quotations.show', $quotation->uuid),
            ]);

        } catch (\Throwable $e) {
            Log::error('BoqController::convert failed.', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create quotation. Please try again.'], 500);
        }
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

    public function draftStatus(): JsonResponse
    {
        $boq = Boq::where('client_id', Auth::id())
            ->where('status', BoqStatusEnum::Draft)
            ->withCount('items')
            ->latest()
            ->first();

        $aiStatus  = Cache::get('boq_ai_status_' . Auth::id());
        $startedAt = Cache::get('boq_ai_started_at_' . Auth::id());

        if ($aiStatus === null || ($aiStatus === 'running' && $startedAt && (now()->timestamp - $startedAt) > 300)) {
            $aiStatus = 'failed';
            Cache::put('boq_ai_status_' . Auth::id(), 'failed', now()->addMinutes(30));
            if ($startedAt) {
                Cache::put('boq_ai_message_' . Auth::id(), 'Processing timed out. Please try extracting again.', now()->addMinutes(30));
            }
        }

        return response()->json([
            'items_count' => $boq?->items_count ?? 0,
            'ai_status'   => $aiStatus ?? 'failed',
            'boq_uuid'    => $boq?->uuid,
        ]);
    }
}
