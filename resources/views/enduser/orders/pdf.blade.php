<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->order_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; padding: 20px 36px 14px; }
        .header td.right { text-align: right; }
        .header-border { border-bottom: 2px solid #059669; }
        .company-name { font-size: 22px; font-weight: 700; color: #059669; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .doc-title h1 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .doc-title p  { font-size: 10px; color: #64748b; margin-top: 3px; }

        .meta-table { width: 100%; border-collapse: collapse; border-bottom: 1px solid #e2e8f0; }
        .meta-table td { padding: 10px 36px; vertical-align: top; border-right: 1px solid #f1f5f9; }
        .meta-table td:last-child { border-right: none; }
        .meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 4px; }
        .meta-value { font-size: 11px; font-weight: 600; color: #0f172a; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: capitalize; }
        .status-pending    { background: #fef3c7; color: #92400e; }
        .status-confirmed  { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #e0e7ff; color: #3730a3; }
        .status-shipped    { background: #ede9fe; color: #5b21b6; }
        .status-delivered  { background: #d1fae5; color: #065f46; }
        .status-completed  { background: #dcfce7; color: #14532d; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }
        .status-refunded   { background: #f1f5f9; color: #475569; }

        .section-hdr { background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin-top: 18px; }
        .section-hdr table { width: 100%; border-collapse: collapse; }
        .section-hdr td { padding: 11px 36px; vertical-align: middle; }
        .section-hdr td.right { text-align: right; }
        .section-title { font-size: 12px; font-weight: 700; color: #0f172a; }
        .section-badge { font-size: 10px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 10px; border-radius: 4px; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        table.items thead th { padding: 9px 14px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        table.items thead th.right { text-align: right; }
        table.items tbody tr { border-bottom: 1px solid #f1f5f9; }
        table.items tbody tr:last-child { border-bottom: none; }
        table.items tbody td { padding: 10px 14px; vertical-align: top; }
        table.items tbody td.right { text-align: right; }
        .item-desc  { font-weight: 600; color: #0f172a; font-size: 11px; }
        .item-unit  { font-size: 9px; color: #94a3b8; text-transform: uppercase; margin-top: 2px; }
        .item-qty   { font-weight: 700; color: #0f172a; }
        .item-brand { color: #64748b; }
        .item-price { font-family: monospace; font-weight: 700; color: #0f172a; font-size: 11px; }
        .not-priced { color: #cbd5e1; font-style: italic; font-size: 10px; }

        table.totals-outer { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.totals-inner { width: 100%; border-collapse: collapse; }
        table.totals-inner tr { border-bottom: 1px solid #f1f5f9; }
        table.totals-inner tr:last-child { border-bottom: none; }
        table.totals-inner td { padding: 6px 0; font-size: 11px; }
        table.totals-inner td.lbl { color: #64748b; }
        table.totals-inner td.val { text-align: right; font-family: monospace; font-weight: 600; color: #0f172a; }
        tr.grand td.lbl { font-size: 12px; font-weight: 700; color: #0f172a; border-top: 2px solid #059669; padding-top: 8px; }
        tr.grand td.val { font-size: 14px; font-weight: 700; color: #059669; border-top: 2px solid #059669; padding-top: 8px; }

        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { padding: 12px 36px; font-size: 9px; color: #94a3b8; vertical-align: middle; }
        .footer td.right { text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header-border">
        <table class="header">
            <tr>
                <td>
                    <div class="company-name">Qimta</div>
                    <div class="company-sub">Construction &amp; MEP Supply Platform</div>
                </td>
                <td class="right">
                    <div class="doc-title">
                        <h1>Order #{{ $order->order_no }}</h1>
                        <p>Generated: {{ now()->format('M d, Y  H:i') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Meta strip --}}
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Project Name</div>
                <div class="meta-value">{{ $order->quotationRequest?->project_name ?? '—' }}</div>
            </td>
            <td>
                <div class="meta-label">Client</div>
                <div class="meta-value">{{ $order->client?->name ?? '—' }}</div>
            </td>
            <td>
                <div class="meta-label">Order Date</div>
                <div class="meta-value">{{ $order->created_at ? $order->created_at->format('M d, Y') : '—' }}</div>
            </td>
            <td>
                <div class="meta-label">Status</div>
                <div class="meta-value">
                    <span class="status-badge status-{{ $statusValue }}">{{ $statusLabel }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Items section header --}}
    <div class="section-hdr">
        <table>
            <tr>
                <td><span class="section-title">Bill of Quantities (BOQ)</span></td>
                <td class="right"><span class="section-badge">{{ count($items) }} {{ count($items) === 1 ? 'ITEM' : 'ITEMS' }}</span></td>
            </tr>
        </table>
    </div>

    @if(!empty($items))
    <table class="items">
        <thead>
            <tr>
                <th style="width:24px;">#</th>
                <th>Item Description</th>
                <th style="width:90px;">QTY / Unit</th>
                <th style="width:90px;">Brand</th>
                <th style="width:100px;">Unit Price (SAR)</th>
                <th class="right" style="width:110px;">Total (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                <td><div class="item-desc">{{ $item['description'] ?: '—' }}</div></td>
                <td>
                    <div class="item-qty">{{ number_format((float)$item['quantity']) }}</div>
                    <div class="item-unit">{{ $item['unit'] }}</div>
                </td>
                <td class="item-brand">{{ $item['brand'] !== '—' ? $item['brand'] : '' }}</td>
                <td>
                    @if(is_numeric($item['unit_price']) && (float)$item['unit_price'] > 0)
                        <span class="item-price">{{ number_format((float)$item['unit_price'], 2) }}</span>
                    @else
                        <span class="not-priced">—</span>
                    @endif
                </td>
                <td class="right">
                    @if(is_numeric($item['total_price']) && (float)$item['total_price'] > 0)
                        <span class="item-price">{{ number_format((float)$item['total_price'], 2) }}</span>
                    @else
                        <span class="not-priced">Not priced</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Totals --}}
    <table class="totals-outer">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%; padding: 0 36px 0 0; vertical-align:top;">
                <table class="totals-inner">
                    <tr>
                        <td class="lbl">Subtotal</td>
                        <td class="val">{{ number_format((float)($order->total_amount ?? 0), 2) }} SAR</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tax / VAT (15%)</td>
                        <td class="val">{{ number_format((float)($order->vat_amount ?? 0), 2) }} SAR</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl">Grand Total</td>
                        <td class="val">{{ number_format((float)($order->grand_total ?? 0), 2) }} SAR</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <table>
            <tr>
                <td>Qimta Platform — {{ config('app.url') }}</td>
                <td class="right">This document is system-generated and does not require a signature.</td>
            </tr>
        </table>
    </div>

</body>
</html>
