<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user          = Auth::user();
        $totalProducts = SupplierProduct::where('supplier_id', $user->id)->count();
        $activeProducts   = SupplierProduct::where('supplier_id', $user->id)->where('active', true)->count();
        $inactiveProducts = SupplierProduct::where('supplier_id', $user->id)->where('active', false)->count();

        return view('supplier.dashboard', compact('totalProducts', 'activeProducts', 'inactiveProducts'));
    }
}
