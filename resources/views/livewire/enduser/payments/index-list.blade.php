<div>
    @php $isAr = app()->getLocale() === 'ar'; @endphp

    <div style="display:flex;flex-direction:column;gap:24px;">
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0;">{{ $isAr ? 'المدفوعات' : 'Payments' }}</h1>
                <p style="font-size:13px;color:#94a3b8;margin:0;">{{ $isAr ? 'سجل جميع عمليات الدفع والتحويلات البنكية' : 'All your payment receipts and bank transfers' }}</p>
            </div>
        </div>

        {{-- Table --}}
        @if($payments->isEmpty())
            <div style="text-align:center;padding:60px 24px;background:#fff;border-radius:16px;border:1px solid #e2e8f0;">
                <div style="width:60px;height:60px;border-radius:16px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <p style="font-size:15px;font-weight:700;color:#334155;margin:0 0 6px;">{{ $isAr ? 'لا توجد مدفوعات بعد' : 'No payments yet' }}</p>
                <p style="font-size:13px;color:#94a3b8;margin:0;">{{ $isAr ? 'ستظهر هنا تفاصيل تحويلاتك البنكية بعد إرسالها' : 'Your bank transfer receipts will appear here' }}</p>
            </div>
        @else
            <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04);">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">{{ $isAr ? 'رقم الطلب' : 'Order No.' }}</th>
                                <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">{{ $isAr ? 'المبلغ' : 'Amount' }}</th>
                                <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">{{ $isAr ? 'رقم المرجع' : 'Reference' }}</th>
                                <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">{{ $isAr ? 'الحالة' : 'Status' }}</th>
                                <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">{{ $isAr ? 'التاريخ' : 'Date' }}</th>
                                <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">{{ $isAr ? 'الإيصال' : 'Receipt' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php
                                    $statusConfig = [
                                        'submitted' => ['label' => $isAr ? 'قيد المراجعة' : 'Under Review', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
                                        'approved'  => ['label' => $isAr ? 'مقبول' : 'Approved',      'bg' => '#f0fdf4', 'color' => '#15803d'],
                                        'rejected'  => ['label' => $isAr ? 'مرفوض' : 'Rejected',      'bg' => '#fff5f5', 'color' => '#b91c1c'],
                                        'pending'   => ['label' => $isAr ? 'معلق' : 'Pending',         'bg' => '#fefce8', 'color' => '#92400e'],
                                        'refunded'  => ['label' => $isAr ? 'مسترجع' : 'Refunded',      'bg' => '#f5f3ff', 'color' => '#6d28d9'],
                                    ];
                                    $sc = $statusConfig[$payment->status?->value ?? 'pending'] ?? $statusConfig['pending'];
                                    $receipt = $payment->uploadedDocuments->first();
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                    <td style="padding:14px 16px;font-weight:700;color:#0f172a;white-space:nowrap;">
                                        {{ $payment->order?->order_no ?? '—' }}
                                    </td>
                                    <td style="padding:14px 16px;font-weight:700;color:#0f172a;white-space:nowrap;">
                                        {{ number_format($payment->amount, 2) }} <span style="color:#94a3b8;font-weight:500;font-size:12px;">{{ $payment->currency }}</span>
                                    </td>
                                    <td style="padding:14px 16px;color:#334155;font-family:monospace;">{{ $payment->reference_number ?? '—' }}</td>
                                    <td style="padding:14px 16px;">
                                        <span style="display:inline-flex;align-items:center;gap:5px;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border-radius:8px;padding:4px 10px;font-size:12px;font-weight:700;white-space:nowrap;">
                                            {{ $sc['label'] }}
                                        </span>
                                        @if($payment->status?->value === 'rejected' && $payment->notes)
                                            <p style="font-size:11px;color:#94a3b8;margin:4px 0 0;">{{ Str::limit($payment->notes, 50) }}</p>
                                        @endif
                                    </td>
                                    <td style="padding:14px 16px;color:#64748b;font-size:13px;white-space:nowrap;">
                                        {{ $payment->created_at?->format('Y/m/d') }}
                                    </td>
                                    <td style="padding:14px 16px;text-align:center;">
                                        @if($receipt)
                                            <a href="{{ Storage::url($receipt->file_path) }}" target="_blank"
                                                style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:8px;padding:5px 10px;font-size:12px;font-weight:700;text-decoration:none;">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                {{ $isAr ? 'عرض' : 'View' }}
                                            </a>
                                        @else
                                            <span style="color:#cbd5e1;font-size:12px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($payments->hasPages())
                    <div style="padding:16px;border-top:1px solid #f1f5f9;">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
