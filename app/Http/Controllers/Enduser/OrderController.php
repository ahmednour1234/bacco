<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Enduser\OrderRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderRepository $repo) {}

    public function index(): View
    {
        return view('enduser.orders.index');
    }

    public function show(Request $request, string $uuid): View|Response
    {
        $order = Order::with([
            'items.product.brand',
            'items.unit',
            'quotationRequest',
            'client.clientProfile',
            'logisticsUpdates',
            'engineeringUpdates',
        ])
            ->where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        if ($request->query('export') === 'pdf') {
            $items = $order->items->map(fn($item) => [
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit?->name ?? '—',
                'brand'       => $item->product?->brand?->name ?? '—',
                'unit_price'  => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray();

            $pdf = Pdf::loadView('enduser.orders.pdf', compact('order', 'items'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Order-' . $order->order_no . '.pdf');
        }

        return view('enduser.orders.show', compact('order'));
    }
}

