<div
    x-data="{
        statusOpen: false,
        dateOpen: false,
        toast: null,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>

    {{-- ───── Toast ────────────────────────────────────────────────────────────── --}}
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

    {{-- ───── Page Header ───────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.track_orders') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('app.monitor_manage_orders') }}</p>
        </div>
    </div>

    {{-- ───── Stat Cards ────────────────────────────────────────────────────────── --}}
    <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.total_orders') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                <svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.active_orders') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-emerald-500">{{ $stats['active'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="h-6 w-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-blue-100 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.completed_orders') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['completed'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.closed_orders') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['closed'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ───── Section Header + Filters ─────────────────────────────────────────── --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <h2 class="flex-1 text-base font-bold text-slate-900">{{ __('app.recent_orders') }}</h2>

        {{-- Search --}}
        <div class="relative min-w-[220px]">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('app.search_orders') }}"
                class="h-9 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        {{-- Status filter --}}
        <div class="relative">
            <button
                type="button"
                @click="statusOpen = !statusOpen"
                class="inline-flex items-center gap-2 rounded-lg border bg-white px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="statusOpen || @js($status !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($status !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button>
            <div
                x-show="statusOpen"
                x-cloak
                @click.outside="statusOpen = false"
                class="absolute right-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <button type="button" wire:click="$set('status', '')" @click="statusOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    {{ __('app.all_statuses') }}
                </button>
                @foreach($statuses as $statusItem)
                    <button type="button" wire:click="$set('status', '{{ $statusItem->value }}')" @click="statusOpen = false"
                        class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === $statusItem->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                        {{ $statusItem->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Date filter --}}
        <div class="relative">
            <button
                type="button"
                @click="dateOpen = !dateOpen"
                class="inline-flex items-center gap-2 rounded-lg border bg-white px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="dateOpen || @js($created_from !== '' || $created_to !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($created_from !== '' || $created_to !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>
            <div
                x-show="dateOpen"
                x-cloak
                @click.outside="dateOpen = false"
                class="absolute right-0 top-full z-20 mt-1.5 w-56 rounded-xl border border-slate-200 bg-white p-4 shadow-lg"
            >
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.date_range') }}</p>
                <div class="flex flex-col gap-2">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ __('app.from') }}</label>
                        <input type="date" wire:model.live="created_from"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none focus:border-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ __('app.to') }}</label>
                        <input type="date" wire:model.live="created_to"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none focus:border-emerald-400">
                    </div>
                </div>
            </div>
        </div>

        @if($hasActiveFilters)
            <button type="button" wire:click="clearFilters"
                class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                {{ __('app.clear') }}
            </button>
        @endif
    </div>

    {{-- ───── Order Cards ────────────────────────────────────────────────────────── --}}
    @if($orders->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
            <svg class="mx-auto mb-4 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">{{ __('app.no_orders_found') }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ __('app.try_adjust_filters') }}</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                @php
                    $sv = $order->status->value ?? 'open';

                    $badgeClass = match($sv) {
                        'open'   => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200',
                        'closed' => 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',
                        default  => 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',
                    };

                    $leftBorder = match($sv) {
                        'open'   => 'border-l-emerald-400',
                        'closed' => 'border-l-slate-300',
                        default  => 'border-l-slate-300',
                    };

                    $statusMsg = match($sv) {
                        'open'   => __('app.order_open_msg'),
                        'closed' => __('app.order_closed_msg'),
                        default  => __('app.status_update_pending'),
                    };

                    $msgIconClass = match($sv) {
                        'closed' => 'text-slate-400',
                        default  => 'text-emerald-500',
                    };

                    $itemsTotal = $order->items->sum(fn($i) => (float) ($i->total_price ?? 0));
                @endphp

                <div class="group flex flex-col gap-4 rounded-2xl border border-slate-200 border-l-4 {{ $leftBorder }} bg-white px-6 py-5 shadow-sm transition hover:shadow-md sm:flex-row sm:items-center sm:justify-between">

                    {{-- Left: info --}}
                    <div class="flex-1 min-w-0">

                        {{-- Badge + Order No --}}
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                                {{ $order->status->label() }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">ORDER: #{{ $order->order_no }}</span>
                        </div>

                        {{-- Project name --}}
                        <h3 class="text-base font-bold text-slate-900 truncate">
                            {{ $order->quotationRequest?->project_name ?? __('app.no_project_name') }}
                        </h3>

                        {{-- Meta line --}}
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ $order->quotationRequest?->project_status?->label() ?? 'General' }}
                            &middot;
                            {{ __('app.placed_colon') }} {{ $order->created_at?->format('M d, Y') }}
                            @if($itemsTotal > 0)
                                &middot;
                                <span class="font-semibold text-slate-600">{{ number_format($itemsTotal, 2) }} {{ __('app.sar') }}</span>
                            @endif
                        </p>

                        {{-- Status message --}}
                        <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
                            @if(in_array($sv, ['cancelled', 'refunded']))
                                <svg class="h-4 w-4 shrink-0 {{ $msgIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif(in_array($sv, ['completed', 'delivered']))
                                <svg class="h-4 w-4 shrink-0 {{ $msgIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="h-4 w-4 shrink-0 {{ $msgIconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                            {{ $statusMsg }}
                        </p>
                    </div>

                    {{-- Right: actions --}}
                    <div class="flex shrink-0 items-center gap-2">
                        <a
                            href="{{ route('enduser.orders.show', $order->uuid) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600"
                        >
                            {{ __('app.view_order') }}
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">
                {{ __('app.showing') }}
                <span class="font-semibold text-slate-700">{{ $orders->firstItem() }}</span>
                {{ __('app.to') }}
                <span class="font-semibold text-slate-700">{{ $orders->lastItem() }}</span>
                {{ __('app.of') }}
                <span class="font-semibold text-slate-700">{{ $orders->total() }}</span>
                {{ __('app.results') }}
            </p>

            @if($orders->hasPages())
            <nav class="flex items-center gap-1">
                @if($orders->onFirstPage())
                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <button wire:click="previousPage" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                @endif

                @foreach($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                    @if($page == $orders->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">{{ $page }}</button>
                    @endif
                @endforeach

                @if($orders->hasMorePages())
                    <button wire:click="nextPage" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @else
                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </nav>
            @endif
        </div>
    @endif

</div>
