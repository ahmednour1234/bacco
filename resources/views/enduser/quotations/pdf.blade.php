<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation #{{ $quotation->quotation_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: dejavusans, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        /* ── Header ── */
        .header { padding: 28px 36px 18px; border-bottom: 2px solid #059669; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { border: none; padding: 0; vertical-align: top; }
        .logo-img { height: 40px; }
        .company-name { font-size: 22px; font-weight: 700; color: #059669; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .doc-title    { text-align: right; }
        .doc-title h1 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .doc-title p  { font-size: 10px; color: #64748b; margin-top: 3px; }

        /* ── Meta strip ── */
        .meta { border-bottom: 1px solid #e2e8f0; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 12px 20px; border-right: 1px solid #f1f5f9; vertical-align: top; }
        .meta td:last-child { border-right: none; }
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
        <table>
            <tr>
                <td>
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="Qimta">
                    @else
                        <div class="company-name">Qimta</div>
                    @endif
                    <div class="company-sub">Construction &amp; MEP Supply Platform</div>
                </td>
                <td style="text-align:right;">
                    <div class="doc-title">
                        <h1>Quotation #{{ $quotation->quotation_no }}</h1>
                        <p>Generated: {{ now()->format('M d, Y  H:i') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Meta strip ── --}}
    @php
        $sv = $quotation->status->value ?? 'draft';
        $statusLabel = match($sv) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'tender' => 'Tender',
            'in_review' => 'In Review',
            'quoted' => 'Quoted',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => ucfirst($sv),
        };
    @endphp
    <div class="meta">
        <table>
            <tr>
                <td>
                    <div class="meta-label">Project</div>
                    <div class="meta-value">{{ $quotation->project_name ?: '—' }}</div>
                </td>
                <td>
                    <div class="meta-label">Client</div>
                    <div class="meta-value">{{ $quotation->client?->name ?? '—' }}</div>
                </td>
                <td>
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ $quotation->created_at->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Items section ──
         Counts and totals are aggregated in SQL rather than by loading every
         row: a real BOQ runs to thousands of lines, and hydrating them all here
         just to sum them is what exhausted memory on a 5,000-row quotation.
         The rows themselves are written to the PDF in batches by the
         controller, so they are never all in memory at once. --}}
    @php
        $itemCount = $quotation->items()->count();

        $subtotal = (float) $quotation->items()
            ->where('is_selected', true)
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) AS total')
            ->value('total');

        $tax   = $subtotal * 0.15;
        $grand = $subtotal + $tax;
    @endphp

    <div class="section-header">
        <span class="section-title">Bill of Quantities (BOQ)</span>
        <span class="section-badge">{{ $itemCount }} {{ $itemCount === 1 ? 'ITEM' : 'ITEMS' }}</span>
    </div>

    {{-- The controller replaces this marker with one complete table per batch
         of rows. Each batch is self-contained markup — mPDF buffers a table
         until its closing tag, so a table split across WriteHTML() calls would
         not render. --}}
    @if($itemCount > 0)
    <!--QIMTA_ROWS-->
    @endif

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal (selected items)</td>
                <td class="val">{{ number_format($subtotal, 2) }} SAR</td>
            </tr>
            <tr>
                <td class="label">Tax / VAT (15%)</td>
                <td class="val">{{ number_format($tax, 2) }} SAR</td>
            </tr>
            <tr class="grand">
                <td class="label">Grand Total</td>
                <td class="val">{{ number_format($grand, 2) }} SAR</td>
            </tr>
        </table>
    </div>

    {{-- ── Terms & Conditions ── --}}
    <div class="terms">
        <div class="terms-title">4. Terms &amp; Conditions</div>
        <div class="terms-line"></div>
        <ol class="terms-list">
            <li>Prices are quoted in Saudi Riyals (SAR) and are inclusive of 15% VAT unless stated otherwise.</li>
            <li>This quotation is valid for <strong>10 days</strong> from the date of issue.</li>
            <li>This quotation is official and binding. Upon receipt of 100% advance payment, the order shall be considered confirmed and will be processed for execution.</li>
            <li>Payment terms: 100% advance payment upon order confirmation.</li>
            <li>Delivery schedule: Maximum 60 days from receipt of advance payment for all supply items. Engineering-related items shall be delivered within a maximum of 90 days from receipt of advance payment.</li>
            <li>This quotation is strictly for supply only. It does not include installation, testing, commissioning, site works, supervision, or any engineering execution at site, even if any wording elsewhere in the quotation may imply otherwise, unless expressly agreed in writing by Qimta.</li>
            <li>All materials shall comply with project specifications and applicable Saudi Building Code requirements.</li>
            <li>Warranty shall be as per manufacturer standards unless otherwise specified.</li>
            <li>Qimta reserves the right to revise pricing in case of any change in project scope, specifications, quantities, delivery location, or market conditions.</li>
            <li>Delivery period is subject to the availability of materials, manufacturer lead time, and timely approval of all required technical submittals, drawings, or documents.</li>
            <li>The quoted prices are based on the quantities and specifications provided at the time of quotation. Any variation in quantity or specification may require price adjustment.</li>
            <li>Order cancellation after payment or procurement confirmation shall be subject to supplier/manufacturer cancellation policy and any incurred costs.</li>
            <li>Qimta shall not be liable for delays caused by force majeure events, customs clearance delays, supplier/manufacturer delays, government procedures, or circumstances beyond its control.</li>
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
        <span class="footer-note footer-right">This document is system-generated and does not require a signature.</span>
    </div>

</body>
</html>
