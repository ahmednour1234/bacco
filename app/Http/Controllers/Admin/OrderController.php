<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function pdf(string $uuid)
    {
        $order = Order::with(['client', 'items.product.brand', 'items.unit'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = Pdf::loadView('admin.orders.pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('order-' . $order->order_no . '.pdf');
    }
}
