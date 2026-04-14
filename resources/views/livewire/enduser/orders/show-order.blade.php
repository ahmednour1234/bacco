<div
    x-data="{
        toast: null,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
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
            'pending'    => ['dot' => 'bg-amber-400',  'text' => 'text-amber-700',  'bg' => 'bg-amber-50 border-amber-200'],
            'confirmed'  => ['dot' => 'bg-blue-500',   'text' => 'text-blue-700',   'bg' => 'bg-blue-50 border-blue-200'],
            'processing' => ['dot' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'bg' => 'bg-indigo-50 border-indigo-200'],
            'shipped'    => ['dot' => 'bg-violet-500', 'text' => 'text-violet-700', 'bg' => 'bg-violet-50 border-violet-200'],
            'delivered'  => ['dot' => 'bg-emerald-500','text' => 'text-emerald-700','bg' => 'bg-emerald-50 border-emerald-200'],
            'completed'  => ['dot' => 'bg-green-600',  'text' => 'text-green-700',  'bg' => 'bg-green-50 border-green-200'],
            'cancelled'  => ['dot' => 'bg-red-400',    'text' => 'text-red-600',    'bg' => 'bg-red-50 border-red-200'],
        ];
        $sv     = $order->status->value ?? 'pending';
        $sc     = $statusColors[$sv] ?? $statusColors['pending'];
    @endphp

    {{-- ───── Top action bar ──────────────────────────────────────────────────── --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.order_hash') }}{{ $order->order_no }}</h1>
            <nav class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                <a href="{{ route('enduser.dashboard') }}" class="hover:text-slate-600">{{ __('app.home') }}</a>
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('enduser.orders.index') }}" class="hover:text-slate-600">{{ __('app.orders') }}</a>
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-500 font-medium">{{ $order->order_no }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4H8v4a1 1 0 001 1zm1-10V4a1 1 0 00-1-1H9a1 1 0 00-1 1v3"/>
                </svg>
                {{ __('app.print_order') }}
            </button>
            <a
                href="{{ route('enduser.orders.show', ['uuid' => $order->uuid, 'export' => 'pdf']) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('app.export_pdf') }}
            </a>
        </div>
    </div>

    {{-- ───── Meta strip ──────────────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.project_name') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $order->quotationRequest?->project_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.client') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $order->client?->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.order_date') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $order->created_at->format('M d, Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.status') }}</p>
            <p class="mt-1 flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full {{ $sc['dot'] }}"></span>
                <span class="text-sm font-semibold {{ $sc['text'] }}">{{ $order->status->label() }}</span>
            </p>
        </div>
    </div>

    {{-- ───── BOQ Table ────────────────────────────────────────────────────────── --}}
    @php
        $logUpdates = $order->logisticsUpdates->sortByDesc('created_at');
        $engUpdates = $order->engineeringUpdates->sortByDesc('created_at');
        $latestLog  = $logUpdates->first();
        $latestEng  = $engUpdates->first();

        $logColorMap = [
            'pending'    => ['dot' => 'bg-amber-400',  'text' => 'text-amber-600'],
            'preparing'  => ['dot' => 'bg-blue-400',   'text' => 'text-blue-600'],
            'dispatched' => ['dot' => 'bg-indigo-400', 'text' => 'text-indigo-600'],
            'in_transit' => ['dot' => 'bg-violet-500', 'text' => 'text-violet-600'],
            'delivered'  => ['dot' => 'bg-emerald-500','text' => 'text-emerald-600'],
            'failed'     => ['dot' => 'bg-red-400',    'text' => 'text-red-600'],
        ];
        $engColorMap = [
            'pending'     => ['dot' => 'bg-amber-400',  'text' => 'text-amber-600'],
            'in_progress' => ['dot' => 'bg-blue-400',   'text' => 'text-blue-600'],
            'reviewing'   => ['dot' => 'bg-indigo-400', 'text' => 'text-indigo-600'],
            'approved'    => ['dot' => 'bg-green-500',  'text' => 'text-green-600'],
            'rejected'    => ['dot' => 'bg-red-400',    'text' => 'text-red-600'],
            'completed'   => ['dot' => 'bg-emerald-500','text' => 'text-emerald-600'],
        ];
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5">

        {{-- Card header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_full') }}</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold uppercase tracking-wide text-slate-500">
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
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 min-w-[220px]">{{ __('app.item_description') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-28">{{ __('app.quantity_unit') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-44">{{ __('app.logistics_timeline') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-44">{{ __('app.engineering_timeline') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-32">{{ __('app.brand') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-36 text-right">{{ __('app.price_sar') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors align-top">

                                {{-- Item Description --}}
                                <td class="px-5 py-5">
                                    <p class="font-semibold text-slate-800 leading-snug">{{ $item['description'] ?: '—' }}</p>
                                </td>

                                {{-- Quantity / Unit --}}
                                <td class="px-5 py-5">
                                    <p class="font-bold text-slate-800">{{ number_format((float)$item['quantity']) }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5 uppercase">{{ $item['unit'] }}</p>
                                </td>

                                {{-- Logistics Timeline --}}
                                <td class="px-5 py-5">
                                    @if($latestLog)
                                        @php $lc = $logColorMap[$latestLog->status->value] ?? ['dot' => 'bg-slate-300', 'text' => 'text-slate-500']; @endphp
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <span class="h-2 w-2 shrink-0 rounded-full {{ $lc['dot'] }}"></span>
                                            <span class="text-xs font-bold {{ $lc['text'] }}">{{ $latestLog->status->label() }}</span>
                                        </div>
                                        @foreach($logUpdates->skip(1)->take(3) as $lu)
                                            <div class="ml-3.5 mb-1">
                                                <p class="text-xs text-slate-400">{{ $lu->status->label() }}</p>
                                                <p class="text-xs text-slate-300">{{ $lu->created_at->format('M d, H:i') }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-slate-300">{{ __('app.no_updates_yet') }}</span>
                                    @endif
                                </td>

                                {{-- Engineering Timeline --}}
                                <td class="px-5 py-5">
                                    @if($latestEng)
                                        @php $ec = $engColorMap[$latestEng->status->value] ?? ['dot' => 'bg-slate-300', 'text' => 'text-slate-500']; @endphp
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <span class="h-2 w-2 shrink-0 rounded-full {{ $ec['dot'] }}"></span>
                                            <span class="text-xs font-bold {{ $ec['text'] }}">{{ $latestEng->status->label() }}</span>
                                        </div>
                                        @foreach($engUpdates->skip(1)->take(3) as $eu)
                                            <div class="ml-3.5 mb-1">
                                                <p class="text-xs text-slate-400">{{ $eu->status->label() }}</p>
                                                <p class="text-xs text-slate-300">{{ $eu->created_at->format('M d, H:i') }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-slate-300">{{ __('app.no_updates_yet') }}</span>
                                    @endif
                                </td>

                                {{-- Brand --}}
                                <td class="px-5 py-5 text-slate-600 text-xs">{{ $item['brand'] }}</td>

                                {{-- Price --}}
                                <td class="px-5 py-5 text-right font-mono font-bold text-slate-800">
                                    {{ number_format((float)$item['total_price'], 2) }}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ───── Totals ───────────────────────────────────────────────────────────── --}}
    <div class="flex justify-end">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="space-y-3">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>{{ __('app.order_subtotal') }}</span>
                    <span class="font-mono font-medium">{{ number_format((float)$order->total_amount, 2) }} {{ __('app.sar') }}</span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>{{ __('app.tax_vat_15') }}</span>
                    <span class="font-mono font-medium">{{ number_format((float)$order->vat_amount, 2) }} {{ __('app.sar') }}</span>
                </div>
                <div class="border-t-2 border-dashed border-slate-200 pt-3 flex justify-between items-baseline">
                    <span class="text-sm font-bold text-slate-700 uppercase tracking-wide">{{ __('app.total_amount_due') }}</span>
                    <div class="text-right">
                        <p class="font-mono text-2xl font-bold text-slate-900">{{ number_format((float)$order->grand_total, 2) }}</p>
                        <p class="text-xs text-slate-400">{{ __('app.sar') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif

</div>
