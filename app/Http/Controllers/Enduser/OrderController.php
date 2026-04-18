<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Enduser\OrderRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            return $this->downloadPdf($order);
        }

        return view('enduser.orders.show', compact('order'));
    }

    private function downloadPdf(Order $order): Response
    {
        try {
            $items = $order->items->map(fn ($item) => [
                'description' => (string) ($item->description ?? ''),
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit?->name ?? '—',
                'brand'       => $item->product?->brand?->name ?? '—',
                'unit_price'  => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray();

            $statusLabel = $order->status?->label() ?? ($order->getRawOriginal('status') ?? 'pending');
            $statusValue = $order->status?->value ?? ($order->getRawOriginal('status') ?? 'pending');

            $pdf = Pdf::loadView('enduser.orders.pdf', [
                'order'       => $order,
                'items'       => $items,
                'statusLabel' => $statusLabel,
                'statusValue' => $statusValue,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('Order-' . $order->order_no . '.pdf');

        } catch (\Throwable $e) {
            Log::error('OrderController: PDF generation failed.', [
                'order_uuid' => $order->uuid,
                'order_no'   => $order->order_no,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            abort(500, 'PDF generation failed: ' . $e->getMessage());
        }
    }
}

