<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $clientId = Auth::id();

        // ── Stats ────────────────────────────────────────────────────────────
        $allQuotations = QuotationRequest::where('client_id', $clientId);

        $stats = [
            'total_quotations'     => (clone $allQuotations)->count(),
            'active_quotations'    => (clone $allQuotations)->whereIn('status', ['submitted', 'in_review', 'quoted'])->count(),
            'completed_quotations' => (clone $allQuotations)->where('status', 'accepted')->count(),
            'active_projects'      => Project::where('client_id', $clientId)->where('status', 'active')->count(),
            'completed_projects'   => Project::where('client_id', $clientId)->where('status', 'completed')->count(),
            'accepted_quotations'  => (clone $allQuotations)->where('status', 'tender')->count(),
        ];

        // ── Recent Quotations (latest 5) ─────────────────────────────────────
        $recentQuotations = QuotationRequest::withCount('items')
            ->where('client_id', $clientId)
            ->latest()
            ->limit(5)
            ->get();

        // ── Tender Quotations (ready for order) ──────────────────────────────
        $acceptedQuotations = QuotationRequest::with(['items' => fn($q) => $q->select('id', 'quotation_request_id', 'unit_price', 'quantity')])
            ->where('client_id', $clientId)
            ->where('status', 'tender')
            ->latest()
            ->limit(5)
            ->get();

        // ── Active Projects (latest 5) ───────────────────────────────────────
        $activeProjects = Project::where('client_id', $clientId)
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        return view('enduser.dashboard', compact('stats', 'recentQuotations', 'acceptedQuotations', 'activeProjects'));
    }
}
