<div x-data="{ statusOpen: false, dateOpen: false, typeOpen: false }">

    {{-- ═══════════════════════════════════════════════════════
         PAGE HEADER
    ═══════════════════════════════════════════════════════ --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.crm_projects') }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ __('app.track_orders_engineering') }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.orders.export') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('app.export_excel') }}
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         STAT CARDS
    ═══════════════════════════════════════════════════════ --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        {{-- Total Orders --}}
        <div class="flex flex-col rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs font-medium text-slate-400">{{ __('app.total_orders') }}</p>
        </div>

        {{-- Open --}}
        <div class="flex flex-col rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">{{ __('app.open') }}</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900">{{ $stats['open'] }}</p>
            <p class="mt-1 text-xs font-medium text-slate-400">{{ __('app.open_orders') }}</p>
        </div>

        {{-- Closed --}}
        <div class="flex flex-col rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ __('app.closed') }}</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900">{{ $stats['closed'] }}</p>
            <p class="mt-1 text-xs font-medium text-slate-400">{{ __('app.closed_orders') }}</p>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════
         TABLE CARD
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Filters row --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </span>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('app.search_orders_projects_clients') }}"
                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 ps-9 pe-4 text-sm text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                >
            </div>

            <div class="flex flex-wrap items-center gap-2">

                {{-- Status dropdown --}}
                <div class="relative">
                    <button type="button" @click="statusOpen = !statusOpen"
                        class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                        :class="statusOpen || @js($status !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        {{ $status !== '' ? collect($statuses)->first(fn($s) => $s->value === $status)?->label() ?? __('app.status') : __('app.all_statuses') }}
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="statusOpen" x-cloak @click.outside="statusOpen = false"
                        class="absolute start-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                        <button type="button" wire:click="$set('status', '')" @click="statusOpen = false"
                            class="block w-full px-4 py-2 text-start text-sm hover:bg-slate-50 {{ $status === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                            {{ __('app.all_statuses') }}
                        </button>
                        @foreach($statuses as $s)
                            <button type="button" wire:click="$set('status', '{{ $s->value }}')" @click="statusOpen = false"
                                class="block w-full px-4 py-2 text-start text-sm hover:bg-slate-50 {{ $status === $s->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                                {{ $s->label() }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Date range dropdown --}}
                <div class="relative">
                    <button type="button" @click="dateOpen = !dateOpen"
                        class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                        :class="dateOpen || @js($dateRange !== '30') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        @php $dateLabel = match($dateRange) { '7'=>__('app.last_7_days'),'30'=>__('app.last_30_days'),'90'=>__('app.last_90_days'),default=>__('app.all_time') }; @endphp
                        {{ $dateLabel }}
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="dateOpen" x-cloak @click.outside="dateOpen = false"
                        class="absolute start-0 top-full z-20 mt-1.5 w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                        @foreach([['7',__('app.last_7_days')],['30',__('app.last_30_days')],['90',__('app.last_90_days')],['',__('app.all_time')]] as [$val,$lbl])
                            <button type="button" wire:click="$set('dateRange', '{{ $val }}')" @click="dateOpen = false"
                                class="block w-full px-4 py-2 text-start text-sm hover:bg-slate-50 {{ $dateRange === $val ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Type dropdown --}}
                <div class="relative">
                    <button type="button" @click="typeOpen = !typeOpen"
                        class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                        :class="typeOpen || @js($type !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $type !== '' ? collect($types)->first(fn($t) => $t->value === $type)?->label() ?? __('app.type') : __('app.all_types') }}
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="typeOpen" x-cloak @click.outside="typeOpen = false"
                        class="absolute start-0 top-full z-20 mt-1.5 w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                        <button type="button" wire:click="$set('type', '')" @click="typeOpen = false"
                            class="block w-full px-4 py-2 text-start text-sm hover:bg-slate-50 {{ $type === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                            {{ __('app.all_types') }}
                        </button>
                        @foreach($types as $t)
                            <button type="button" wire:click="$set('type', '{{ $t->value }}')" @click="typeOpen = false"
                                class="block w-full px-4 py-2 text-start text-sm hover:bg-slate-50 {{ $type === $t->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                                {{ $t->label() }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($hasActiveFilters)
                    <button type="button" wire:click="clearFilters"
                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                        {{ __('app.clear') }}
                    </button>
                @endif

            </div>

            {{-- Showing count --}}
            <p class="shrink-0 whitespace-nowrap text-sm text-slate-400 sm:ml-auto">
                {{ __('app.showing') }}
                <span class="font-semibold text-slate-700">
                    {{ $orders->isEmpty() ? '0' : $orders->firstItem() . '-' . $orders->lastItem() }}
                </span>
                {{ __('app.of') }}
                <span class="font-semibold text-slate-700">{{ $orders->total() }}</span>
                {{ __('app.results') }}
            </p>

        </div>

        {{-- ───── Table ────────────────────────────────────────────────────────── --}}
        <div class="overflow-x-auto">
            @if($orders->isEmpty())
                <div class="py-24 text-center">
                    <svg class="mx-auto mb-4 h-12 w-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-400">{{ __('app.no_orders_found') }}</p>
                    <p class="mt-1 text-xs text-slate-300">{{ __('app.try_adjusting_search_filters') }}</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-start">
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400 w-36">{{ __('app.order_hash') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('app.project') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('app.client') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400 w-36">{{ __('app.type') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400 w-40">{{ __('app.amount_sar') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400 w-36">{{ __('app.status') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-400 w-32">{{ __('app.date') }}</th>
                            <th class="px-4 py-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $order)
                            @php
                                $sv = $order->status->value ?? 'open';

                                $dotClass = match($sv) {
                                    'open'   => 'bg-emerald-500',
                                    'closed' => 'bg-slate-400',
                                    default  => 'bg-slate-400',
                                };
                                $statusTextClass = match($sv) {
                                    'open'   => 'text-emerald-600',
                                    'closed' => 'text-slate-500',
                                    default  => 'text-slate-500',
                                };

                                $typeLabel     = $order->quotationRequest?->project_status?->label() ?? '—';
                                $itemsTotal    = $order->items->sum(fn($i) => (float) ($i->total_price ?? 0));
                                $displayAmount = $itemsTotal > 0 ? $itemsTotal : (float) $order->total_amount;
                                $clientCompany = $order->client?->clientProfile?->company_name ?? $order->client?->name ?? '—';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">

                                {{-- ORDER # --}}
                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-800">#{{ $order->order_no }}</span>
                                </td>

                                {{-- PROJECT --}}
                                <td class="px-5 py-4 max-w-[220px]">
                                    <p class="font-semibold text-slate-800 leading-tight truncate">
                                        {{ $order->quotationRequest?->project_name ?? '—' }}
                                    </p>
                                    @if($order->client?->clientProfile?->city)
                                        <p class="mt-0.5 text-xs text-slate-400 truncate">
                                            {{ $order->client->clientProfile->city }}
                                        </p>
                                    @endif
                                </td>

                                {{-- CLIENT --}}
                                <td class="px-5 py-4">
                                    <span class="text-slate-700">{{ $clientCompany }}</span>
                                </td>

                                {{-- TYPE --}}
                                <td class="px-5 py-4">
                                    @if($typeLabel !== '—')
                                        <span class="inline-flex items-center rounded border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                            {{ $typeLabel }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- AMOUNT --}}
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-800">
                                        {{ number_format($displayAmount, 0) }}
                                    </span>
                                </td>

                                {{-- STATUS --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium {{ $statusTextClass }}">
                                        <span class="inline-block h-2 w-2 rounded-full shrink-0 {{ $dotClass }}"></span>
                                        {{ $order->status->label() }}
                                    </span>
                                </td>

                                {{-- DATE --}}
                                <td class="px-5 py-4">
                                    <span class="whitespace-nowrap text-xs text-slate-500">
                                        {{ $order->created_at?->format('M d, Y') }}
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="px-4 py-4">
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak @click.outside="open = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute end-0 top-full z-30 mt-1 w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                                            <a href="{{ route('admin.orders.show', $order->uuid) }}"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ __('app.view_details') }}
                                            </a>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ───── Pagination ───────────────────────────────────────────────────── --}}
        @if($orders->hasPages())
        <div class="flex items-center justify-between border-t border-slate-100 px-5 py-4">

            @if($orders->onFirstPage())
                <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.previous') }}
                </span>
            @else
                <button wire:click="previousPage"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.previous') }}
                </button>
            @endif

            <nav class="flex items-center gap-1">
                @php
                    $cp    = $orders->currentPage();
                    $lp    = $orders->lastPage();
                    $pStart = max(1, $cp - 2);
                    $pEnd   = min($lp, $cp + 2);
                @endphp
                @if($pStart > 1)
                    <button wire:click="gotoPage(1)"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">1</button>
                    @if($pStart > 2)<span class="px-1 text-slate-400 text-sm">...</span>@endif
                @endif
                @for($pg = $pStart; $pg <= $pEnd; $pg++)
                    @if($pg === $cp)
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-sm font-semibold text-white">{{ $pg }}</span>
                    @else
                        <button wire:click="gotoPage({{ $pg }})"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">{{ $pg }}</button>
                    @endif
                @endfor
                @if($pEnd < $lp)
                    @if($pEnd < $lp - 1)<span class="px-1 text-slate-400 text-sm">...</span>@endif
                    <button wire:click="gotoPage({{ $lp }})"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">{{ $lp }}</button>
                @endif
            </nav>

            @if($orders->hasMorePages())
                <button wire:click="nextPage"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
                    {{ __('app.next') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-300">
                    {{ __('app.next') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif

        </div>
        @endif

    </div>

</div>
