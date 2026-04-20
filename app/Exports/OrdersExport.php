<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return Order::with(['quotationRequest', 'client.clientProfile', 'items'])->latest();
    }

    public function headings(): array
    {
        return [
            __('app.order_hash'),
            __('app.project'),
            __('app.client'),
            __('app.company'),
            __('app.status'),
            __('app.items'),
            __('app.amount_sar'),
            __('app.date'),
        ];
    }

    public function map($order): array
    {
        $itemsTotal = $order->items->sum(fn($i) => (float) ($i->total_price ?? 0));
        $displayAmount = $itemsTotal > 0 ? $itemsTotal : (float) $order->total_amount;

        return [
            $order->order_no,
            $order->quotationRequest?->project_name ?? '—',
            $order->client?->name ?? '—',
            $order->client?->clientProfile?->company_name ?? '—',
            $order->status?->value ?? '—',
            $order->items->count(),
            number_format($displayAmount, 2),
            $order->created_at?->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
