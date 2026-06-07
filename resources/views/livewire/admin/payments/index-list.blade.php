<div x-data="{ rejectOpen: false }">
    @php $isAr = app()->getLocale() === 'ar'; @endphp

    {{-- Toast listener --}}
    <div x-on:toast.window="null"></div>

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0;">{{ $isAr ? 'إدارة المدفوعات' : 'Payments Management' }}</h1>
            @if($pendingCount > 0)
                <span style="background:#fef3c7;color:#92400e;border-radius:8px;padding:4px 10px;font-size:13px;font-weight:700;">{{ $pendingCount }} {{ $isAr ? 'بانتظار المراجعة' : 'pending review' }}</span>
            @endif
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="{{ $isAr ? 'بحث برقم الطلب أو العميل...' : 'Search order or client...' }}"
                style="height:40px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;padding:0 12px;font-size:13px;font-family:'Cairo',sans-serif;outline:none;min-width:220px;">
            <select wire:model.live="statusFilter"
                style="height:40px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;padding:0 12px;font-size:13px;font-family:'Cairo',sans-serif;outline:none;">
                <option value="">{{ $isAr ? 'كل الحالات' : 'All statuses' }}</option>
                <option value="submitted">{{ $isAr ? 'قيد المراجعة' : 'Under Review' }}</option>
                <option value="approved">{{ $isAr ? 'مقبول' : 'Approved' }}</option>
                <option value="rejected">{{ $isAr ? 'مرفوض' : 'Rejected' }}</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04);">
        @if($payments->isEmpty())
            <div style="text-align:center;padding:60px 24px;">
                <p style="font-size:15px;font-weight:700;color:#334155;margin:0 0 6px;">{{ $isAr ? 'لا توجد مدفوعات' : 'No payments found' }}</p>
                <p style="font-size:13px;color:#94a3b8;margin:0;">{{ $isAr ? 'ستظهر هنا عند وصول إيصالات التحويل' : 'Payment receipts will appear here' }}</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            @foreach([
                                $isAr ? 'العميل' : 'Client',
                                $isAr ? 'رقم الطلب' : 'Order No.',
                                $isAr ? 'المبلغ' : 'Amount',
                                $isAr ? 'رقم المرجع' : 'Reference',
                                $isAr ? 'الحالة' : 'Status',
                                $isAr ? 'التاريخ' : 'Date',
                                $isAr ? 'الإيصال' : 'Receipt',
                                $isAr ? 'إجراء' : 'Action',
                            ] as $col)
                            <th style="padding:12px 16px;text-align:{{ $isAr ? 'right' : 'left' }};font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $statusConfig = [
                                    'submitted' => ['label' => $isAr ? 'قيد المراجعة' : 'Under Review', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
                                    'approved'  => ['label' => $isAr ? 'مقبول' : 'Approved',             'bg' => '#f0fdf4', 'color' => '#15803d'],
                                    'rejected'  => ['label' => $isAr ? 'مرفوض' : 'Rejected',             'bg' => '#fff5f5', 'color' => '#b91c1c'],
                                    'pending'   => ['label' => $isAr ? 'معلق' : 'Pending',                'bg' => '#fefce8', 'color' => '#92400e'],
                                    'refunded'  => ['label' => $isAr ? 'مسترجع' : 'Refunded',            'bg' => '#f5f3ff', 'color' => '#6d28d9'],
                                ];
                                $sc = $statusConfig[$payment->status?->value ?? 'pending'] ?? $statusConfig['pending'];
                                $receipt = $payment->uploadedDocuments->first();
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                <td style="padding:14px 16px;">
                                    <p style="font-weight:700;color:#0f172a;margin:0;">{{ $payment->client?->name ?? '—' }}</p>
                                    <p style="font-size:12px;color:#94a3b8;margin:0;">{{ $payment->client?->email }}</p>
                                </td>
                                <td style="padding:14px 16px;font-weight:700;color:#0f172a;white-space:nowrap;">{{ $payment->order?->order_no ?? '—' }}</td>
                                <td style="padding:14px 16px;font-weight:700;white-space:nowrap;">
                                    {{ number_format($payment->amount, 2) }} <span style="color:#94a3b8;font-weight:500;font-size:12px;">{{ $payment->currency }}</span>
                                </td>
                                <td style="padding:14px 16px;font-family:monospace;color:#334155;">{{ $payment->reference_number ?? '—' }}</td>
                                <td style="padding:14px 16px;">
                                    <span style="display:inline-flex;align-items:center;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border-radius:8px;padding:4px 10px;font-size:12px;font-weight:700;white-space:nowrap;">{{ $sc['label'] }}</span>
                                </td>
                                <td style="padding:14px 16px;color:#64748b;font-size:13px;white-space:nowrap;">{{ $payment->created_at?->format('Y/m/d') }}</td>
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
                                <td style="padding:14px 16px;text-align:center;">
                                    @if(($payment->status?->value ?? '') === 'submitted')
                                        <button wire:click="openReview({{ $payment->id }})"
                                            style="display:inline-flex;align-items:center;gap:5px;background:#0f172a;color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;">
                                            {{ $isAr ? 'مراجعة' : 'Review' }}
                                        </button>
                                    @elseif(($payment->status?->value ?? '') === 'approved')
                                        <span style="font-size:12px;color:#15803d;">{{ $payment->reviewer?->name ?? '—' }}</span>
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
                <div style="padding:16px;border-top:1px solid #f1f5f9;">{{ $payments->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Review Modal --}}
    @if($showModal && $reviewing)
    <div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);"
        wire:click.self="$set('showModal', false)">
        <div style="background:#fff;border-radius:20px;width:560px;max-width:calc(100vw - 40px);max-height:90vh;overflow-y:auto;box-shadow:0 24px 80px rgba(0,0,0,.25);" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
            {{-- Modal Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #f1f5f9;">
                <div>
                    <p style="font-size:16px;font-weight:800;color:#0f172a;margin:0;">{{ $isAr ? 'مراجعة إيصال الدفع' : 'Review Payment Receipt' }}</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0;">{{ $reviewing->order?->order_no }}</p>
                </div>
                <button wire:click="$set('showModal', false)" style="width:32px;height:32px;border-radius:8px;border:none;background:#f1f5f9;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">

                {{-- Details grid --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    @foreach([
                        [$isAr ? 'العميل' : 'Client', $reviewing->client?->name],
                        [$isAr ? 'المبلغ' : 'Amount', number_format($reviewing->amount, 2) . ' ' . $reviewing->currency],
                        [$isAr ? 'رقم المرجع' : 'Reference', $reviewing->reference_number],
                        [$isAr ? 'تاريخ الإرسال' : 'Submitted', $reviewing->created_at?->format('Y/m/d H:i')],
                    ] as [$label, $value])
                    <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                        <p style="font-size:11px;font-weight:600;color:#94a3b8;margin:0 0 3px;text-transform:uppercase;">{{ $label }}</p>
                        <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">{{ $value ?? '—' }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Receipt preview --}}
                @php $receipt = $reviewing->uploadedDocuments->first(); @endphp
                @if($receipt)
                    <div style="border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
                        <div style="background:#f8fafc;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-size:13px;font-weight:700;color:#334155;">{{ $isAr ? 'الإيصال المرفوع' : 'Uploaded Receipt' }}</span>
                            <a href="{{ Storage::url($receipt->file_path) }}" target="_blank"
                                style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;">
                                {{ $isAr ? 'فتح في نافذة جديدة ↗' : 'Open in new tab ↗' }}
                            </a>
                        </div>
                        @if(str_contains($receipt->file_type ?? '', 'image'))
                            <img src="{{ Storage::url($receipt->file_path) }}" alt="receipt" style="width:100%;max-height:300px;object-fit:contain;background:#f1f5f9;">
                        @else
                            <div style="padding:24px;text-align:center;background:#f8fafc;">
                                <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <p style="font-size:13px;color:#64748b;margin:0;">{{ $receipt->file_name }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Client notes --}}
                @if($reviewing->notes)
                    <div style="background:#fefce8;border-radius:10px;padding:12px 14px;border:1px solid #fde68a;">
                        <p style="font-size:11px;font-weight:700;color:#92400e;margin:0 0 4px;text-transform:uppercase;">{{ $isAr ? 'ملاحظات العميل' : 'Client Notes' }}</p>
                        <p style="font-size:13px;color:#78350f;margin:0;">{{ $reviewing->notes }}</p>
                    </div>
                @endif

                {{-- Reject reason input --}}
                <div x-show="rejectOpen" x-cloak style="display:flex;flex-direction:column;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#334155;">{{ $isAr ? 'سبب الرفض' : 'Rejection Reason' }} <span style="color:#ef4444;">*</span></label>
                    <textarea wire:model="rejectReason" rows="3" placeholder="{{ $isAr ? 'اكتب سبب الرفض...' : 'Explain why the receipt is rejected...' }}"
                        style="border-radius:10px;border:1.5px solid #e2e8f0;padding:10px 12px;font-size:14px;font-family:'Cairo',sans-serif;outline:none;resize:none;"></textarea>
                    @error('rejectReason') <p style="font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                {{-- Action buttons --}}
                <div style="display:flex;gap:10px;padding-top:4px;">
                    <button wire:click="approve"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border:none;border-radius:12px;padding:13px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $isAr ? 'قبول الدفعة' : 'Approve Payment' }}
                    </button>
                    <button x-on:click="rejectOpen = !rejectOpen"
                        x-show="!rejectOpen"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#b91c1c;border:1.5px solid #fecaca;border-radius:12px;padding:13px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ $isAr ? 'رفض الإيصال' : 'Reject Receipt' }}
                    </button>
                    <button wire:click="reject"
                        x-show="rejectOpen"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;background:#b91c1c;color:#fff;border:none;border-radius:12px;padding:13px;font-size:14px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;">
                        {{ $isAr ? 'تأكيد الرفض' : 'Confirm Reject' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
