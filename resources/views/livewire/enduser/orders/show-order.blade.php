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
        $enduserStatus = $order->enduserStatus();
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
                <span class="h-2 w-2 rounded-full {{ $enduserStatus->dotClass() }}"></span>
                <span class="text-sm font-semibold {{ $enduserStatus->textClass() }}">{{ $enduserStatus->label() }}</span>
            </p>
        </div>
    </div>

    {{-- ───── Delivery Address ─────────────────────────────────────────────────── --}}
    @if($order->delivery_address_type)
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2.5 border-b border-slate-100 px-6 py-4">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.delivery_address') }}</h2>
            <span class="ms-auto rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                {{ $order->delivery_address_type === 'national' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $order->delivery_address_type === 'national' ? __('app.address_type_national') : __('app.address_type_detailed') }}
            </span>
        </div>
        <div class="px-6 py-4">
            @if($order->delivery_address_type === 'national')
            <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-3">
                @if($order->delivery_building_no)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_building_no') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_building_no }}</p>
                </div>
                @endif
                @if($order->delivery_street)
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_street') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_street }}</p>
                </div>
                @endif
                @if($order->delivery_district)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_district') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_district }}</p>
                </div>
                @endif
                @if($order->delivery_city)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_city') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_city }}</p>
                </div>
                @endif
                @if($order->delivery_postal_code)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_postal_code') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_postal_code }}</p>
                </div>
                @endif
                @if($order->delivery_additional_no)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.national_additional_no') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_additional_no }}</p>
                </div>
                @endif
            </div>
            @else
            <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-3">
                @if($order->delivery_street)
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_street') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_street }}</p>
                </div>
                @endif
                @if($order->delivery_district)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_district') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_district }}</p>
                </div>
                @endif
                @if($order->delivery_city)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_city') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_city }}</p>
                </div>
                @endif
                @if($order->delivery_region)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_region') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_region }}</p>
                </div>
                @endif
                @if($order->delivery_postal_code)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.address_postal_code') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $order->delivery_postal_code }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

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

    {{-- ───── Bank Transfer Payment Section ───────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3" style="background:#fafbfc;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">{{ app()->getLocale() === 'ar' ? 'الدفع بالتحويل البنكي' : 'Bank Transfer Payment' }}</h2>
                <p class="text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? 'حوّل المبلغ وارفع الإيصال لتأكيد طلبك' : 'Transfer the amount and upload your receipt to confirm your order' }}</p>
            </div>
        </div>
        <div class="p-6">
            <livewire:enduser.payments.submit-payment :order="$order" />
        </div>
    </div>

</div>
