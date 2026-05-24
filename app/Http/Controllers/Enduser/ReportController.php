<?php

namespace App\Http\Controllers\Enduser;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Project;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $clientId = Auth::id();

        // ── Quotation breakdown by status ────────────────────────────────────
        $quotationsByStatus = QuotationRequest::where('client_id', $clientId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Order breakdown by status ────────────────────────────────────────
        $ordersByStatus = Order::where('client_id', $clientId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Financial summary ────────────────────────────────────────────────
        $financials = [
            'total_order_value' => Order::where('client_id', $clientId)->sum('grand_total'),
            'total_paid'        => Payment::where('client_id', $clientId)
                ->where('status', PaymentStatusEnum::Approved)->sum('amount'),
            'pending_payments'  => Payment::where('client_id', $clientId)
                ->whereIn('status', [PaymentStatusEnum::Pending, PaymentStatusEnum::Submitted])
                ->sum('amount'),
        ];
        $financials['outstanding'] = $financials['total_order_value'] - $financials['total_paid'];

        // ── Project breakdown by status ──────────────────────────────────────
        $projectsByStatus = Project::where('client_id', $clientId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Monthly order trend (last 6 months) ─────────────────────────────
        $monthlyOrders = Order::where('client_id', $clientId)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('count(*) as count'),
                DB::raw('COALESCE(sum(grand_total), 0) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ── Recent payments ──────────────────────────────────────────────────
        $recentPayments = Payment::with('order')
            ->where('client_id', $clientId)
            ->latest()
            ->limit(10)
            ->get();

        return view('enduser.reports', compact(
            'quotationsByStatus',
            'ordersByStatus',
            'financials',
            'projectsByStatus',
            'monthlyOrders',
            'recentPayments',
        ));
    }
}
