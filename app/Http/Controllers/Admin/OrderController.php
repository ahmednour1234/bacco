<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function show(string $uuid)
    {
        return view('admin.orders.show', compact('uuid'));
    }

    public function export()
    {
        return Excel::download(new OrdersExport, 'orders-' . now()->format('Y-m-d') . '.xlsx');
    }
}
