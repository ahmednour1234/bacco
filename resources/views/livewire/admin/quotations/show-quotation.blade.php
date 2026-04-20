<div>

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
                        @endphp
                        <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                        <span class="text-sm font-semibold text-slate-700">{{ $quotation->status->label() }}</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.submitted_date') }}</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">
                        {{ $quotation->created_at?->format('M j, Y') ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.project_type') }}</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">{{ $quotation->project_type ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.quotation_id') }}</dt>
                    <dd class="mt-0.5 text-sm font-mono text-slate-700">{{ $quotation->quotation_no ?? '#' . $quotation->id }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.client') }}</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">
                        {{ $quotation->client?->clientProfile?->full_name ?? $quotation->client?->name ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.company') }}</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">
                        {{ $quotation->client?->clientProfile?->company_name ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Action buttons --}}
        <div class="flex shrink-0 items-center gap-3">
            <a
                href="{{ route('admin.quotations.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('app.back') }}
            </a>
            <a
                href="{{ route('admin.quotations.pdf', $quotation->uuid) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('app.export_pdf') }}
            </a>
        </div>
    </div>

    {{-- ───── BOQ Table card ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_full') }}</h2>
            <span class="text-xs text-slate-400">{{ __('app.read_only_view') }}</span>
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
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-14">{{ __('app.selected') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.unit') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.brand') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-10">{{ __('app.src') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.price_sar') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-44">{{ __('app.selected_product') }}</th>
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
                                @endphp

                                <tr class="transition-colors hover:bg-slate-50/60 @if($statusVal === 'rejected') opacity-50 @endif @if($item['selected'] ?? false) bg-emerald-50/40 @endif">
                                    <td class="px-3 py-3 text-center">
                                        @if($item['selected'] ?? false)
                                            <span title="Selected by client" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
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
                                        @if(is_numeric($item['unit_price'] ?? null))
                                            {{ number_format((float)$item['unit_price'] * (float)($item['quantity'] ?? 0), 2) }}
                                        @else
                                            <span class="text-xs italic text-slate-400">{{ __('app.not_priced') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($item['product_name']))
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $item['product_name'] }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-300">{{ __('app.not_selected') }}</span>
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

    {{-- ───── Financial Summary ─────────────────────────────────────────────── --}}
    @php
        $taxRate       = 0.15;
        $selectedItems = collect($items)->filter(fn($i) => ($i['selected'] ?? false));
        $subtotal      = $selectedItems
            ->filter(fn($i) => ($i['status'] ?? '') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
            ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));
        $tax           = $subtotal * $taxRate;
        $total         = $subtotal + $tax;
        $selectedCount = $selectedItems->count();
    @endphp

    <div class="mt-6 flex justify-end">
        <div class="w-full max-w-sm">
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
        </div>
    </div>

    @endif

</div>
