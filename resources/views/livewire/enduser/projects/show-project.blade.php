<div class="space-y-6">

    {{-- Project Header --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </span>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $project->name }}</h2>
                <p class="text-xs text-slate-400">{{ $project->project_no }}</p>
            </div>
            <div class="ml-auto flex items-center gap-3">
                @php
                    $pStatusBadge = match($project->status->value ?? 'pending') {
                        'active'     => 'bg-emerald-100 text-emerald-700',
                        'completed'  => 'bg-blue-100 text-blue-700',
                        'on_hold'    => 'bg-amber-100 text-amber-700',
                        'cancelled'  => 'bg-red-100 text-red-700',
                        default      => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $pStatusBadge }}">
                    {{ $project->status->label() }}
                </span>
            </div>
        </div>

        @if($project->description)
            <div class="px-6 py-4 text-sm text-slate-600">
                {{ $project->description }}
            </div>
        @endif

        <div class="flex items-center gap-6 border-t border-slate-100 px-6 py-3 text-xs text-slate-400">
            <span>{{ __('app.created') }} {{ $project->created_at->format('M d, Y') }}</span>
            @if($project->start_date)
                <span>{{ __('app.start_colon') }} {{ $project->start_date->format('M d, Y') }}</span>
            @endif
            @if($project->expected_end_date)
                <span>{{ __('app.expected_end') }} {{ $project->expected_end_date->format('M d, Y') }}</span>
            @endif
        </div>
    </div>

    {{-- BOQs Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq') }} ({{ $boqs->count() }})</h2>
            <a href="{{ route('enduser.boqs.create.project', $project->uuid) }}"
                class="ml-auto inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.new_boq') }}
            </a>
        </div>

        <div class="p-6">
            @if($boqs->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center text-sm text-slate-400">
                    {{ __('app.no_boqs_project') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($boqs as $boq)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $boq->boq_no }}</span>
                                    @php
                                        $bStatusBadge = match($boq->status->value ?? 'draft') {
                                            'submitted'  => 'bg-blue-100 text-blue-700',
                                            'completed'  => 'bg-emerald-100 text-emerald-700',
                                            'cancelled'  => 'bg-red-100 text-red-700',
                                            default      => 'bg-amber-100 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $bStatusBadge }}">
                                        {{ $boq->status->label() }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $boq->items_count }} {{ __('app.items') }} &middot; {{ $boq->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('enduser.boqs.show', $boq->uuid) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                {{ __('app.view_arrow') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quotations Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.quotations') }} ({{ $quotations->count() }})</h2>
        </div>

        <div class="p-6">
            @if($quotations->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center text-sm text-slate-400">
                    {{ __('app.no_quotations_project') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($quotations as $quotation)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $quotation->quotation_no }}</span>
                                    @php
                                        $qStatusBadge = match($quotation->status->value ?? 'draft') {
                                            'submitted','in_review','quoted' => 'bg-blue-100 text-blue-700',
                                            'accepted'    => 'bg-emerald-100 text-emerald-700',
                                            'rejected'    => 'bg-red-100 text-red-700',
                                            'cancelled'   => 'bg-red-100 text-red-700',
                                            'tender'      => 'bg-purple-100 text-purple-700',
                                            default       => 'bg-amber-100 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $qStatusBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $quotation->status->value ?? 'draft')) }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $quotation->items->count() }} {{ __('app.items') }} &middot; {{ $quotation->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('enduser.quotations.show', $quotation->uuid) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                {{ __('app.view_arrow') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Orders Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </span>
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.orders') }} ({{ $orders->count() }})</h2>
        </div>

        <div class="p-6">
            @if($orders->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center text-sm text-slate-400">
                    {{ __('app.no_orders_project') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $order->order_no }}</span>
                                    @php
                                        $oStatusBadge = match($order->status->value ?? 'open') {
                                            'open'   => 'bg-emerald-100 text-emerald-700',
                                            'closed' => 'bg-slate-100 text-slate-600',
                                            default  => 'bg-amber-100 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $oStatusBadge }}">
                                        {{ $order->status?->label() ?? ucfirst($order->status->value ?? 'open') }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ number_format($order->grand_total, 2) }} {{ __('app.sar') }} &middot; {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('enduser.orders.show', $order->uuid) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                {{ __('app.view_arrow') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
