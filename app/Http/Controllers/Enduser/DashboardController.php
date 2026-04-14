<?php

namespace App\Http\Controllers\Enduser;

use App\Enums\OrderStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $clientId = Auth::id();

        // ── Quotation stats ──────────────────────────────────────────────────
        $allQuotations = QuotationRequest::where('client_id', $clientId);

        $stats = [
            'total_quotations'     => (clone $allQuotations)->count(),
            'active_quotations'    => (clone $allQuotations)
                ->whereIn('status', [
                    QuotationRequestStatusEnum::Submitted,
                    QuotationRequestStatusEnum::InReview,
                    QuotationRequestStatusEnum::Quoted,
                ])->count(),
            'completed_quotations' => (clone $allQuotations)
                ->where('status', QuotationRequestStatusEnum::Accepted)->count(),
            'accepted_quotations'  => (clone $allQuotations)
                ->where('status', QuotationRequestStatusEnum::Tender)->count(),
        ];

        // ── Order stats ──────────────────────────────────────────────────────
        $allOrders = Order::where('client_id', $clientId);

        $stats['total_orders']  = (clone $allOrders)->count();
        $stats['active_orders'] = (clone $allOrders)
            ->whereNotIn('status', [
                OrderStatusEnum::Completed,
                OrderStatusEnum::Cancelled,
                OrderStatusEnum::Refunded,
            ])->count();

        // ── Project stats ────────────────────────────────────────────────────
        $stats['active_projects']    = Project::where('client_id', $clientId)
            ->where('status', ProjectStatusEnum::Active)->count();
        $stats['completed_projects'] = Project::where('client_id', $clientId)
            ->where('status', ProjectStatusEnum::Completed)->count();

        // ── Recent Quotations (latest 5) ─────────────────────────────────────
        $recentQuotations = QuotationRequest::withCount('items')
            ->where('client_id', $clientId)
            ->latest()
            ->limit(5)
            ->get();

        // ── Tender Quotations (ready for order) ──────────────────────────────
        $acceptedQuotations = QuotationRequest::withCount('items')
            ->where('client_id', $clientId)
            ->where('status', QuotationRequestStatusEnum::Tender)
            ->latest()
            ->limit(5)
            ->get();

        // ── Active Projects (latest 5) ───────────────────────────────────────
        $activeProjects = Project::where('client_id', $clientId)
            ->where('status', ProjectStatusEnum::Active)
            ->latest()
            ->limit(5)
            ->get();

        // ── Recent Orders (latest 5) ─────────────────────────────────────────
        $recentOrders = Order::where('client_id', $clientId)
            ->latest()
            ->limit(5)
            ->get();

        return view('enduser.dashboard', compact(
            'stats',
            'recentQuotations',
            'acceptedQuotations',
            'activeProjects',
            'recentOrders',
        ));
    }
}
