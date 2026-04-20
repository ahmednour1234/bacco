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
        .header { padding: 28px 36px 18px; border-bottom: 2px solid #059669; }
        .header-inner { width: 100%; }
        .logo-img { height: 40px; }
        .company-name { font-size: 22px; font-weight: 700; color: #059669; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .doc-title h1 { font-size: 18px; font-weight: 700; color: #0f172a; text-align: right; }
        .doc-title p  { font-size: 10px; color: #64748b; margin-top: 3px; text-align: right; }

        /* ── Meta ── */
        .meta { border-bottom: 1px solid #e2e8f0; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 12px 20px; border-right: 1px solid #f1f5f9; vertical-align: top; }
        .meta td:last-child { border-right: none; }
        .meta-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 4px; }
        .meta-value { font-size: 11px; font-weight: 600; color: #0f172a; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .status-open   { background: #dbeafe; color: #1e40af; }
        .status-closed { background: #d1fae5; color: #065f46; }

        /* ── Section heading ── */
        .section-header { padding: 12px 36px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin-top: 20px; }
        .section-title  { font-size: 12px; font-weight: 700; color: #0f172a; display: inline-block; }
        .section-badge  { font-size: 10px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 10px; border-radius: 12px; float: right; }

        /* ── Items table ── */
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        table.items thead th { padding: 9px 12px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        table.items thead th.right { text-align: right; }
        table.items tbody tr { border-bottom: 1px solid #f1f5f9; }
        table.items tbody tr:last-child { border-bottom: none; }
        table.items tbody td { padding: 9px 12px; vertical-align: top; }
        table.items tbody td.right { text-align: right; font-family: monospace; }
        .item-no   { color: #94a3b8; width: 28px; }
        .item-desc { font-weight: 600; color: #0f172a; font-size: 11px; }
        .item-sub  { font-size: 9px; color: #94a3b8; margin-top: 2px; }
        .item-qty  { font-weight: 700; color: #0f172a; }
        .price     { font-family: monospace; font-weight: 700; color: #0f172a; }
        .discount  { color: #d97706; font-size: 10px; font-weight: 600; }
        .not-priced { color: #cbd5e1; font-style: italic; font-size: 10px; }

        /* ── Totals ── */
        .totals-wrap { padding: 18px 36px; page-break-inside: avoid; }
        .totals-table { width: 280px; border-collapse: collapse; margin-left: auto; }
        .totals-table td { padding: 6px 0; font-size: 11px; }
        .totals-table td.label { color: #64748b; font-size: 10px; }
        .totals-table td.val { text-align: right; font-family: monospace; font-weight: 600; color: #0f172a; }
        .totals-table tr.grand td { border-top: 2px solid #059669; padding-top: 10px; }
        .totals-table tr.grand td.label { font-size: 11px; font-weight: 700; color: #0f172a; }
        .totals-table tr.grand td.val { font-size: 14px; font-weight: 700; color: #059669; }

        /* ── Terms ── */
        .terms { padding: 20px 36px; page-break-inside: avoid; }
        .terms-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .terms-line { border-top: 2px solid #059669; margin-bottom: 12px; }
        .terms-list { padding-left: 16px; }
        .terms-list li { font-size: 10px; color: #475569; margin-bottom: 4px; line-height: 1.5; }

        /* ── Authorization ── */
        .auth { padding: 20px 36px; page-break-inside: avoid; }
        .auth-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .auth-line  { border-top: 2px solid #059669; margin-bottom: 16px; }
        .auth-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
        .auth-table td { padding: 14px 16px; border: 1px solid #e2e8f0; vertical-align: top; width: 50%; height: 80px; }
        .auth-label { font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 40px; }
        .auth-hint  { font-size: 9px; color: #94a3b8; font-style: italic; }

        /* ── Footer ── */
        .footer { margin-top: 30px; padding: 14px 36px; border-top: 1px solid #e2e8f0; }
        .footer-note { font-size: 9px; color: #94a3b8; }
        .footer-right { float: right; }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none; padding:0;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="Qimta">
                    @else
                        <div class="company-name">Qimta</div>
                    @endif
                    <div class="company-sub">Construction &amp; MEP Supply Platform</div>
                </td>
                <td style="border:none; padding:0; text-align:right;">
                    <div class="doc-title">
                        <h1>Order #{{ $order->order_no }}</h1>
                        <p>Generated: {{ now()->format('M d, Y  H:i') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Meta strip ── --}}
    @php
        $sv = $order->status->value ?? 'open';
        $statusLabel = match($sv) {
            'open' => 'Open',
            'closed' => 'Closed',
            default => ucfirst($sv),
        };
    @endphp
    <div class="meta">
        <table>
            <tr>
                <td>
                    <div class="meta-label">Client</div>
                    <div class="meta-value">{{ $order->client?->name ?? '—' }}</div>
                </td>
                <td>
                    <div class="meta-label">Order Date</div>
                    <div class="meta-value">{{ $order->created_at->format('M d, Y') }}</div>
                </td>
                <td>
                    <div class="meta-label">Status</div>
                    <div class="meta-value">
                        <span class="status-badge status-{{ $sv }}">{{ $statusLabel }}</span>
                    </div>
                </td>
                <td>
                    <div class="meta-label">Currency</div>
                    <div class="meta-value">{{ $order->currency ?? 'SAR' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Items section ── --}}
    @php
        $orderItems = $order->items()->with(['product.brand', 'unit'])->get();
        $itemCount  = $orderItems->count();
    @endphp

    <div class="section-header">
        <span class="section-title">3. Bill of Quantities (BOQ)</span>
        <span class="section-badge">{{ $itemCount }} {{ $itemCount === 1 ? 'ITEM' : 'ITEMS' }}</span>
    </div>

    @if($itemCount > 0)
    <table class="items">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Description</th>
                <th style="width:50px;">Qty</th>
                <th style="width:45px;">Unit</th>
                <th style="width:70px;">Brand</th>
                <th style="width:80px;" class="right">Unit Price</th>
                <th style="width:50px;" class="right">Disc%</th>
                <th style="width:90px;" class="right">Total (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItems as $i => $item)
            @php
                $hasPrice  = is_numeric($item->unit_price) && (float)$item->unit_price > 0;
            @endphp
            <tr>
                <td class="item-no">{{ $i + 1 }}</td>
                <td>
                    <div class="item-desc">{{ $item->description ?: '—' }}</div>
                </td>
                <td class="item-qty">{{ number_format((float)$item->quantity, 0) }}</td>
                <td style="color:#64748b; font-size:10px;">{{ $item->unit?->name ?: '—' }}</td>
                <td style="color:#64748b; font-size:10px;">{{ $item->product?->brand?->name ?: '—' }}</td>
                <td class="right">
                    @if($hasPrice)
                        <span class="price">{{ number_format((float)$item->unit_price, 2) }}</span>
                    @else
                        <span class="not-priced">—</span>
                    @endif
                </td>
                <td class="right">
                    @if($item->discount_pct > 0)
                        <span class="discount">{{ $item->discount_pct }}%</span>
                    @else
                        <span class="not-priced">—</span>
                    @endif
                </td>
                <td class="right">
                    <span class="price">{{ number_format((float)$item->total_price, 2) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="val">{{ number_format((float)$order->total_amount, 2) }} SAR</td>
            </tr>
            <tr>
                <td class="label">Tax / VAT (15%)</td>
                <td class="val">{{ number_format((float)$order->vat_amount, 2) }} SAR</td>
            </tr>
            <tr class="grand">
                <td class="label">Grand Total</td>
                <td class="val">{{ number_format((float)$order->grand_total, 2) }} SAR</td>
            </tr>
        </table>
    </div>

    {{-- ── Terms & Conditions ── --}}
    <div class="terms">
        <div class="terms-title">4. Terms &amp; Conditions</div>
        <div class="terms-line"></div>
        <ol class="terms-list">
            <li>Prices are quoted in Saudi Riyals (SAR) and are exclusive of VAT unless stated otherwise.</li>
            <li>This quotation is valid for 30 days from the date of issue.</li>
            <li>Payment terms: 50% advance, 50% upon delivery, unless otherwise agreed.</li>
            <li>Delivery schedule is subject to confirmation upon receipt of purchase order.</li>
            <li>All materials comply with project specifications and Saudi building codes.</li>
            <li>Warranty as per manufacturer standards unless otherwise specified.</li>
            <li>Qimta reserves the right to revise pricing if project scope changes.</li>
        </ol>
    </div>

    {{-- ── Authorization ── --}}
    <div class="auth">
        <div class="auth-title">5. Authorization</div>
        <div class="auth-line"></div>
        <table class="auth-table">
            <tr>
                <td>
                    <div class="auth-label">Prepared By:</div>
                    <br><br><br>
                    <div class="auth-hint">Name / Signature / Date</div>
                </td>
                <td>
                    <div class="auth-label">Approved By (Client):</div>
                    <br><br><br>
                    <div class="auth-hint">Name / Signature / Date / Stamp</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <span class="footer-note">Qimta Platform — {{ config('app.url') }}</span>
        <span class="footer-note footer-right">This document is system-generated.</span>
    </div>

</body>
</html>
