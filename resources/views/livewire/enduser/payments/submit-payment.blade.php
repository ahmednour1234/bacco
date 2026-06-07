<div
    x-data="{ uploading: false, fileName: null }"
    x-on:toast.window="null"
>
    @php
        $isAr = app()->getLocale() === 'ar';
        $isClosed = ($order->status->value ?? $order->status) === \App\Enums\OrderStatusEnum::Closed->value;
    @endphp

    {{-- Already submitted --}}
    @if($isClosed)
        <div style="border-radius:16px;border:1.5px solid #e2e8f0;background:#f8fafc;padding:20px 24px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 4px;">
                    {{ $isAr ? 'الطلب مغلق' : 'Order Closed' }}
                </p>
                <p style="font-size:13px;color:#64748b;margin:0;">
                    {{ $isAr ? 'لا يمكن رفع إيصال دفع جديد لهذا الطلب.' : 'A new payment receipt cannot be uploaded for this order.' }}
                </p>
            </div>
        </div>
    @elseif($submitted && $latestPayment)
        @php
            $status = $latestPayment->status;
            $statusColors = [
                'submitted' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8', 'badge' => '#dbeafe'],
                'approved'  => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d', 'badge' => '#dcfce7'],
                'rejected'  => ['bg' => '#fff5f5', 'border' => '#fecaca', 'text' => '#b91c1c', 'badge' => '#fee2e2'],
                'pending'   => ['bg' => '#fefce8', 'border' => '#fde68a', 'text' => '#92400e', 'badge' => '#fef3c7'],
            ];
            $sc = $statusColors[$status?->value ?? 'pending'];
        @endphp

        <div style="border-radius:16px;border:1.5px solid {{ $sc['border'] }};background:{{ $sc['bg'] }};padding:20px 24px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $sc['badge'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if(($status?->value ?? '') === 'approved')
                    <svg width="20" height="20" fill="none" stroke="{{ $sc['text'] }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @elseif(($status?->value ?? '') === 'rejected')
                    <svg width="20" height="20" fill="none" stroke="{{ $sc['text'] }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                    <svg width="20" height="20" fill="none" stroke="{{ $sc['text'] }}" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                @endif
            </div>
            <div style="flex:1;">
                <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 4px;">
                    @if(($status?->value ?? '') === 'approved')
                        {{ $isAr ? 'تم تأكيد دفعتك ✓' : 'Payment Confirmed ✓' }}
                    @elseif(($status?->value ?? '') === 'rejected')
                        {{ $isAr ? 'تم رفض الإيصال' : 'Receipt Rejected' }}
                    @else
                        {{ $isAr ? 'الإيصال قيد المراجعة' : 'Receipt Under Review' }}
                    @endif
                </p>
                <p style="font-size:13px;color:#64748b;margin:0 0 6px;">
                    @if(($status?->value ?? '') === 'approved')
                        {{ $isAr ? 'تم تأكيد استلام التحويل البنكي، سيُعالَج طلبك الآن.' : 'Your bank transfer has been confirmed. Your order is now being processed.' }}
                    @elseif(($status?->value ?? '') === 'rejected')
                        {{ $isAr ? 'السبب: ' : 'Reason: ' }}{{ $latestPayment->notes ?? '—' }}
                    @else
                        {{ $isAr ? 'سيتم مراجعة إيصالك خلال 24 ساعة وسيصلك إشعار بالنتيجة.' : 'Your receipt will be reviewed within 24 hours. You will be notified.' }}
                    @endif
                </p>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <span style="font-size:12px;color:#94a3b8;">
                        {{ $isAr ? 'رقم المرجع:' : 'Ref:' }} <strong style="color:#334155;">{{ $latestPayment->reference_number }}</strong>
                    </span>
                    <span style="font-size:12px;color:#94a3b8;">
                        {{ $isAr ? 'المبلغ:' : 'Amount:' }} <strong style="color:#334155;">{{ number_format($latestPayment->amount, 2) }} {{ $latestPayment->currency }}</strong>
                    </span>
                </div>
                @if(($status?->value ?? '') === 'rejected')
                    <button wire:click="$set('submitted', false)" style="margin-top:12px;display:inline-flex;align-items:center;gap:6px;background:#0f172a;color:#fff;border:none;border-radius:10px;padding:8px 16px;font-size:13px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;">
                        {{ $isAr ? 'إعادة رفع الإيصال' : 'Re-upload Receipt' }}
                    </button>
                @endif
            </div>
        </div>

    @else
        {{-- Bank Transfer Form --}}
        <div style="display:flex;flex-direction:column;gap:24px;">

            <x-bank-transfer-accounts compact :show-header="false" />

            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:14px;padding:12px 16px;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" fill="none" stroke="#92400e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p style="font-size:12.5px;color:#78350f;margin:0;font-weight:600;">
                    {{ $isAr
                        ? 'بعد إتمام التحويل على الحساب المناسب للعملة، أدخل رقم العملية وارفع صورة الإيصال أدناه.'
                        : 'After completing the transfer to the account that matches the currency, enter the transaction number and upload the receipt below.' }}
                </p>
            </div>

            @if(false)
            {{-- Bank Account Card --}}
            <div style="border-radius:16px;border:1.5px solid #e2e8f0;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#0f2d5e,#1e40af);padding:18px 24px;display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                        <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:14px;font-weight:800;color:#fff;margin:0;">{{ $isAr ? 'بيانات الحساب البنكي للتحويل' : 'Bank Account Details' }}</p>
                        <p style="font-size:12px;color:rgba(255,255,255,.7);margin:0;">{{ $isAr ? 'مصرف الراجحي — الحساب الرسمي لشركة كيمتا التقنية' : 'Al Rajhi Bank — Official Qimta Technology account' }}</p>
                    </div>
                </div>
                <div style="background:#fff;padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @foreach([
                        [$isAr ? 'اسم المستفيد' : 'Beneficiary', $isAr ? 'شركة كيمتا التقنية' : 'Qimta Technology Company'],
                        [$isAr ? 'البنك' : 'Bank', $isAr ? 'مصرف الراجحي' : 'Al Rajhi Bank'],
                        [$isAr ? 'الرقم الوطني الموحد' : 'Unified National No.', '7051075815'],
                        [$isAr ? 'رقم الحساب' : 'Account Number', '44600001006080444992'],
                        [$isAr ? 'رقم الآيبان (IBAN)' : 'IBAN', 'SA0680000446608010444992'],
                        [$isAr ? 'العملة' : 'Currency', $isAr ? 'ريال سعودي (SAR)' : 'Saudi Riyal (SAR)'],
                    ] as [$label, $value])
                    <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                        <p style="font-size:11px;font-weight:600;color:#94a3b8;margin:0 0 3px;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</p>
                        <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0;word-break:break-all;">{{ $value }}</p>
                    </div>
                    @endforeach
                </div>
                <div style="background:#fefce8;border-top:1px solid #fde68a;padding:12px 24px;display:flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="#92400e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p style="font-size:12.5px;color:#78350f;margin:0;font-weight:600;">
                        {{ $isAr
                            ? 'بعد إتمام التحويل، أدخل رقم العملية ارفع صورة الإيصال أدناه.'
                            : 'After completing the transfer, enter the transaction number and upload the receipt below.' }}
                    </p>
                </div>
            </div>

            @endif

            {{-- Order Amount Summary --}}
            <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #bbf7d0;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <p style="font-size:12px;color:#64748b;margin:0 0 2px;font-weight:600;">{{ $isAr ? 'المبلغ الإجمالي للطلب' : 'Total Order Amount' }}</p>
                    <p style="font-size:22px;font-weight:900;color:#15803d;margin:0;">{{ number_format($order->grand_total, 2) }} <span style="font-size:14px;">{{ $order->currency ?? 'SAR' }}</span></p>
                </div>
                <div style="background:#dcfce7;border-radius:10px;padding:10px 14px;text-align:center;">
                    <p style="font-size:11px;font-weight:700;color:#15803d;margin:0;">{{ $order->order_no }}</p>
                    <p style="font-size:10px;color:#86efac;margin:0;">{{ $isAr ? 'رقم الطلب' : 'Order No.' }}</p>
                </div>
            </div>

            {{-- Form --}}
            <form wire:submit.prevent="submit" style="display:flex;flex-direction:column;gap:20px;">

                {{-- Reference Number --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.06em;">
                        {{ $isAr ? 'رقم عملية التحويل / رقم المرجع' : 'Transfer Reference Number' }}
                        <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute;top:50%;transform:translateY(-50%);{{ $isAr ? 'right:14px' : 'left:14px' }};color:#94a3b8;pointer-events:none;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        </span>
                        <input type="text" wire:model.blur="referenceNumber"
                            placeholder="{{ $isAr ? 'مثال: 1234567890' : 'e.g. 1234567890' }}"
                            style="width:100%;height:52px;border-radius:14px;border:1.5px solid {{ $errors->has('referenceNumber') ? '#f87171' : '#e2e8f0' }};background:#f8fafc;font-size:14px;color:#0f172a;font-family:'Cairo',sans-serif;outline:none;padding:0 14px 0 {{ $isAr ? '14px' : '42px' }};{{ $isAr ? 'padding-right:42px;' : '' }}box-sizing:border-box;">
                    </div>
                    @error('referenceNumber') <p style="font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                {{-- Receipt Upload --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.06em;">
                        {{ $isAr ? 'صورة / PDF الإيصال' : 'Receipt Image / PDF' }}
                        <span style="color:#ef4444;">*</span>
                    </label>
                    <label for="receipt-upload"
                        style="display:flex;flex-direction:column;align-items:center;gap:10px;border:2px dashed {{ $errors->has('receiptFile') ? '#f87171' : '#d1d5db' }};border-radius:14px;padding:28px 20px;background:#fafbff;cursor:pointer;text-align:center;transition:border-color .2s;"
                        x-on:dragover.prevent="$el.style.borderColor='#22c55e'"
                        x-on:dragleave.prevent="$el.style.borderColor='#d1d5db'"
                    >
                        <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#dcfce7,#bbf7d0);display:flex;align-items:center;justify-content:center;">
                            <svg width="24" height="24" fill="none" stroke="#16a34a" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <div x-show="!fileName">
                            <p style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 4px;">{{ $isAr ? 'اسحب الإيصال هنا أو اضغط للاختيار' : 'Drop receipt here or click to select' }}</p>
                            <p style="font-size:12px;color:#94a3b8;margin:0;">{{ $isAr ? 'JPG، PNG أو PDF — الحد الأقصى 10 ميجابايت' : 'JPG, PNG or PDF — max 10 MB' }}</p>
                        </div>
                        <p x-show="fileName" x-text="fileName" style="font-size:14px;font-weight:700;color:#16a34a;margin:0;"></p>
                        <input id="receipt-upload" type="file" wire:model="receiptFile" accept=".jpg,.jpeg,.png,.pdf"
                            x-on:change="fileName = $event.target.files[0]?.name ?? null" class="hidden">
                    </label>
                    <div wire:loading wire:target="receiptFile" style="font-size:12px;color:#22c55e;font-weight:600;">
                        {{ $isAr ? 'جاري رفع الملف...' : 'Uploading file...' }}
                    </div>
                    @error('receiptFile') <p style="font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.06em;">
                        {{ $isAr ? 'ملاحظات إضافية' : 'Additional Notes' }}
                        <span style="font-size:11px;font-weight:400;color:#cbd5e1;text-transform:none;letter-spacing:0;">{{ $isAr ? '(اختياري)' : '(optional)' }}</span>
                    </label>
                    <textarea wire:model.blur="notes" rows="2"
                        placeholder="{{ $isAr ? 'أي معلومات إضافية بخصوص التحويل...' : 'Any additional details about the transfer...' }}"
                        style="width:100%;border-radius:14px;border:1.5px solid #e2e8f0;background:#f8fafc;font-size:14px;color:#0f172a;font-family:'Cairo',sans-serif;outline:none;padding:12px 16px;resize:none;box-sizing:border-box;"></textarea>
                    @error('notes') <p style="font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled"
                    style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border:none;border-radius:14px;padding:15px 28px;font-size:16px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;box-shadow:0 4px 16px #22c55e40;transition:opacity .2s,transform .15s;">
                    <svg wire:loading wire:target="submit" style="width:18px;height:18px;" class="animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <svg wire:loading.remove wire:target="submit" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span wire:loading.remove wire:target="submit">{{ $isAr ? 'إرسال إيصال التحويل' : 'Submit Transfer Receipt' }}</span>
                    <span wire:loading wire:target="submit">{{ $isAr ? 'جاري الإرسال...' : 'Submitting...' }}</span>
                </button>

            </form>
        </div>
    @endif
</div>
