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
    <div
        x-data="{ dots: 0 }"
        x-init="setInterval(() => dots = (dots + 1) % 4, 500)"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="background: rgba(15,23,42,0.65); backdrop-filter: blur(6px);"
    >
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative mx-4 w-full max-w-sm rounded-3xl bg-white px-8 py-10 shadow-2xl text-center"
        >
            {{-- Animated rings --}}
            <div class="relative mx-auto mb-7 h-24 w-24">
                <span class="absolute inset-0 rounded-full border-4 border-emerald-100"></span>
                <span class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-emerald-500" style="animation-duration:1s;"></span>
                <span class="absolute inset-2 animate-spin rounded-full border-4 border-transparent border-t-emerald-300" style="animation-duration:1.5s; animation-direction:reverse;"></span>
                <span class="absolute inset-0 flex items-center justify-center">
                    <svg class="h-9 w-9 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </span>
            </div>

            {{-- Title --}}
            <h3 class="text-lg font-bold text-slate-800">جاري جلب الأسعار</h3>
            <p class="mt-1 text-sm text-slate-500">Fetching prices from AI &amp; catalogue</p>

            {{-- Animated dots progress --}}
            <div class="mt-5 flex items-center justify-center gap-2">
                <template x-for="n in 4" :key="n">
                    <span
                        class="h-2.5 w-2.5 rounded-full transition-all duration-300"
                        :class="dots === (n - 1) ? 'bg-emerald-500 scale-125' : 'bg-slate-200'"
                    ></span>
                </template>
            </div>

            {{-- Steps --}}
            <ul class="mt-6 space-y-2 text-left text-xs text-slate-500">
                <li class="flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    البحث في كتالوج المنتجات
                </li>
                <li class="flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 shrink-0 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    تقدير الأسعار بالذكاء الاصطناعي
                </li>
                <li class="flex items-center gap-2 opacity-40">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    </svg>
                    حفظ الأسعار وتحديث العرض
                </li>
            </ul>

            <p class="mt-6 text-xs text-slate-400">قد يستغرق هذا دقيقة واحدة…</p>
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

            @if(in_array($quotation->status->value, ['draft', 'tender'], true))
            <a
                href="{{ route('enduser.quotations.edit', $quotation->uuid) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ __('app.edit_quotation') }}
            </a>
            @endif
        </div>
    </div>

    {{-- ───── BOQ Table card ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_full') }}</h2>

            {{-- Edit toolbar — only while editable --}}
            @if(in_array($quotation->status->value, ['tender', 'draft'], true))
            <div class="flex flex-wrap items-center gap-2">
                {{-- Re-fetch Prices --}}
                <button
                    type="button"
                    wire:click="refetchPrices"
                    wire:loading.attr="disabled"
                    wire:target="refetchPrices"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 disabled:opacity-60 transition"
                >
                    <svg wire:loading.remove wire:target="refetchPrices" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg wire:loading wire:target="refetchPrices" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ __('app.refetch_prices') }}
                </button>

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
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(15,23,42,0.45); backdrop-filter: blur(4px);"
                    @keydown.escape.window="confirmOpen = false"
                >
                    <div
                        x-show="confirmOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        @click.stop
                        class="w-full max-w-sm rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden"
                    >
                        {{-- Modal header --}}
                        <div class="flex items-center gap-3 px-5 pt-5 pb-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-slate-900">{{ __('app.submit_for_approval') }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                                    {{ __('app.quotation_sent_review') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="confirmOpen = false"
                                class="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Summary line --}}
                        <div class="mx-5 mb-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ __('app.total') }}</span>
                            <span class="font-mono text-sm font-bold text-emerald-600">{{ number_format($total, 2) }} {{ __('app.sar') }}</span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                            <button
                                type="button"
                                @click="confirmOpen = false"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                            >
                                {{ __('app.cancel') }}
                            </button>
                            <button
                                type="button"
                                @click="confirmOpen = false; $wire.submitForApproval()"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"/>
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
