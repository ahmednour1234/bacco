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
        x-data="{
            msgIndex: 0,
            bar: 5,
            messages: [
                'جاري البحث في كتالوج المنتجات…',
                'نحلل بنود جدول الكميات…',
                'نستشير الذكاء الاصطناعي للتسعير…',
                'نقارن الأسعار بسوق السعودية…',
                'نحسب أفضل الأسعار لك…',
                'يرجى الانتظار، تقريباً انتهينا…',
            ],
            init() {
                setInterval(() => {
                    this.msgIndex = (this.msgIndex + 1) % this.messages.length;
                }, 2500);
                setInterval(() => {
                    if (this.bar < 90) this.bar += Math.random() * 7;
                }, 1400);
            }
        }"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="background: rgba(2,8,23,0.72); backdrop-filter: blur(8px);"
    >
        <div
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative mx-4 w-full max-w-sm overflow-hidden rounded-3xl bg-white shadow-2xl"
        >
            {{-- Top progress bar --}}
            <div class="h-1 w-full bg-slate-100">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-1000"
                    :style="`width: ${bar}%`"
                ></div>
            </div>

            <div class="px-8 pb-8 pt-7 text-center">

                {{-- Animated orb: conic gradient spinning ring + SAR symbol --}}
                <div class="relative mx-auto mb-6 h-20 w-20">
                    <span class="absolute inset-0 animate-ping rounded-full bg-emerald-100 opacity-50" style="animation-duration:2.2s;"></span>
                    <span class="absolute inset-0 rounded-full" style="background: conic-gradient(#10b981 0%, #14b8a6 35%, #06b6d4 60%, #10b981 100%); animation: qspin 1.8s linear infinite;"></span>
                    <span class="absolute inset-1.5 flex items-center justify-center rounded-full bg-white">
                        <span class="text-xl font-extrabold text-emerald-600" style="font-family: system-ui, sans-serif;">﷼</span>
                    </span>
                </div>

                {{-- Title --}}
                <h3 class="text-xl font-bold text-slate-900">تحليل الأسعار</h3>

                {{-- Rotating messages --}}
                <div class="relative mt-2 h-6 overflow-hidden">
                    <template x-for="(msg, i) in messages" :key="i">
                        <p
                            class="absolute inset-0 flex items-center justify-center text-sm text-slate-500"
                            style="transition: opacity 0.4s, transform 0.4s;"
                            :style="i === msgIndex
                                ? 'opacity:1; transform:translateY(0)'
                                : 'opacity:0; transform:translateY(8px)'"
                            x-text="msg"
                        ></p>
                    </template>
                </div>

                {{-- Wave dots --}}
                <div class="mt-5 flex items-end justify-center gap-1.5" style="height:18px;">
                    <span class="w-2 rounded-full bg-emerald-400" style="animation: qwave 1.1s ease-in-out infinite; animation-delay:0s;"></span>
                    <span class="w-2 rounded-full bg-emerald-500" style="animation: qwave 1.1s ease-in-out infinite; animation-delay:0.18s;"></span>
                    <span class="w-2 rounded-full bg-teal-500"    style="animation: qwave 1.1s ease-in-out infinite; animation-delay:0.36s;"></span>
                    <span class="w-2 rounded-full bg-emerald-400" style="animation: qwave 1.1s ease-in-out infinite; animation-delay:0.54s;"></span>
                    <span class="w-2 rounded-full bg-emerald-300" style="animation: qwave 1.1s ease-in-out infinite; animation-delay:0.72s;"></span>
                </div>

                {{-- Steps (RTL timeline) --}}
                <div class="mt-7 space-y-3">
                    {{-- Done --}}
                    <div class="flex items-center justify-end gap-3">
                        <span class="text-sm font-medium text-slate-700">البحث في الكتالوج</span>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </div>
                    {{-- In progress --}}
                    <div class="flex items-center justify-end gap-3">
                        <span class="text-sm font-medium text-emerald-700">تقدير الذكاء الاصطناعي</span>
                        <span class="relative flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 ring-2 ring-emerald-400">
                            <svg class="h-3.5 w-3.5 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </span>
                    </div>
                    {{-- Pending --}}
                    <div class="flex items-center justify-end gap-3 opacity-30">
                        <span class="text-sm font-medium text-slate-500">حفظ وتحديث العرض</span>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-slate-200 bg-slate-50">
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                        </span>
                    </div>
                </div>

            </div>

            {{-- Keyframes injected once --}}
            <style>
                @keyframes qspin  { to { transform: rotate(360deg); } }
                @keyframes qwave  {
                    0%, 100% { height: 6px;  }
                    50%       { height: 16px; }
                }
            </style>
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
