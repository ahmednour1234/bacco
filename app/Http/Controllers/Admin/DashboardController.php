<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\QuotationRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalQuotations = QuotationRequest::count();
        $activeOrders    = Order::whereNotIn('status', [
            OrderStatusEnum::Completed->value,
            OrderStatusEnum::Cancelled->value,
            OrderStatusEnum::Refunded->value,
        ])->count();
        $totalClients    = User::where('user_type', UserTypeEnum::Client->value)->count();
        $activeProjects  = Project::where('status', 'active')->count();

        $recentOrders = Order::with('client')
            ->latest()
            ->take(5)
            ->get();

        $recentQuotations = QuotationRequest::with('client')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalQuotations',
            'activeOrders',
            'totalClients',
            'activeProjects',
            'recentOrders',
            'recentQuotations',
        ));
    }
}
