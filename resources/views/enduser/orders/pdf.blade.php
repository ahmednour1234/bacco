<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->order_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        /* ── Header ── */
        .header { padding: 28px 36px 18px; border-bottom: 2px solid #059669; display: flex; justify-content: space-between; align-items: flex-start; }
        .company-name { font-size: 22px; font-weight: 700; color: #059669; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .doc-title    { text-align: right; }
        .doc-title h1 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .doc-title p  { font-size: 10px; color: #64748b; margin-top: 3px; }

        /* ── Meta strip ── */
        .meta { display: flex; gap: 0; border-bottom: 1px solid #e2e8f0; }
        .meta-cell { flex: 1; padding: 12px 36px 12px 36px; border-right: 1px solid #f1f5f9; }
        .meta-cell:last-child { border-right: none; }
        .meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 4px; }
        .meta-value { font-size: 11px; font-weight: 600; color: #0f172a; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: capitalize; }
        .status-pending    { background: #fef3c7; color: #92400e; }
        .status-confirmed  { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #e0e7ff; color: #3730a3; }
        .status-shipped    { background: #ede9fe; color: #5b21b6; }
        .status-delivered  { background: #d1fae5; color: #065f46; }
        .status-completed  { background: #dcfce7; color: #14532d; }
        .status-cancelled  { background: #fee2e2; color: #991b1b; }

        /* ── Section heading ── */
        .section-header { padding: 14px 36px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; margin-top: 18px; }
        .section-title { font-size: 12px; font-weight: 700; color: #0f172a; }
        .section-badge { font-size: 10px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 10px; border-radius: 12px; }

        /* ── Items table ── */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        thead th { padding: 9px 14px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 10px 14px; vertical-align: top; }
        tbody td.right { text-align: right; }
        .item-desc  { font-weight: 600; color: #0f172a; font-size: 11px; }
        .item-unit  { font-size: 9px; color: #94a3b8; text-transform: uppercase; margin-top: 2px; }
        .item-qty   { font-weight: 700; color: #0f172a; }
        .item-brand { color: #64748b; }
        .item-price { font-family: monospace; font-weight: 700; color: #0f172a; font-size: 11px; }
        .not-priced { color: #cbd5e1; font-style: italic; font-size: 10px; }

        /* ── Totals ── */
        .totals-wrap { padding: 18px 36px; display: flex; justify-content: flex-end; page-break-inside: avoid; }
        .totals-box  { width: 260px; }
        .totals-row  { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f1f5f9; }
        .totals-row:last-child { border-bottom: none; }
        .totals-label { font-size: 10px; color: #64748b; }
        .totals-val   { font-size: 11px; font-family: monospace; font-weight: 600; color: #0f172a; }
        .totals-grand { border-top: 2px solid #059669; margin-top: 6px; padding-top: 8px; }
        .totals-grand .totals-label { font-size: 11px; font-weight: 700; color: #0f172a; }
        .totals-grand .totals-val   { font-size: 14px; font-weight: 700; color: #059669; }

        /* ── Footer ── */
        .footer { margin-top: 30px; padding: 14px 36px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .footer-note { font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <div>
            <div class="company-name">Qimta</div>
            <div class="company-sub">Construction &amp; MEP Supply Platform</div>
        </div>
        <div class="doc-title">
            <h1>Order #{{ $order->order_no }}</h1>
            <p>Generated: {{ now()->format('M d, Y  H:i') }}</p>
        </div>
    </div>

    {{-- ── Meta strip ── --}}
    @php
        $sv = $order->status->value ?? 'pending';
    @endphp
    <div class="meta">
        <div class="meta-cell">
            <div class="meta-label">Project Name</div>
            <div class="meta-value">{{ $order->quotationRequest?->project_name ?? '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Client</div>
            <div class="meta-value">{{ $order->client?->name ?? '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Order Date</div>
            <div class="meta-value">{{ $order->created_at->format('M d, Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Status</div>
            <div class="meta-value">
                <span class="status-badge status-{{ $sv }}">{{ $order->status->label() }}</span>
            </div>
        </div>
    </div>

    {{-- ── BOQ Section ── --}}
    <div class="section-header">
        <span class="section-title">Bill of Quantities (BOQ)</span>
        <span class="section-badge">{{ count($items) }} {{ count($items) === 1 ? 'ITEM' : 'ITEMS' }}</span>
    </div>

    @if(!empty($items))
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>QTY / Unit</th>
                <th>Brand</th>
                <th>Unit Price (SAR)</th>
                <th class="right">Total (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td style="color:#94a3b8; width:28px;">{{ $i + 1 }}</td>
                <td>
                    <div class="item-desc">{{ $item['description'] ?: '—' }}</div>
                </td>
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

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <div class="totals-box">
            <div class="totals-row">
                <span class="totals-label">Subtotal</span>
                <span class="totals-val">{{ number_format((float)$order->total_amount, 2) }} SAR</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">Tax / VAT (15%)</span>
                <span class="totals-val">{{ number_format((float)$order->vat_amount, 2) }} SAR</span>
            </div>
            <div class="totals-row totals-grand">
                <span class="totals-label">Grand Total</span>
                <span class="totals-val">{{ number_format((float)$order->grand_total, 2) }} SAR</span>
            </div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <span class="footer-note">Qimta Platform — {{ config('app.url') }}</span>
        <span class="footer-note">This document is system-generated and does not require a signature.</span>
    </div>

</body>
</html>
