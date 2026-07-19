{{--
    One batch of BOQ rows for the PDF export.

    Rendered separately from the main template so the controller can write a few
    hundred rows at a time: a multi-thousand-row quotation held as a single HTML
    string is what ran the exporter out of memory.

    Expects: $rows (iterable of QuotationItem), $offset (0-based index of the
    first row in this batch, so numbering continues across batches).
--}}
<table>
    <thead>
        <tr>
            <th style="width:28px;">#</th>
            <th>Item Description</th>
            <th style="width:60px;">Qty</th>
            <th style="width:50px;">Unit</th>
            <th style="width:70px;">Category</th>
            <th style="width:90px;" class="right">Unit Price (SAR)</th>
            <th style="width:100px;" class="right">Total (SAR)</th>
        </tr>
    </thead>
    <tbody>
@foreach($rows as $i => $item)
    @php
        $hasPrice  = is_numeric($item->unit_price) && (float) $item->unit_price > 0;
        $lineTotal = $hasPrice ? (float) $item->unit_price * (float) $item->quantity : null;
    @endphp
    <tr>
        <td class="item-no">{{ $offset + $i + 1 }}</td>
        <td>
            <div class="item-desc" dir="rtl">{{ $item->description ?: '—' }}</div>
            @if($item->brand)
                <div class="item-sub">{{ $item->brand }}</div>
            @endif
        </td>
        <td class="item-qty">{{ number_format((float) $item->quantity, 0) }}</td>
        <td style="color:#64748b;">{{ $item->unit?->name ?: '—' }}</td>
        <td style="color:#64748b; font-size:10px;">{{ $item->category ?: '—' }}</td>
        <td class="right">
            @if($hasPrice)
                <span class="price">{{ number_format((float) $item->unit_price, 2) }}</span>
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
