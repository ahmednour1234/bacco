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

    {{-- ───── Pricing loading banner ──────────────────────────────────────────── --}}
    @if($fetchingPrices)
    <div class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5">
        <svg class="h-5 w-5 animate-spin shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-emerald-800">Fetching prices…</p>
            <p class="text-xs text-emerald-600">Looking up the products catalogue then estimating with AI for unmatched items. This may take a few seconds.</p>
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
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</dt>
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
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Submitted Date</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $quotation->updated_at?->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Project Type</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700">
                        {{ $quotation->project_status?->label() ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">ID</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-700 font-mono">{{ $quotation->quotation_no }}</dd>
                </div>
            </dl>
        </div>

        {{-- Action buttons --}}
        <div class="flex shrink-0 items-center gap-3">
            {{-- Export PDF placeholder --}}
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </button>

            @if(in_array($quotation->status->value, ['draft', 'tender'], true))
            <a
                href="{{ route('enduser.quotations.edit', $quotation->uuid) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Quotation
            </a>
            @endif
        </div>
    </div>

    {{-- ───── BOQ Table card ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-800">Bill of Quantities (BOQ)</h2>

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
                    Re-fetch Prices
                </button>

                {{-- Remove All Products --}}
                <button
                    type="button"
                    wire:click="removeAllProducts"
                    wire:loading.attr="disabled"
                    wire:confirm="Remove all product selections and prices?"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 disabled:opacity-60 transition"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Remove All Products
                </button>
            </div>
            @endif
        </div>

        <div class="p-6">
            @if(empty($items))
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                    No items found for this quotation.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-3 py-3 w-10"></th>{{-- checkbox --}}
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">QTY</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">Brand</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">Engineering</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-10">Src</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">Price (SAR)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-44">Selected Product</th>
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
                                            <span title="Selected" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
                                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span title="Not selected" class="inline-block h-6 w-6 rounded-full border-2 border-slate-200 bg-white"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item['description'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float)($item['quantity'] ?? 0)) }}</td>
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
                                    <td class="px-4 py-3 text-center">
                                        @if(($item['price_source'] ?? null) === 'products')
                                            <span title="Matched from Products catalogue" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold">P</span>
                                        @elseif(($item['price_source'] ?? null) === 'gemini')
                                            <span title="Estimated by Gemini AI" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-purple-100 text-purple-700 text-xs font-bold">AI</span>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
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
                                            <span class="text-xs italic text-slate-400">Not priced</span>
                                        @endif
                                    </td>

                                    {{-- Selected Product (always visible; picker actions for editable) --}}
                                    <td class="px-4 py-3">
                                        @if($canEdit)
                                            <div class="flex items-center gap-1.5">
                                                @if($hasProduct)
                                                    <span class="flex-1 min-w-0 truncate text-xs font-semibold text-emerald-700" title="{{ $item['product_name'] ?? '' }}">{{ $item['product_name'] ?? '—' }}</span>
                                                    <button
                                                        type="button"
                                                        wire:click="removeProduct({{ $item['id'] }})"
                                                        title="Remove product"
                                                        class="shrink-0 inline-flex items-center justify-center h-5 w-5 rounded bg-red-50 text-red-400 hover:bg-red-100 transition"
                                                    >
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    @if($isPickerOpen)
                                                        <button
                                                            type="button"
                                                            wire:click="closePicker"
                                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                                        >
                                                            Close
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            wire:click="openPicker({{ $item['id'] }})"
                                                            class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition"
                                                        >
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                                                            </svg>
                                                            Select
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            {{-- Read-only: just show the product name --}}
                                            @if(!empty($item['product_name']))
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ $item['product_name'] }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-300">Not selected</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                {{-- Product picker panel --}}
                                @if($isPickerOpen && $canEdit)
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <td colspan="12" class="px-4 py-4">
                                        <div class="max-w-xl space-y-3">
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Search products catalogue</p>
                                            <div class="relative">
                                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                                                </svg>
                                                <input
                                                    type="text"
                                                    wire:model.live.debounce.300ms="productSearch"
                                                    placeholder="Type product name…"
                                                    autofocus
                                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-800 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                                />
                                                <div wire:loading wire:target="updatedProductSearch" class="absolute right-3 top-1/2 -translate-y-1/2">
                                                    <svg class="h-4 w-4 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            @if(strlen(trim($productSearch)) >= 2)
                                                @if(empty($productResults))
                                                    <p class="text-sm italic text-slate-400 px-1">No products found for "{{ $productSearch }}".</p>
                                                @else
                                                    <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                                        @foreach($productResults as $product)
                                                            <li>
                                                                <button
                                                                    type="button"
                                                                    wire:click="selectProduct({{ $item['id'] }}, {{ $product['id'] }})"
                                                                    class="w-full flex items-center justify-between px-4 py-3 text-left text-sm hover:bg-emerald-50 transition group"
                                                                >
                                                                    <div class="min-w-0">
                                                                        <p class="font-medium text-slate-800 truncate group-hover:text-emerald-700">{{ $product['name'] }}</p>
                                                                        <p class="text-xs text-slate-400 mt-0.5">Unit: <span class="font-semibold text-slate-600">{{ $product['unit_name'] ?: '—' }}</span></p>
                                                                    </div>
                                                                    <span class="ml-4 shrink-0 font-mono text-sm font-semibold text-emerald-600">
                                                                        {{ number_format($product['unit_price'], 2) }} SAR
                                                                    </span>
                                                                </button>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            @else
                                                <p class="text-xs text-slate-400 px-1">Type at least 2 characters to search.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endif

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
                    <h3 class="text-sm font-semibold text-slate-700">Financial Summary</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $selectedCount }} item{{ $selectedCount !== 1 ? 's' : '' }} selected
                    </span>
                </div>

                <div class="space-y-2.5">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-mono font-medium">{{ number_format($subtotal, 2) }} SAR</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Tax/VAT (15%)</span>
                        <span class="font-mono font-medium">{{ number_format($tax, 2) }} SAR</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3 flex justify-between">
                        <span class="text-sm font-bold text-slate-800">Total Amount</span>
                        <span class="font-mono text-lg font-bold text-emerald-600">{{ number_format($total, 2) }} SAR</span>
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
                        Select at least one item to submit.
                    @else
                        All selected items have no price yet. Cannot submit.
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
                    <span wire:loading.remove wire:target="submitForApproval">Submit for Approval</span>
                    <span wire:loading wire:target="submitForApproval">Submitting…</span>
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
                                <h3 class="text-sm font-bold text-slate-900">Submit for Approval</h3>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                                    This quotation will be sent to the team for review.
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
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total</span>
                            <span class="font-mono text-sm font-bold text-emerald-600">{{ number_format($total, 2) }} SAR</span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                            <button
                                type="button"
                                @click="confirmOpen = false"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                            >
                                Cancel
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
                                Yes, Submit
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
