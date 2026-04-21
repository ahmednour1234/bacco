<div
    x-data="{
        toast: null,
        confirmOpen: false,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        }
    }"
    x-init="
        if (@js($fetchingPrices)) { $wire.fetchPrices(); }
    "
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
    x-on:refetchPrices.window="$wire.fetchPrices()"
>

    {{-- ───── Toast ──────────────────────────────────────────────────────────── --}}
    <div
        x-show="toast !== null"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type === 'success',
            'bg-red-50 text-red-700 border border-red-200':             toast?.type === 'error',
            'bg-amber-50 text-amber-700 border border-amber-200':       toast?.type === 'warning',
        }"
    >
        <span x-text="toast?.message"></span>
        <button @click="toast = null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ───── Pricing Loading Modal ────────────────────────────────────────────── --}}
    @if($fetchingPrices)
    <style>
        @keyframes ai-cw   { to { transform: rotate(360deg); } }
        @keyframes ai-ccw  { to { transform: rotate(-360deg); } }
        @keyframes ai-blink{ 0%,100%{opacity:.25} 50%{opacity:1} }
        @keyframes ai-fill { from{width:4%} to{width:88%} }
    </style>
    <div
        x-data="{
            msgIndex: 0,
            messages: [
                'جاري البحث في كتالوج المنتجات…',
                'نحلل بنود جدول الكميات…',
                'نستشير الذكاء الاصطناعي للتسعير…',
                'نقارن الأسعار بسوق السعودية…',
                'نحسب أفضل الأسعار لك…',
                'يرجى الانتظار، تقريباً انتهينا…',
            ],
            init() { setInterval(() => { this.msgIndex = (this.msgIndex + 1) % this.messages.length; }, 2600); }
        }"
        style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(2,10,28,0.78);backdrop-filter:blur(8px);"
    >
        <div style="direction:rtl;width:100%;max-width:360px;margin:0 16px;border-radius:20px;background:#fff;box-shadow:0 25px 60px rgba(0,0,0,0.2);overflow:hidden;">

            {{-- Progress bar --}}
            <div style="height:3px;background:#f1f5f9;">
                <div style="height:100%;background:linear-gradient(to right,#10b981,#14b8a6);border-radius:99px;animation:ai-fill 90s linear forwards;"></div>
            </div>

            <div style="padding:28px 28px 24px;text-align:center;">

                {{-- Spinner + icon --}}
                <div style="position:relative;width:80px;height:80px;margin:0 auto 20px;">
                    {{-- Outer ring --}}
                    <span style="position:absolute;inset:0;border-radius:50%;border:3px solid #d1fae5;border-top-color:#10b981;animation:ai-cw 1.5s linear infinite;display:block;"></span>
                    {{-- Inner ring --}}
                    <span style="position:absolute;inset:10px;border-radius:50%;border:3px solid #ccfbf1;border-bottom-color:#0d9488;animation:ai-ccw 1s linear infinite;display:block;"></span>
                    {{-- Center icon: sparkles --}}
                    <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z" fill="#10b981"/>
                            <path d="M19 3l.75 2.25L22 6l-2.25.75L19 9l-.75-2.25L16 6l2.25-.75L19 3z" fill="#34d399"/>
                            <path d="M5 15l.75 2.25L8 18l-2.25.75L5 21l-.75-2.25L2 18l2.25-.75L5 15z" fill="#6ee7b7"/>
                        </svg>
                    </span>
                </div>

                {{-- Label --}}
                <p style="font-size:11px;font-weight:700;letter-spacing:2px;color:#10b981;text-transform:uppercase;margin-bottom:4px;">AI Pricing</p>
                <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 8px;">تحليل الأسعار</h3>

                {{-- Cycling message --}}
                <div style="position:relative;height:22px;overflow:hidden;margin-bottom:16px;">
                    <template x-for="(msg, i) in messages" :key="i">
                        <p
                            style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:13px;color:#64748b;transition:opacity .45s ease, transform .45s ease;margin:0;"
                            :style="i===msgIndex ? 'opacity:1;transform:translateY(0)' : 'opacity:0;transform:translateY(7px)'"
                            x-text="msg"
                        ></p>
                    </template>
                </div>

                {{-- Blinking dots --}}
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:20px;">
                    <span style="display:block;width:8px;height:8px;border-radius:50%;background:#10b981;animation:ai-blink 1.2s ease-in-out infinite;animation-delay:0s;"></span>
                    <span style="display:block;width:8px;height:8px;border-radius:50%;background:#14b8a6;animation:ai-blink 1.2s ease-in-out infinite;animation-delay:0.22s;"></span>
                    <span style="display:block;width:8px;height:8px;border-radius:50%;background:#06b6d4;animation:ai-blink 1.2s ease-in-out infinite;animation-delay:0.44s;"></span>
                </div>

                {{-- Divider --}}
                <div style="height:1px;background:#f1f5f9;margin-bottom:16px;"></div>

                {{-- Steps --}}
                <div style="display:flex;flex-direction:column;gap:10px;">

                    {{-- Step 1 - Done --}}
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                        <span style="font-size:13px;font-weight:500;color:#334155;">البحث في الكتالوج</span>
                        <span style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Step 2 - Active --}}
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                        <span style="font-size:13px;font-weight:700;color:#059669;">تقدير الذكاء الاصطناعي</span>
                        <span style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:#ecfdf5;border:2px solid #10b981;display:flex;align-items:center;justify-content:center;">
                            <svg style="animation:ai-cw 1s linear infinite;" width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="3" stroke-dasharray="38 18" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Step 3 - Pending --}}
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;opacity:.3;">
                        <span style="font-size:13px;color:#94a3b8;">حفظ وتحديث العرض</span>
                        <span style="flex-shrink:0;width:28px;height:28px;border-radius:50%;border:2px solid #cbd5e1;display:flex;align-items:center;justify-content:center;">
                            <span style="display:block;width:8px;height:8px;border-radius:50%;background:#cbd5e1;"></span>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif

    @if($quotation)

    {{-- ───── Header card ─────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $quotation->project_name }}</h1>

            <dl class="mt-3 flex flex-wrap gap-x-8 gap-y-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.status') }}</dt>
                    <dd class="mt-0.5 flex items-center gap-1.5">
                        @php
                            $statusVal = $quotation->status->value ?? '';
                            $dotClass = match($statusVal) {
                                'tender'    => 'bg-blue-500',
                                'submitted' => 'bg-indigo-500',
                                'in_review' => 'bg-amber-500',
                                'quoted'    => 'bg-emerald-500',
                                'accepted'  => 'bg-green-600',
                                'rejected'  => 'bg-red-500',
                                default     => 'bg-slate-400',
                            };
                            $statusLabel = $quotation->status->label();
                        @endphp
                        <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                        <span class="text-sm font-semibold text-slate-700">{{ $statusLabel }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.submitted_date') }}</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $quotation->updated_at?->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.project_type') }}</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700">
                        {{ $quotation->project_status?->label() ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.id') }}</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700 font-mono">{{ $quotation->quotation_no }}</dd>
                </div>
            </dl>
        </div>

        {{-- Action buttons --}}
        <div class="flex shrink-0 items-center gap-3">
            {{-- Export PDF --}}
            <a
                href="{{ route('enduser.quotations.pdf', $quotation->uuid) }}"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('app.export_pdf') }}
            </a>
        </div>
    </div>

    {{-- ───── BOQ Table card ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_full') }}</h2>

            {{-- Edit toolbar — only while editable --}}
            @if(in_array($quotation->status->value, ['tender', 'draft'], true))
            <div class="flex flex-wrap items-center gap-2">
                {{-- Remove All Products --}}
                <button
                    type="button"
                    wire:click="removeAllProducts"
                    wire:loading.attr="disabled"
                    wire:confirm="{{ __('app.remove_all_selections') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 disabled:opacity-60 transition"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('app.remove_all_products') }}
                </button>
            </div>
            @endif
        </div>

        <div class="p-6">
            @if(empty($items))
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                    {{ __('app.no_items_quotation') }}
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-3 py-3 w-10"></th>{{-- checkbox --}}
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.unit') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.brand') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.unit_price_sar') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.total_sar') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $item)
                                @php
                                    $statusVal = $item['status'] ?? 'pending';
                                    $badgeClass = match($statusVal) {
                                        'sourcing' => 'bg-emerald-100 text-emerald-700',
                                        'sourced'  => 'bg-blue-100 text-blue-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default    => 'bg-amber-100 text-amber-700',
                                    };
                                    $badgeLabel = match($statusVal) {
                                        'sourcing' => 'APPROVED',
                                        'sourced'  => 'SOURCED',
                                        'rejected' => 'REJECTED',
                                        default    => 'PENDING',
                                    };
                                    $isSelected   = $item['selected'] ?? false;
                                    $isPickerOpen = $openPickerItemId === $item['id'];
                                    $hasProduct   = ! empty($item['product_id']);
                                    $canEdit      = in_array($quotation->status->value, ['tender', 'draft'], true);
                                @endphp

                                {{-- Main row --}}
                                <tr class="transition-colors hover:bg-slate-50/60 @if($statusVal === 'rejected') opacity-50 @endif @if($isSelected) bg-emerald-50/40 @endif">
                                    {{-- Selection indicator / checkbox --}}
                                    <td class="px-3 py-3 text-center">
                                        @if($canEdit)
                                            <input
                                                type="checkbox"
                                                @checked($isSelected)
                                                wire:click="toggleSelected({{ $item['id'] }})"
                                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 cursor-pointer focus:ring-emerald-500"
                                            />
                                        @elseif($isSelected)
                                            <span title="{{ __('app.selected') }}" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
                                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span title="{{ __('app.not_selected') }}" class="inline-block h-6 w-6 rounded-full border-2 border-slate-200 bg-white"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item['description'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        @if($canEdit)
                                            <input
                                                type="number"
                                                min="0.001"
                                                step="any"
                                                value="{{ $item['quantity'] }}"
                                                @change="$wire.updateQuantity({{ $item['id'] }}, $event.target.value)"
                                                class="w-20 rounded border border-slate-200 bg-white px-2 py-1 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                            />
                                        @else
                                            {{ number_format((float)($item['quantity'] ?? 0)) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item['unit'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item['category'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $item['brand'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if(!empty($item['engineering_required']))
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-emerald-100">
                                                <svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="inline-block h-5 w-5 rounded border border-slate-200 bg-slate-50"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-medium text-slate-800">
                                        @if($fetchingPrices && empty($item['unit_price']))
                                            <svg class="ml-auto h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                        @elseif(is_numeric($item['unit_price'] ?? null))
                                            {{ number_format((float)$item['unit_price'], 2) }}
                                        @else
                                            <span class="text-xs italic text-slate-400">{{ __('app.not_priced') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-medium text-slate-800">
                                        @if($fetchingPrices && empty($item['unit_price']))
                                            <svg class="ml-auto h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                        @elseif(is_numeric($item['unit_price'] ?? null))
                                            {{ number_format((float)$item['unit_price'] * (float)($item['quantity'] ?? 0), 2) }}
                                        @else
                                            <span class="text-xs italic text-slate-400">{{ __('app.not_priced') }}</span>
                                        @endif
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ───── Financial Summary + Submit for Approval ─────────────────────────── --}}
    @php
        $taxRate     = 0.15;
        $selectedItems = collect($items)->filter(fn($i) => ($i['selected'] ?? false));
        $subtotal    = $selectedItems
            ->filter(fn($i) => ($i['status'] ?? '') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
            ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));
        $tax         = $subtotal * $taxRate;
        $total       = $subtotal + $tax;
        $selectedCount = $selectedItems->count();
    @endphp

    <div class="mt-6 flex justify-end">
        <div class="w-full max-w-sm space-y-4">

            {{-- Summary card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">{{ __('app.financial_summary') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $selectedCount }} {{ __('app.items_selected') }}
                    </span>
                </div>

                <div class="space-y-2.5">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>{{ __('app.subtotal') }}</span>
                        <span class="font-mono font-medium">{{ number_format($subtotal, 2) }} {{ __('app.sar') }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>{{ __('app.tax_vat_15') }}</span>
                        <span class="font-mono font-medium">{{ number_format($tax, 2) }} {{ __('app.sar') }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3 flex justify-between">
                        <span class="text-sm font-bold text-slate-800">{{ __('app.total_amount') }}</span>
                        <span class="font-mono text-lg font-bold text-emerald-600">{{ number_format($total, 2) }} {{ __('app.sar') }}</span>
                    </div>
                </div>
            </div>

            {{-- Submit for Approval --}}
            @if(in_array($quotation->status->value, ['tender', 'draft'], true))
                {{-- Cannot submit hint --}}
                @if($selectedCount === 0 || $subtotal <= 0)
                <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-700">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    @if($selectedCount === 0)
                        {{ __('app.select_item_submit') }}
                    @else
                        {{ __('app.no_price_submit') }}
                    @endif
                </div>
                @endif

                {{-- Trigger button --}}
                <button
                    type="button"
                    @click="confirmOpen = true"
                    wire:loading.attr="disabled"
                    wire:target="submitForApproval"
                    @disabled($selectedCount === 0 || $subtotal <= 0)
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <svg wire:loading wire:target="submitForApproval" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg wire:loading.remove wire:target="submitForApproval" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span wire:loading.remove wire:target="submitForApproval">{{ __('app.submit_for_approval') }}</span>
                    <span wire:loading wire:target="submitForApproval">{{ __('app.submitting') }}</span>
                </button>

                {{-- ───── Confirmation Modal ────────────────────────────────────── --}}
                <div
                    x-show="confirmOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(15,23,42,0.4); backdrop-filter: blur(4px);"
                    @keydown.escape.window="confirmOpen = false"
                >
                    <div
                        x-show="confirmOpen"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        @click.stop
                        class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden"
                    >
                        {{-- Header --}}
                        <div class="relative flex flex-col items-center border-b border-slate-100 px-6 pt-7 pb-5 text-center bg-slate-50/70">
                            {{-- Icon --}}
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900">{{ __('app.submit_for_approval') }}</h3>
                            <p class="mt-1.5 text-sm text-slate-500 leading-relaxed max-w-[270px]">
                                {{ __('app.quotation_sent_review') }}
                            </p>
                        </div>

                        {{-- Summary card --}}
                        <div class="mx-5 mt-4 mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white border border-slate-200">
                                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-700">{{ __('app.total') }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $selectedCount }} {{ __('app.items') }}</p>
                                    </div>
                                </div>
                                <span class="font-mono text-xl font-bold text-slate-900">{{ number_format($total, 2) }} <span class="text-sm font-semibold text-slate-600">{{ __('app.sar') }}</span></span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3 px-5 pb-5">
                            <button
                                type="button"
                                @click="confirmOpen = false"
                                class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition"
                            >
                                {{ __('app.cancel') }}
                            </button>
                            <button
                                type="button"
                                @click="confirmOpen = false; $wire.submitForApproval()"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                {{ __('app.yes_submit') }}
                            </button>
                        </div>
                    </div>
                </div>

            @else
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-6 py-3.5 text-center text-sm font-semibold text-slate-500">
                    {{ $quotation->status->label() }}
                </div>
            @endif

        </div>
    </div>

    @endif

</div>
