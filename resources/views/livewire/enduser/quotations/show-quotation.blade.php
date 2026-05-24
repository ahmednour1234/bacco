<div
    wire:init="fetchPricesOnInit"
    x-data="{
        step: 1,
        toast: null,
        addressError: false,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        },
        async tryProceedToReview() {
            const type = await $wire.get('addressType');
            let valid = false;
            if (type === 'national') {
                const b = await $wire.get('nationalBuildingNo');
                const s = await $wire.get('nationalStreet');
                const d = await $wire.get('nationalDistrict');
                const c = await $wire.get('nationalCity');
                valid = b?.trim() && s?.trim() && d?.trim() && c?.trim();
            } else {
                const s = await $wire.get('deliveryStreet');
                const d = await $wire.get('deliveryDistrict');
                const c = await $wire.get('deliveryCity');
                valid = s?.trim() && d?.trim() && c?.trim();
            }
            if (!valid) { this.addressError = true; return; }
            this.addressError = false;
            this.step = 4;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
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
        class="fixed bottom-6 end-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type === 'success',
            'bg-red-50 text-red-700 border border-red-200':             toast?.type === 'error',
            'bg-amber-50 text-amber-700 border border-amber-200':       toast?.type === 'warning',
        }"
    >
        <span x-text="toast?.message"></span>
        <button @click="toast = null" class="ms-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Background pricing poller: fires every 4s while job is running --}}
    @if($pricingQueued)
        <div wire:poll.4s="checkPricingStatus" class="sr-only" aria-hidden="true"></div>
    @endif

    @if($quotation)

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP PROGRESS BAR  (Amazon-style)
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-8">
        {{-- Project header row --}}
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $quotation->project_name }}</h1>
                <p class="mt-0.5 text-xs text-slate-500 font-mono">{{ $quotation->quotation_no }}</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Validity badge --}}
                @if(in_array($quotation->status->value ?? '', ['tender', 'draft'], true))
                @php
                    $validityRef     = $quotation->prices_fetched_at ?? $quotation->created_at;
                    $expiresAt       = $validityRef->copy()->addDays(\App\Models\QuotationRequest::EXPIRY_DAYS);
                    $daysLeft        = (int) now()->diffInDays($expiresAt, false);
                    $isExpiredBadge  = $daysLeft < 0;
                    $daysOverdue     = abs($daysLeft);
                @endphp
                @if($isExpiredBadge)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ $daysOverdue === 1 ? __('app.expired_since_one_day') : __('app.expired_since_days', ['days' => $daysOverdue]) }}
                    </span>
                @elseif($daysLeft === 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('app.expires_today') }}</span>
                @elseif($daysLeft <= 3)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                        {{ $daysLeft === 1 ? __('app.expires_in_one_day') : __('app.expires_in_days', ['days' => $daysLeft]) }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $daysLeft === 1 ? __('app.valid_for_one_day') : __('app.valid_for_days', ['days' => $daysLeft]) }}
                    </span>
                @endif
                @endif
                {{-- PDF --}}
                <a href="{{ route('enduser.quotations.pdf', $quotation->uuid) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('app.export_pdf') }}
                </a>
            </div>
        </div>

        {{-- Steps breadcrumb --}}
        @if(in_array($quotation->status->value ?? '', ['tender', 'draft'], true))
        <div class="relative flex items-center justify-between py-2">
            {{-- Connecting track --}}
            <div class="absolute top-1/2 start-0 end-0 h-0.5 bg-slate-200 mx-12 -translate-y-1/2 hidden sm:block"></div>
            {{-- Animated fill --}}
            <div class="absolute top-1/2 start-12 h-0.5 bg-emerald-400 transition-all duration-500 -translate-y-1/2 hidden sm:block"
                 :style="'width: calc(' + ((step-1)/3) + ' * (100% - 6rem))'"></div>

            <template x-for="s in [1,2,3,4]" :key="s">
                <div class="relative z-10 flex flex-col items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-xs font-bold transition-all duration-300 bg-white"
                         :class="{
                             'border-emerald-500 bg-emerald-500 text-white shadow-md shadow-emerald-200': step > s,
                             'border-emerald-500 text-emerald-600 ring-4 ring-emerald-50': step === s,
                             'border-slate-200 text-slate-400': step < s
                         }">
                        <template x-if="step > s">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="step <= s"><span x-text="s"></span></template>
                    </div>
                    <span class="text-[11px] font-semibold transition-colors duration-300 text-center"
                          :class="step >= s ? 'text-emerald-600' : 'text-slate-400'"
                          x-text="s === 1 ? '{{ __('app.checkout_step_items') }}' : (s === 2 ? '{{ __('app.checkout_step_pricing') }}' : (s === 3 ? '{{ __('app.checkout_step_address') }}' : '{{ __('app.checkout_step_confirm') }}'  ))"
                    ></span>
                </div>
            </template>
        </div>
        @else
        {{-- Non-editable: show simple status bar --}}
        <div class="rounded-2xl border border-slate-100 bg-white px-6 py-4 shadow-sm">
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
            @endphp
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                <span class="text-sm font-semibold text-slate-700">{{ $quotation->status->label() }}</span>
                <span class="text-xs text-slate-400 ms-auto font-mono">{{ $quotation->quotation_no }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 1 — Review Items
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 1"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-3"
         x-transition:enter-end="opacity-100 translate-x-0">

        {{-- Pricing banner --}}
        @if($pricingQueued)
        <div x-data="{ visible: true }" x-show="visible"
             class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5">
            <svg class="h-5 w-5 animate-spin text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-emerald-800">جاري تسعير عناصر عرض السعر في الخلفية</p>
                <p class="mt-0.5 text-xs text-emerald-600">يمكنك التصفح بحرية — ستصلك إشعار فور اكتمال التسعير.</p>
            </div>
            <button @click="visible = false; $wire.dismissPricingBanner()" class="text-emerald-500 hover:text-emerald-700 rounded-lg p-1 hover:bg-emerald-100 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        {{-- Expiry banner --}}
        @if($isExpired)
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5">
            <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-800">{{ __('app.expired_banner_title') }}</p>
                <p class="mt-0.5 text-xs text-red-600">{{ __('app.expired_banner_body') }}</p>
            </div>
            @if(in_array($quotation->status->value ?? '', ['tender', 'draft'], true))
            <button wire:click="refetchPrices" wire:loading.attr="disabled" wire:target="refetchPrices"
                class="shrink-0 rounded-xl border border-red-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 transition">
                {{ __('app.refresh_prices') }}
            </button>
            @endif
        </div>
        @endif

        {{-- BOQ Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_full') }}</h2>
                @php
                    $statusVal2 = $quotation->status->value ?? '';
                    $dotClass2 = match($statusVal2) {
                        'tender'    => 'bg-blue-500',
                        'submitted' => 'bg-indigo-500',
                        'in_review' => 'bg-amber-500',
                        'quoted'    => 'bg-emerald-500',
                        'accepted'  => 'bg-green-600',
                        'rejected'  => 'bg-red-500',
                        default     => 'bg-slate-400',
                    };
                @endphp
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full {{ $dotClass2 }}"></span>
                    <span class="text-xs font-semibold text-slate-600">{{ $quotation->status->label() }}</span>
                </div>
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
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.unit') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.brand') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                    <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.unit_price_sar') }}</th>
                                    <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.total_sar') }}</th>
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
                                            'sourcing' => __('app.item_status_sourcing'),
                                            'sourced'  => __('app.item_status_sourced'),
                                            'rejected' => __('app.item_status_rejected'),
                                            default    => __('app.item_status_pending'),
                                        };
                                    @endphp
                                    <tr class="transition-colors hover:bg-slate-50/60 @if($statusVal === 'rejected') opacity-50 @endif">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $item['description'] ?: '—' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format((float)($item['quantity'] ?? 0)) }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $item['unit'] ?: '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $item['category'] ?: '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $item['brand'] ?: '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if(!empty($item['engineering_required']))
                                                <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-emerald-100">
                                                    <svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @else
                                                <span class="inline-block h-5 w-5 rounded border border-slate-200 bg-slate-50"></span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-end font-mono font-medium text-slate-800">
                                            @if($pricingQueued && empty($item['unit_price']))
                                                <svg class="ms-auto h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            @elseif(is_numeric($item['unit_price'] ?? null))
                                                {{ number_format((float)$item['unit_price'], 2) }}
                                            @else
                                                <span class="text-xs italic text-slate-400">{{ __('app.not_priced') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end font-mono font-medium text-slate-800">
                                            @if($pricingQueued && empty($item['unit_price']))
                                                <svg class="ms-auto h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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

        {{-- Financial Summary + CTA --}}
        @php
            $taxRate  = 0.15;
            $allItems = collect($items);
            $subtotal = $allItems->filter(fn($i) => !empty($i['selected']) && ($i['status'] ?? '') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
                                 ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));
            $tax      = $subtotal * $taxRate;
            $total    = $subtotal + $tax;
            $itemCount = $allItems->filter(fn($i) => !empty($i['selected']) && ($i['status'] ?? '') !== 'rejected')->count();
        @endphp

        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-4">

                {{-- Summary card --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700">{{ __('app.financial_summary') }}</h3>
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                            {{ $itemCount }} {{ __('app.items_selected') }}
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

                @if(in_array($quotation->status->value, ['tender', 'draft'], true))
                    @if($isExpired)
                    <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        {{ __('app.expired_block_msg') }}
                    </div>
                    <button wire:click="refetchPrices" wire:loading.attr="disabled" wire:target="refetchPrices" type="button"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                        <span wire:loading.remove wire:target="refetchPrices">{{ __('app.refresh_to_renew') }}</span>
                        <span wire:loading wire:target="refetchPrices">{{ __('app.refreshing_prices') }}</span>
                    </button>
                    @else
                    @if($subtotal <= 0)
                    <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        {{ __('app.no_price_submit') }}
                    </div>
                    @endif

                    {{-- Continue to Step 2 --}}
                    <button
                        type="button"
                        @click="step = 2; window.scrollTo({ top: 0, behavior: 'smooth' })"
                        @disabled($subtotal <= 0)
                        class="flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-white shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
                        style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);"
                    >
                        <span>{{ __('app.checkout_step_pricing') }}</span>
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    @endif
                @else
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-6 py-3.5 text-center text-sm font-semibold text-slate-500">
                    {{ $quotation->status->label() }}
                </div>
                @endif

            </div>
        </div>

    </div>{{-- end step 1 --}}

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 2 — إنشاء عرض السعر (Create Quotation)
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 2"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak>

        <div class="mx-auto max-w-lg space-y-5">

            {{-- Heading --}}
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-md shadow-emerald-100"
                     style="background: linear-gradient(135deg, #10b981 0%, #059669 100%)">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.checkout_step_pricing') }}</h2>
                    <p class="text-sm text-slate-500">
                        {{ app()->getLocale() === 'ar' ? 'مراجعة الأسعار والإجمالي قبل المتابعة' : 'Review prices and totals before proceeding' }}
                    </p>
                </div>
            </div>

            {{-- Pricing status banner --}}
            @if($pricingQueued)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5">
                <svg class="h-5 w-5 animate-spin text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">
                        {{ app()->getLocale() === 'ar' ? 'جاري إنشاء عرض السعر…' : 'Generating quotation prices…' }}
                    </p>
                    <p class="mt-0.5 text-xs text-amber-600">
                        {{ app()->getLocale() === 'ar' ? 'يتم تسعير الأصناف — ستصلك إشعار فور الاكتمال.' : 'Items are being priced — you will be notified when done.' }}
                    </p>
                </div>
            </div>
            @else
            <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-semibold text-emerald-700">
                    {{ app()->getLocale() === 'ar' ? 'تم إنشاء عرض السعر بنجاح' : 'Quotation created successfully' }}
                </p>
            </div>
            @endif

            {{-- Financial Summary card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">{{ __('app.financial_summary') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $itemCount }} {{ __('app.items_selected') }}
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
                        <span class="text-sm font-bold text-slate-800">
                            {{ app()->getLocale() === 'ar' ? 'إجمالي عرض السعر' : 'Quotation Total' }}
                        </span>
                        <span class="font-mono text-lg font-bold text-emerald-600">{{ number_format($total, 2) }} {{ __('app.sar') }}</span>
                    </div>
                </div>
            </div>

            {{-- PDF Export button --}}
            <a href="{{ route('enduser.quotations.pdf', $quotation->uuid) }}" target="_blank"
               class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-3.5 text-sm font-semibold text-slate-600 hover:border-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('app.export_pdf') }}
            </a>

            @if($subtotal <= 0)
            <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-700">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ __('app.no_price_submit') }}
            </div>
            @endif

            {{-- Navigation --}}
            <div class="flex gap-3 pb-4">
                <button type="button"
                    @click="step = 1; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-800">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.back') }}
                </button>
                <button type="button"
                    @click="step = 3; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    @disabled($subtotal <= 0)
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-white shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <span>{{ __('app.checkout_step_address') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>{{-- end step 2 --}}

    {{-- ═══════════════════════════════════════════════════════════════════════
         STEP 3 — Shipping Address
    ════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 3"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak>

        <div class="mx-auto max-w-2xl">

            {{-- ── Section heading ──────────────────────────────────────────── --}}
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-md shadow-emerald-100"
                     style="background: linear-gradient(135deg, #10b981 0%, #059669 100%)">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('app.checkout_step_address') }}</h2>
                    <p class="text-sm text-slate-500">{{ __('app.choose_address_type_hint') }}</p>
                </div>
            </div>

            {{-- ── Address type selector (radio list style) ─────────────────── --}}
            <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- National option --}}
                <button type="button" wire:click="$set('addressType', 'national')" @click="addressError = false"
                    class="flex w-full items-center gap-4 border-b border-slate-100 px-5 py-4 text-start transition-colors
                           {{ $addressType === 'national' ? 'bg-emerald-50' : 'hover:bg-slate-50' }}">
                    {{-- Radio circle --}}
                    <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors
                        {{ $addressType === 'national' ? 'border-emerald-500' : 'border-slate-300' }}">
                        @if($addressType === 'national')
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                        @endif
                    </div>
                    {{-- Icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                        {{ $addressType === 'national' ? 'bg-emerald-100' : 'bg-slate-100' }}">
                        <svg class="h-5 w-5 {{ $addressType === 'national' ? 'text-emerald-600' : 'text-slate-400' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    {{-- Text --}}
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold {{ $addressType === 'national' ? 'text-emerald-700' : 'text-slate-800' }}">
                                {{ __('app.address_type_national') }}
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                {{ __('app.recommended') }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500 leading-relaxed">{{ __('app.address_type_national_desc') }}</p>
                    </div>
                </button>

                {{-- Detailed option --}}
                <button type="button" wire:click="$set('addressType', 'detailed')" @click="addressError = false"
                    class="flex w-full items-center gap-4 px-5 py-4 text-start transition-colors
                           {{ $addressType === 'detailed' ? 'bg-emerald-50' : 'hover:bg-slate-50' }}">
                    {{-- Radio circle --}}
                    <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors
                        {{ $addressType === 'detailed' ? 'border-emerald-500' : 'border-slate-300' }}">
                        @if($addressType === 'detailed')
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                        @endif
                    </div>
                    {{-- Icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                        {{ $addressType === 'detailed' ? 'bg-emerald-100' : 'bg-slate-100' }}">
                        <svg class="h-5 w-5 {{ $addressType === 'detailed' ? 'text-emerald-600' : 'text-slate-400' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    {{-- Text --}}
                    <div class="flex-1">
                        <span class="text-sm font-semibold {{ $addressType === 'detailed' ? 'text-emerald-700' : 'text-slate-800' }}">
                            {{ __('app.address_type_detailed') }}
                        </span>
                        <p class="mt-0.5 text-xs text-slate-500 leading-relaxed">{{ __('app.address_type_detailed_desc') }}</p>
                    </div>
                </button>
            </div>

            {{-- ── Address form card ────────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Card header --}}
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-3.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">{{ __('app.fill_address_fields') }}</span>
                    <span class="ms-auto rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                        {{ $addressType === 'national' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        @if($addressType === 'national'){{ __('app.address_type_national') }}@else{{ __('app.address_type_detailed') }}@endif
                    </span>
                </div>

                <div class="p-5">
                @if($addressType === 'national')
                {{-- ── National address fields ──────────────────────────────── --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_building_no') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="nationalBuildingNo" @input="addressError = false"
                                class="w-full rounded-xl border bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:bg-white
                                       {{ $addressType === 'national' && empty($nationalBuildingNo) && false ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100' : 'border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100' }}">
                        </div>
                        <div class="col-span-3">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_street') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="nationalStreet" @input="addressError = false"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_district') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="nationalDistrict" @input="addressError = false"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_city') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="nationalCity" @input="addressError = false"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_postal_code') }}</label>
                            <input type="text" wire:model="nationalPostalCode"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_additional_no') }}</label>
                            <input type="text" wire:model="nationalAdditionalNo"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-xl border border-blue-100 bg-blue-50/80 px-4 py-3">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs leading-relaxed text-blue-700">{{ __('app.national_address_info') }}</span>
                    </div>
                </div>
                @else
                {{-- ── Detailed address fields ───────────────────────────────── --}}
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_street') }} <span class="text-red-400">*</span></label>
                        <input type="text" wire:model.live="deliveryStreet" @input="addressError = false" placeholder="{{ __('app.address_street_placeholder') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_district') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="deliveryDistrict" @input="addressError = false" placeholder="{{ __('app.address_district_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_city') }} <span class="text-red-400">*</span></label>
                            <input type="text" wire:model.live="deliveryCity" @input="addressError = false" placeholder="{{ __('app.address_city_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_region') }}</label>
                            <input type="text" wire:model="deliveryRegion" placeholder="{{ __('app.address_region_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_postal_code') }}</label>
                            <input type="text" wire:model="deliveryPostalCode"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                        </div>
                    </div>
                </div>
                @endif
                </div>
            </div>

            {{-- ── Validation error ─────────────────────────────────────────── --}}
            <div x-show="addressError" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <svg class="h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-red-700">{{ __('app.address_fields_required') }}</span>
            </div>

            {{-- ── Navigation ───────────────────────────────────────────────── --}}
            <div class="mt-4 flex gap-3">
                <button type="button"
                    @click="step = 2; addressError = false; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-800">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.back') }}
                </button>
                <button type="button" @click="tryProceedToReview()"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-white shadow-lg transition"
                    style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <span>{{ __('app.checkout_continue_review') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>{{-- end step 3 --}}

    {{-- ═══════════════════════════════════════════════════════════════════════════
         STEP 4 — Review & Place Order
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-3"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-cloak>

        <div class="mx-auto max-w-lg space-y-5">

            {{-- Heading --}}
            <div class="text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50">
                    <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('app.checkout_step_confirm') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('app.review_address_hint') }}</p>
            </div>

            {{-- Order summary card --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 bg-slate-50/70">
                    <h3 class="text-sm font-semibold text-slate-700">{{ __('app.checkout_order_summary') }}</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-xs text-slate-500">{{ __('app.project') }}</span>
                        <span class="text-xs font-semibold text-slate-800">{{ $quotation->project_name }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-xs text-slate-500">{{ __('app.id') }}</span>
                        <span class="text-xs font-mono text-slate-700">{{ $quotation->quotation_no }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-xs text-slate-500">{{ __('app.items_selected') }}</span>
                        <span class="text-xs font-semibold text-slate-800">{{ $itemCount }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-xs text-slate-500">{{ __('app.subtotal') }}</span>
                        <span class="text-xs font-mono font-medium text-slate-800">{{ number_format($subtotal, 2) }} {{ __('app.sar') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-xs text-slate-500">{{ __('app.tax_vat_15') }}</span>
                        <span class="text-xs font-mono font-medium text-slate-800">{{ number_format($tax, 2) }} {{ __('app.sar') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-4 bg-emerald-50/60">
                        <span class="text-sm font-bold text-slate-800">{{ __('app.total_amount') }}</span>
                        <span class="font-mono text-lg font-bold text-emerald-600">{{ number_format($total, 2) }} {{ __('app.sar') }}</span>
                    </div>
                </div>
            </div>

            {{-- Address summary card --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50/70">
                    <h3 class="text-sm font-semibold text-slate-700">{{ __('app.delivery_address') }}</h3>
                    <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                        :class="$wire.addressType === 'national' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                        x-text="$wire.addressType === 'national' ? '{{ __('app.address_type_national') }}' : '{{ __('app.address_type_detailed') }}'"
                    ></span>
                </div>
                <div class="divide-y divide-slate-100">
                    {{-- National fields --}}
                    <template x-if="$wire.addressType === 'national'">
                        <div class="divide-y divide-slate-100">
                            <template x-if="$wire.nationalBuildingNo">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.national_building_no') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalBuildingNo"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalStreet">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.national_street') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalStreet"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalDistrict">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_district') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalDistrict"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalCity">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_city') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalCity"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalPostalCode">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_postal_code') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalPostalCode"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalAdditionalNo">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.national_additional_no') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalAdditionalNo"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    {{-- Detailed fields --}}
                    <template x-if="$wire.addressType !== 'national'">
                        <div class="divide-y divide-slate-100">
                            <template x-if="$wire.deliveryStreet">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_street') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryStreet"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryDistrict">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_district') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryDistrict"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryCity">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_city') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryCity"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryRegion">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_region') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryRegion"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryPostalCode">
                                <div class="flex justify-between px-5 py-3">
                                    <span class="text-xs text-slate-500">{{ __('app.address_postal_code') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryPostalCode"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Warning note --}}
            <div class="flex gap-2.5 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3.5">
                <svg class="h-4 w-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-xs text-amber-700">{{ __('app.order_create_info') }}</span>
            </div>

            {{-- Navigation --}}
            <div class="flex gap-3 pb-8">
                <button type="button" @click="step = 3; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    {{ __('app.back') }}
                </button>
                <button
                    type="button"
                    wire:click="submitForApproval"
                    wire:loading.attr="disabled"
                    wire:target="submitForApproval"
                    class="flex-1 rounded-xl px-4 py-3 text-sm font-bold text-white shadow-lg transition"
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 6px 20px -4px rgba(16,185,129,0.5);"
                >
                    <span wire:loading.remove wire:target="submitForApproval">{{ __('app.checkout_place_order') }}</span>
                    <span wire:loading wire:target="submitForApproval">{{ __('app.creating_order') }}</span>
                </button>
            </div>

        </div>
    </div>{{-- end step 4 --}}

    @endif

</div>
