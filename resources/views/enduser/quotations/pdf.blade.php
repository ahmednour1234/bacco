<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation #{{ $quotation->quotation_no }}</title>
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
        .meta-cell { flex: 1; padding: 12px 36px; border-right: 1px solid #f1f5f9; }
        .meta-cell:last-child { border-right: none; }
        .meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 4px; }
        .meta-value { font-size: 11px; font-weight: 600; color: #0f172a; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .status-tender    { background: #dbeafe; color: #1e40af; }
        .status-submitted { background: #e0e7ff; color: #3730a3; }
        .status-in_review { background: #fef3c7; color: #92400e; }
        .status-quoted    { background: #d1fae5; color: #065f46; }
        .status-accepted  { background: #dcfce7; color: #14532d; }
        .status-rejected  { background: #fee2e2; color: #991b1b; }
        .status-draft     { background: #f1f5f9; color: #475569; }

        /* ── Section heading ── */
        .section-header { padding: 12px 36px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .section-title  { font-size: 12px; font-weight: 700; color: #0f172a; }
        .section-badge  { font-size: 10px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 10px; border-radius: 12px; }

        /* ── Items table ── */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        thead th { padding: 9px 12px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 9px 12px; vertical-align: top; }
        tbody td.right { text-align: right; font-family: monospace; }
        tbody td.center { text-align: center; }
        .item-no   { color: #94a3b8; width: 28px; }
        .item-desc { font-weight: 600; color: #0f172a; font-size: 11px; }
        .item-sub  { font-size: 9px; color: #94a3b8; margin-top: 2px; }
        .item-qty  { font-weight: 700; color: #0f172a; }
        .status-pending  { background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .status-sourcing { background: #d1fae5; color: #065f46; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .status-sourced  { background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .status-rejected { background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .not-priced { color: #cbd5e1; font-style: italic; font-size: 10px; }
        .price { font-family: monospace; font-weight: 700; color: #0f172a; }

        /* ── Totals ── */
        .totals-wrap { padding: 18px 36px; display: flex; justify-content: flex-end; page-break-inside: avoid; }
        .totals-box  { width: 280px; }
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
            <h1>Quotation #{{ $quotation->quotation_no }}</h1>
            <p>Generated: {{ now()->format('M d, Y  H:i') }}</p>
        </div>
    </div>

    {{-- ── Meta strip ── --}}
    @php
        $sv = $quotation->status->value ?? 'draft';
    @endphp
    <div class="meta">
        <div class="meta-cell">
            <div class="meta-label">Project</div>
            <div class="meta-value">{{ $quotation->project_name ?: '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Client</div>
            <div class="meta-value">{{ $quotation->client?->name ?? '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Date</div>
            <div class="meta-value">{{ $quotation->created_at->format('M d, Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Status</div>
            <div class="meta-value">
                <span class="status-badge status-{{ $sv }}">{{ $quotation->status->label() }}</span>
            </div>
        </div>
    </div>

    {{-- ── Items section ── --}}
    @php
        $allItems    = $quotation->items()->with('unit')->get();
        $itemCount   = $allItems->count();
        $subtotal    = $allItems->filter(fn($i) => $i->is_selected && is_numeric($i->unit_price) && $i->unit_price > 0)
                                ->sum(fn($i) => (float) $i->unit_price * (float) $i->quantity);
        $tax         = $subtotal * 0.15;
        $grand       = $subtotal + $tax;
    @endphp

    <div class="section-header">
        <span class="section-title">Bill of Quantities (BOQ)</span>
        <span class="section-badge">{{ $itemCount }} {{ $itemCount === 1 ? 'ITEM' : 'ITEMS' }}</span>
    </div>

    @if($itemCount > 0)
    <table>
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Item Description</th>
                <th style="width:60px;">Qty</th>
                <th style="width:50px;">Unit</th>
                <th style="width:70px;">Category</th>
                <th style="width:60px;" class="center">Status</th>
                <th style="width:90px;" class="right">Unit Price (SAR)</th>
                <th style="width:100px;" class="right">Total (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allItems as $i => $item)
            @php
                $statusVal = $item->status->value ?? 'pending';
                $hasPrice  = is_numeric($item->unit_price) && (float)$item->unit_price > 0;
                $lineTotal = $hasPrice ? (float)$item->unit_price * (float)$item->quantity : null;
            @endphp
            <tr>
                <td class="item-no">{{ $i + 1 }}</td>
                <td>
                    <div class="item-desc">{{ $item->description ?: '—' }}</div>
                    @if($item->brand)
                        <div class="item-sub">{{ $item->brand }}</div>
                    @endif
                </td>
                <td class="item-qty">{{ number_format((float)$item->quantity, 0) }}</td>
                <td style="color:#64748b;">{{ $item->unit?->name ?: '—' }}</td>
                <td style="color:#64748b; font-size:10px;">{{ $item->category ?: '—' }}</td>
                <td class="center">
                    <span class="status-{{ $statusVal }}">{{ strtoupper($statusVal) }}</span>
                </td>
                <td class="right">
                    @if($hasPrice)
                        <span class="price">{{ number_format((float)$item->unit_price, 2) }}</span>
                    @else
                        <span class="not-priced">—</span>
                    @endif
                </td>
                <td class="right">
                    @if($lineTotal !== null)
                        <span class="price">{{ number_format($lineTotal, 2) }}</span>
                    @else
                        <span class="not-priced">—</span>
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
                <span class="totals-label">Subtotal (selected items)</span>
                <span class="totals-val">{{ number_format($subtotal, 2) }} SAR</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">Tax / VAT (15%)</span>
                <span class="totals-val">{{ number_format($tax, 2) }} SAR</span>
            </div>
            <div class="totals-row totals-grand">
                <span class="totals-label">Grand Total</span>
                <span class="totals-val">{{ number_format($grand, 2) }} SAR</span>
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
