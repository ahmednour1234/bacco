<div
    x-data="{
        toast: null,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
    class="min-h-screen bg-slate-50"
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

    @if($order)
    @php
        $statusColors = [
            'pending'    => ['dot' => 'bg-amber-400',  'text' => 'text-amber-700',  'badge' => 'bg-amber-50 text-amber-700 ring-amber-200'],
            'confirmed'  => ['dot' => 'bg-blue-500',   'text' => 'text-blue-700',   'badge' => 'bg-blue-50 text-blue-700 ring-blue-200'],
            'processing' => ['dot' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
            'shipped'    => ['dot' => 'bg-violet-500', 'text' => 'text-violet-700', 'badge' => 'bg-violet-50 text-violet-700 ring-violet-200'],
            'delivered'  => ['dot' => 'bg-emerald-500','text' => 'text-emerald-700','badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            'completed'  => ['dot' => 'bg-green-600',  'text' => 'text-green-700',  'badge' => 'bg-green-50 text-green-700 ring-green-200'],
            'cancelled'  => ['dot' => 'bg-red-400',    'text' => 'text-red-600',    'badge' => 'bg-red-50 text-red-600 ring-red-200'],
            'refunded'   => ['dot' => 'bg-pink-400',   'text' => 'text-pink-700',   'badge' => 'bg-pink-50 text-pink-700 ring-pink-200'],
        ];
        $sv = $order->status->value ?? 'pending';
        $sc = $statusColors[$sv] ?? $statusColors['pending'];
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- PAGE HEADER                                                            --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}"
               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white shadow-sm hover:bg-slate-50 transition">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('app.order_hash') }}{{ $order->order_no }}</h1>
                <nav class="mt-0.5 flex items-center gap-1 text-xs text-slate-400">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600">{{ __('app.dashboard') }}</a>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <a href="{{ route('admin.orders.index') }}" class="hover:text-slate-600">{{ __('app.orders') }}</a>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="font-medium text-slate-600">{{ $order->order_no }}</span>
                </nav>
            </div>
        </div>
        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4H8v4a1 1 0 001 1zm1-10V4a1 1 0 00-1-1H9a1 1 0 00-1 1v3"/>
            </svg>
            {{ __('app.print') }}
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- STATUS BAR                                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div class="flex items-center gap-4">
            <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold ring-1 ring-inset {{ $sc['badge'] }}">
                <span class="h-2.5 w-2.5 rounded-full {{ $sc['dot'] }}"></span>
                {{ $order->status->label() }}
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.current_status') }}</p>
                <p class="mt-0.5 text-sm font-semibold text-slate-700">{{ __('app.last_updated') }} {{ $order->updated_at->diffForHumans() }}</p>
            </div>
        </div>

        <div class="h-10 w-px bg-slate-200 mx-2"></div>

        <button
            wire:click="openStatusModal"
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ __('app.change_order_status') }}
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- CHANGE STATUS MODAL                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($showStatusModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div
            x-data
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200"
        >
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-800">{{ __('app.change_order_status') }}</h3>
                </div>
                <button wire:click="$set('showStatusModal', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-slate-500">
                {{ __('app.select_new_status_order') }} <span class="font-semibold text-slate-700">{{ $order->order_no }}</span>.
            </p>

            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('app.new_status') }}</label>
            <select
                wire:model="newStatus"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
            >
                @foreach(\App\Enums\OrderStatusEnum::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>

            <div class="mt-5 flex gap-3">
                <button
                    wire:click="updateStatus"
                    wire:loading.attr="disabled"
                    class="flex-1 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60 transition"
                >
                    <span wire:loading.remove wire:target="updateStatus">{{ __('app.confirm_change') }}</span>
                    <span wire:loading wire:target="updateStatus">Updating…</span>
                </button>
                <button
                    wire:click="$set('showStatusModal', false)"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                >
                    {{ __('app.cancel') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- PROGRESS STEPPER                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm overflow-x-auto">
        <h2 class="mb-6 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.order_progress') }}</h2>

        <div class="relative flex items-start justify-between min-w-[600px]">
            @foreach($steps as $step)
                <div class="relative flex flex-1 flex-col items-center">
                    @if(!$loop->last)
                        <div class="absolute top-5 left-1/2 h-0.5 w-full {{ $step['state'] === 'completed' ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                    @endif

                    @if($step['state'] === 'completed')
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500 shadow-md shadow-emerald-200 ring-4 ring-emerald-50">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @elseif($step['state'] === 'in_progress')
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-emerald-500 bg-white ring-4 ring-emerald-50">
                            <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    @else
                        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-[3px] border-slate-800 bg-white">
                        </div>
                    @endif

                    <div class="mt-3 w-full px-1 text-center">
                        <p class="text-xs font-bold leading-snug {{ $step['state'] === 'completed' ? 'text-emerald-700' : ($step['state'] === 'in_progress' ? 'text-slate-800' : 'text-slate-400') }}">
                            {{ $step['label'] }}
                        </p>
                        <p class="mt-0.5 text-[10px] leading-none {{ $step['state'] === 'completed' ? 'text-emerald-500' : ($step['state'] === 'in_progress' ? 'text-indigo-500 font-semibold' : 'text-slate-300') }}">
                            @if($step['state'] === 'completed') {{ __('app.status_completed') }}
                            @elseif($step['state'] === 'in_progress') {{ __('app.in_progress') }}
                            @else {{ __('app.status_pending') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ORDER INFO GRID — 2 cols (no Payment)                                  --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Order Details --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-700">{{ __('app.order_details') }}</h3>
            </div>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.order_hash') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $order->order_no }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.project') }}</dt>
                    <dd class="font-semibold text-slate-800 text-right max-w-[200px] truncate" title="{{ $order->quotationRequest?->project_name ?? '—' }}">
                        {{ $order->quotationRequest?->project_name ?? '—' }}
                    </dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.order_date') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $order->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.items') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ count($items) }}</dd>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-100 pt-3 mt-1">
                    <dt class="font-bold text-slate-600">{{ __('app.grand_total') }}</dt>
                    <dd class="font-mono font-bold text-slate-900 text-base">{{ number_format((float)$order->grand_total, 2) }} SAR</dd>
                </div>
            </dl>
        </div>

        {{-- Client --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50">
                    <svg class="h-4 w-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-700">{{ __('app.client') }}</h3>
            </div>
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 text-sm font-bold text-white">
                    {{ strtoupper(substr($order->client?->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ $order->client?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $order->client?->email ?? '—' }}</p>
                </div>
            </div>
            <dl class="space-y-2.5 border-t border-slate-100 pt-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.company') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $order->client?->clientProfile?->company_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.phone') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $order->client?->clientProfile?->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-slate-400">{{ __('app.city') }}</dt>
                    <dd class="font-semibold text-slate-800">{{ $order->client?->clientProfile?->city ?? '—' }}</dd>
                </div>
            </dl>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- BOQ TABLE                                                              --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50">
                    <svg class="h-4 w-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <h2 class="text-sm font-bold text-slate-800">{{ __('app.bill_of_quantities') }}</h2>
            </div>
            <span class="rounded-full bg-violet-50 px-3 py-0.5 text-xs font-bold uppercase tracking-wide text-violet-600">
                {{ count($items) }} {{ count($items) === 1 ? __('app.item') : __('app.items') }}
            </span>
        </div>
        <div class="overflow-x-auto">
            @if(empty($items))
                <div class="px-6 py-12 text-center text-sm text-slate-400">{{ __('app.no_items_order') }}</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left">
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400">#</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 min-w-[220px]">{{ __('app.description') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-28">{{ __('app.qty_unit') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-28">{{ __('app.brand') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-28 text-right">{{ __('app.unit_price_sar') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-16 text-right">{{ __('app.disc_percent') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-32 text-right">{{ __('app.total_sar') }}</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-24 text-center">{{ __('app.updates') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $idx => $item)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-4 text-slate-400 text-xs font-mono">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $item['description'] ?: '—' }}</p></td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800">{{ number_format((float)$item['quantity'], 0) }}</p>
                                    <p class="text-xs text-slate-400 uppercase mt-0.5">{{ $item['unit'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 text-xs font-medium">{{ $item['brand'] }}</td>
                                <td class="px-5 py-4 text-right font-mono text-slate-700">{{ number_format((float)$item['unit_price'], 2) }}</td>
                                <td class="px-5 py-4 text-right text-xs">
                                    @if($item['discount'] > 0)
                                        <span class="text-amber-600 font-semibold">{{ $item['discount'] }}%</span>
                                    @else —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">{{ number_format((float)$item['total_price'], 2) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            wire:click="openEngModal({{ $item['id'] }})"
                                            title="{{ __('app.add_engineering_update') }}"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="openLogModal({{ $item['id'] }})"
                                            title="{{ __('app.add_logistics_update') }}"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-orange-50 text-orange-500 hover:bg-orange-100 hover:text-orange-700 transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50">
                            <td colspan="6" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('app.subtotal') }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-slate-800">{{ number_format((float)$order->total_amount, 2) }}</td>
                        </tr>
                        <tr class="bg-slate-50">
                            <td colspan="6" class="px-5 py-2 text-right text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('app.vat_15') }}</td>
                            <td class="px-5 py-2 text-right font-mono font-bold text-slate-800">{{ number_format((float)$order->vat_amount, 2) }}</td>
                        </tr>
                        <tr class="border-t border-slate-200 bg-emerald-50">
                            <td colspan="6" class="px-5 py-3 text-right text-sm font-bold text-emerald-700">{{ __('app.grand_total') }}</td>
                            <td class="px-5 py-3 text-right font-mono text-lg font-bold text-emerald-700">{{ number_format((float)$order->grand_total, 2) }} SAR</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ENGINEERING & LOGISTICS                                                --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Engineering Updates --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-700">{{ __('app.engineering_updates') }}</h3>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-600">{{ count($engUpdates) }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ __('app.use_row_buttons') }}</p>
            </div>
            @if(empty($engUpdates))
                <div class="flex flex-col items-center justify-center px-5 py-10 text-center">
                    <svg class="h-9 w-9 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm text-slate-400">{{ __('app.no_engineering_updates') }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach($engUpdates as $eu)
                        <div class="flex items-start justify-between gap-3 px-5 py-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                </span>
                                <div class="flex-1 min-w-0">
                                    @if($editingEngId === $eu['id'])
                                        <div class="flex items-center gap-2">
                                            <select wire:model="editingEngStatus" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                                                @foreach(\App\Enums\EngineeringStatusEnum::cases() as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button wire:click="updateEngStatus" class="rounded-md bg-emerald-600 px-2 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition">Save</button>
                                            <button wire:click="cancelEditEng" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition">Cancel</button>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($eu['item_desc'])
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200 truncate max-w-[160px]">{{ $eu['item_desc'] }}</span>
                                            @endif
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">{{ $eu['label'] }}</span>
                                            <span class="text-xs text-slate-400">{{ $eu['date'] }}</span>
                                            <span class="text-xs text-slate-400">· {{ $eu['user'] }}</span>
                                        </div>
                                    @endif
                                    @if($eu['notes'])
                                        <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $eu['notes'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($editingEngId !== $eu['id'])
                                    <button wire:click="startEditEng({{ $eu['id'] }}, '{{ $eu['status'] }}')" class="text-slate-300 hover:text-blue-500 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H7v-3.414a2 2 0 01.586-1.414z"/></svg>
                                    </button>
                                @endif
                                <button wire:click="deleteEngUpdate({{ $eu['id'] }})" wire:confirm="Delete this engineering update?" class="text-slate-300 hover:text-red-500 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Logistics Updates --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                        <svg class="h-4 w-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </span>
                    <h3 class="text-sm font-bold text-slate-700">{{ __('app.logistics_updates') }}</h3>
                    <span class="rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-bold text-orange-600">{{ count($logUpdates) }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ __('app.use_row_buttons') }}</p>
            </div>
            @if(empty($logUpdates))
                <div class="flex flex-col items-center justify-center px-5 py-10 text-center">
                    <svg class="h-9 w-9 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <p class="text-sm text-slate-400">{{ __('app.no_logistics_updates') }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach($logUpdates as $lu)
                        <div class="flex items-start justify-between gap-3 px-5 py-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-50">
                                    <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                                </span>
                                <div class="flex-1 min-w-0">
                                    @if($editingLogId === $lu['id'])
                                        <div class="flex items-center gap-2">
                                            <select wire:model="editingLogStatus" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-800 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                                                @foreach(\App\Enums\LogisticsStatusEnum::cases() as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button wire:click="updateLogStatus" class="rounded-md bg-emerald-600 px-2 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition">Save</button>
                                            <button wire:click="cancelEditLog" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition">Cancel</button>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($lu['item_desc'])
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200 truncate max-w-[160px]">{{ $lu['item_desc'] }}</span>
                                            @endif
                                            <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">{{ $lu['label'] }}</span>
                                            @if($lu['tracking'] !== '—')
                                                <span class="text-xs font-mono text-slate-500 bg-slate-50 rounded px-1.5 py-0.5">{{ $lu['tracking'] }}</span>
                                            @endif
                                            @if($lu['carrier'] !== '—')
                                                <span class="text-xs text-slate-400">{{ $lu['carrier'] }}</span>
                                            @endif
                                            <span class="text-xs text-slate-400">· {{ $lu['date'] }}</span>
                                        </div>
                                    @endif
                                    @if($lu['notes'])
                                        <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $lu['notes'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($editingLogId !== $lu['id'])
                                    <button wire:click="startEditLog({{ $lu['id'] }}, '{{ $lu['status'] }}')" class="text-slate-300 hover:text-orange-500 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H7v-3.414a2 2 0 01.586-1.414z"/></svg>
                                    </button>
                                @endif
                                <button wire:click="deleteLogUpdate({{ $lu['id'] }})" wire:confirm="Delete this logistics update?" class="text-slate-300 hover:text-red-500 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- STATUS HISTORY TABLE                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-bold text-slate-800">Status History</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ count($statusLogs) }} {{ count($statusLogs) === 1 ? 'change' : 'changes' }}
            </span>
        </div>
        @if(empty($statusLogs))
            <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                <svg class="h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-slate-400">No status changes recorded yet.</p>
                <p class="mt-1 text-xs text-slate-300">Use "Change Status" above to start tracking.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left">
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 w-12">#</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400">Previous</th>
                            <th class="px-3 py-3 w-6 text-center text-slate-300">→</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400">New Status</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400">Changed By</th>
                            <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-400 text-right">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($statusLogs as $i => $log)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 text-xs text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    @php $oc = $statusColors[$log['old']] ?? $statusColors['pending']; @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $oc['badge'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $oc['dot'] }}"></span>
                                        {{ \App\Enums\OrderStatusEnum::from($log['old'])->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center text-slate-300">→</td>
                                <td class="px-5 py-3.5">
                                    @php $nc = $statusColors[$log['new']] ?? $statusColors['pending']; @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $nc['badge'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $nc['dot'] }}"></span>
                                        {{ \App\Enums\OrderStatusEnum::from($log['new'])->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">{{ $log['user'] }}</td>
                                <td class="px-5 py-3.5 text-right text-xs text-slate-400 whitespace-nowrap">{{ $log['date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ADD ENGINEERING UPDATE MODAL                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($showEngModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(8px)">
        <div
            x-data
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
            style="box-shadow:0 25px 60px rgba(37,99,235,0.18),0 8px 24px rgba(0,0,0,0.12)"
        >
            {{-- Close button --}}
            <button wire:click="$set('showEngModal', false)"
                class="absolute top-4 end-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-slate-500 shadow-sm hover:bg-white hover:text-slate-800 transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Hero header --}}
            <div class="px-8 pt-8 pb-6 text-center" style="background:linear-gradient(135deg,#eff6ff,#dbeafe,#e0e7ff)">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl shadow-lg" style="background:linear-gradient(135deg,#2563eb,#4f46e5)">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-800">{{ __('app.add_engineering_update') }}</h3>
                @if($engOrderItemDesc)
                    <p class="mt-1.5 text-sm font-medium text-blue-600 leading-snug">{{ $engOrderItemDesc }}</p>
                @else
                    <p class="mt-1.5 text-sm text-slate-500">{{ __('app.eng_update_subtitle') }}</p>
                @endif
            </div>

            {{-- Form body --}}
            <div class="px-8 py-6 space-y-5">
                <div>
                    <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.update_type') }}</label>
                    <select wire:model="engStatus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                        @foreach(\App\Enums\EngineeringStatusEnum::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                        {{ __('app.note_optional') }} <span class="normal-case font-normal text-slate-400">({{ __('app.optional') }})</span>
                    </label>
                    <textarea wire:model="engNotes" rows="3" placeholder="{{ __('app.note_placeholder') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition resize-none leading-relaxed"></textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-8 pb-7 flex gap-3">
                <button wire:click="saveEngUpdate" wire:loading.attr="disabled"
                    class="flex-1 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md disabled:opacity-60 transition"
                    style="background:linear-gradient(135deg,#2563eb,#4f46e5);box-shadow:0 4px 15px rgba(37,99,235,0.35)"
                >
                    <span wire:loading.remove wire:target="saveEngUpdate">{{ __('app.save_update') }}</span>
                    <span wire:loading wire:target="saveEngUpdate">{{ __('app.saving') }}</span>
                </button>
                <button wire:click="$set('showEngModal', false)" class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">{{ __('app.cancel') }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ADD LOGISTICS UPDATE MODAL                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($showLogModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(8px)">
        <div
            x-data
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
            style="box-shadow:0 25px 60px rgba(234,88,12,0.15),0 8px 24px rgba(0,0,0,0.12)"
        >
            {{-- Close button --}}
            <button wire:click="$set('showLogModal', false)"
                class="absolute top-4 end-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-slate-500 shadow-sm hover:bg-white hover:text-slate-800 transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Hero header --}}
            <div class="px-8 pt-8 pb-6 text-center" style="background:linear-gradient(135deg,#fff7ed,#ffedd5,#fef3c7)">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl shadow-lg" style="background:linear-gradient(135deg,#ea580c,#f59e0b)">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-800">{{ __('app.add_logistics_update') }}</h3>
                @if($logOrderItemDesc)
                    <p class="mt-1.5 text-sm font-medium text-orange-600 leading-snug">{{ $logOrderItemDesc }}</p>
                @else
                    <p class="mt-1.5 text-sm text-slate-500">{{ __('app.log_update_subtitle') }}</p>
                @endif
            </div>

            {{-- Form body --}}
            <div class="px-8 py-6 space-y-5">
                <div>
                    <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.stage') }}</label>
                    <select wire:model="logStatus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100 transition">
                        @foreach(\App\Enums\LogisticsStatusEnum::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.carrier_supplier') }}</label>
                        <input wire:model="logCarrier" type="text" placeholder="{{ __('app.carrier_placeholder') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100 transition"/>
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.tracking_number') }}</label>
                        <input wire:model="logTracking" type="text" placeholder="{{ __('app.tracking_placeholder') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100 transition"/>
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                        {{ __('app.note_optional') }} <span class="normal-case font-normal text-slate-400">({{ __('app.optional') }})</span>
                    </label>
                    <textarea wire:model="logNotes" rows="3" placeholder="{{ __('app.log_note_placeholder') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100 transition resize-none leading-relaxed"></textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-8 pb-7 flex gap-3">
                <button wire:click="saveLogUpdate" wire:loading.attr="disabled"
                    class="flex-1 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md disabled:opacity-60 transition"
                    style="background:linear-gradient(135deg,#ea580c,#f59e0b);box-shadow:0 4px 15px rgba(234,88,12,0.35)"
                >
                    <span wire:loading.remove wire:target="saveLogUpdate">{{ __('app.save_update') }}</span>
                    <span wire:loading wire:target="saveLogUpdate">{{ __('app.saving') }}</span>
                </button>
                <button wire:click="$set('showLogModal', false)" class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">{{ __('app.cancel') }}</button>
            </div>
        </div>
    </div>
    @endif

    @endif

</div>
