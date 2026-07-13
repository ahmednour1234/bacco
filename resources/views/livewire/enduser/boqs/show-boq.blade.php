<div
    x-data="{
        toast: null,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
    {!! $pricesFetching ? 'wire:poll.5000ms="pollPriceStatus"' : '' !!}
>

    {{-- Toast notification --}}
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

    <div class="space-y-6">

        {{-- Step Progress Indicator --}}
        @if($currentStep < 4)
        <div class="flex items-center gap-0">
            @php
                $steps = [
                    1 => app()->getLocale()==='ar' ? 'مراجعة العناصر'  : 'Review Items',
                    2 => app()->getLocale()==='ar' ? 'إنشاء عرض السعر' : 'Create Quotation',
                    3 => app()->getLocale()==='ar' ? 'العنوان والدفع'  : 'Address & Payment',
                ];
            @endphp
            @foreach($steps as $num => $label)
                <div class="flex items-center @if(!$loop->last) flex-1 @endif">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition
                            {{ $currentStep > $num ? 'bg-emerald-500 text-white' : ($currentStep === $num ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-400') }}">
                            @if($currentStep > $num)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-xs font-medium {{ $currentStep === $num ? 'text-emerald-700' : ($currentStep > $num ? 'text-emerald-500' : 'text-slate-400') }}">{{ $label }}</span>
                    </div>
                    @if(!$loop->last)
                        <div class="mx-3 flex-1 h-px {{ $currentStep > $num ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        {{-- Project & BOQ Header --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_colon') }} {{ $boq->boq_no }}</h2>
                    <p class="text-xs text-slate-400">{{ __('app.project_colon') }} {{ $boq->project?->name ?? '—' }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    @php
                        $statusBadge = match($boq->status->value ?? 'draft') {
                            'submitted'  => 'bg-blue-100 text-blue-700',
                            'completed'  => 'bg-emerald-100 text-emerald-700',
                            'cancelled'  => 'bg-red-100 text-red-700',
                            default      => 'bg-amber-100 text-amber-700',
                        };

                        $typeColors = [
                            'tender'  => 'bg-blue-100 text-blue-700',
                            'awarded' => 'bg-emerald-100 text-emerald-700',
                        ];
                        $typeColor = $typeColors[$boq->type->value ?? ''] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                        {{ $boq->status->label() }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeColor }}">
                        {{ $boq->type?->label() ?? '—' }}
                    </span>
                </div>
            </div>

            @if($boq->project?->description)
                <div class="px-6 py-4 text-sm text-slate-600">
                    <span class="font-medium text-slate-700">{{ __('app.project_description') }}</span>
                    {{ $boq->project->description }}
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════
             STEP 1 – مراجعة العناصر واختيارها
        ═══════════════════════════════════════ --}}
        @if($currentStep === 1)

        {{-- BOQ Items Table with selection --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">{{ __('app.boq_items') }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.select_items_quotation') }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" wire:click="reparseBoq"
                        wire:loading.attr="disabled"
                        wire:target="reparseBoq"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-violet-200 bg-violet-50 px-3.5 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-100 disabled:opacity-60">
                        <svg wire:loading.remove wire:target="reparseBoq" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582M20 20v-5h-.581M5.635 19A9 9 0 104.582 9H4"/>
                        </svg>
                        <svg wire:loading wire:target="reparseBoq" class="h-3.5 w-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582M20 20v-5h-.581M5.635 19A9 9 0 104.582 9H4"/>
                        </svg>
                        <span wire:loading.remove wire:target="reparseBoq">{{ __('app.reparse_boq') }}</span>
                        <span wire:loading wire:target="reparseBoq">{{ __('app.processing') }}…</span>
                    </button>
                    <button type="button" wire:click="selectAll"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        {{ __('app.select_all') }}
                    </button>
                    <button type="button" wire:click="deselectAll"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                        {{ __('app.deselect_all') }}
                    </button>
                    <button type="button" wire:click="approveAll"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.approve_all') }}
                    </button>
                </div>
            </div>

            <div class="p-6">
                @if(empty($items))
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                        {{ __('app.no_items_boq') }}
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-12">{{ __('app.select') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.unit') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.brand') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $index => $item)
                                    <tr class="group transition-colors @if($item['selected'] ?? false) bg-emerald-50/40 @else hover:bg-slate-50/60 @endif
                                        @if(($item['status'] ?? '') === 'rejected') opacity-60 @endif">

                                        <td class="px-4 py-2.5 text-center">
                                            <input type="checkbox"
                                                @checked($item['selected'] ?? false)
                                                wire:click="toggleSelected({{ $item['id'] }})"
                                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                        </td>

                                        <td class="px-4 py-2.5 text-sm text-slate-700 font-medium">
                                            <input
                                                type="text"
                                                wire:model="items.{{ $index }}.description"
                                                wire:blur="updateItem({{ $index }}, 'description')"
                                                placeholder="{{ __('app.enter_description') }}"
                                                class="w-full min-w-[180px] rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 font-medium placeholder-slate-300 transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-400 hover:border-slate-300"
                                            >
                                        </td>
                                        <td class="px-4 py-2.5 text-sm text-slate-600">
                                            <input
                                                type="number"
                                                min="0"
                                                step="any"
                                                wire:model="items.{{ $index }}.quantity"
                                                wire:blur="updateItem({{ $index }}, 'quantity')"
                                                class="w-20 rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-600 transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-400 hover:border-slate-300"
                                            >
                                        </td>
                                        <td class="px-4 py-2.5 text-sm text-slate-500">{{ $item['unit'] ?? '—' }}</td>
                                        <td class="px-4 py-2.5 text-sm text-slate-500">{{ $item['category'] ?? '—' }}</td>
                                        <td class="px-4 py-2.5 text-sm text-slate-500">{{ $item['brand'] ?? '—' }}</td>

                                        <td class="px-4 py-2.5">
                                            @php
                                                $statusVal = $item['status'] ?? 'pending';
                                                $badgeClass = match($statusVal) {
                                                    'sourcing' => 'bg-emerald-100 text-emerald-700',
                                                    'sourced'  => 'bg-blue-100  text-blue-700',
                                                    'rejected' => 'bg-red-100   text-red-700',
                                                    default    => 'bg-amber-100 text-amber-700',
                                                };
                                                $badgeLabel = match($statusVal) {
                                                    'sourcing' => __('app.status_confirmed'),
                                                    'sourced'  => __('app.status_sourced'),
                                                    'rejected' => __('app.status_rejected'),
                                                    default    => __('app.status_pending'),
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                {{ $badgeLabel }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2.5 text-center">
                                            <button
                                                type="button"
                                                wire:click="toggleEngineering({{ $item['id'] }})"
                                                title="{{ __('app.toggle_engineering') }}"
                                                class="inline-flex items-center justify-center h-7 w-7 rounded-lg border transition
                                                    @if(!empty($item['engineering_required']))
                                                        border-emerald-300 bg-emerald-100 hover:bg-emerald-200
                                                    @else
                                                        border-slate-200 bg-slate-50 hover:bg-slate-100
                                                    @endif"
                                            >
                                                @if(!empty($item['engineering_required']))
                                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @else
                                                    <span class="h-3 w-3"></span>
                                                @endif
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Action bar --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-6">
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.total_items') }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($items) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.selected') }}</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $selectedCount }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('enduser.projects.show', $boq->project?->uuid ?? '') }}"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        &larr; {{ __('app.back_to_project') }}
                    </a>

                    <button
                        type="button"
                        wire:click="confirmItems"
                        wire:loading.attr="disabled"
                        @if($selectedCount === 0) disabled @endif
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="confirmItems" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ app()->getLocale()==='ar' ? 'التالي: إنشاء عرض السعر' : 'Next: Create Quotation' }} ({{ $selectedCount }} {{ __('app.items') }}) &rarr;
                    </button>
                </div>
            </div>
        </div>

        @endif {{-- end step 1 --}}

        {{-- ═══════════════════════════════════════
             STEP 2 – جلب الأسعار
        ═══════════════════════════════════════ --}}
        @if($currentStep === 2)
        <div class="space-y-6">

            {{-- Fetching banner --}}
            @if($pricesFetching)
            <div class="flex items-center gap-4 rounded-2xl border border-blue-200 bg-blue-50 px-6 py-5">
                <svg class="h-8 w-8 animate-spin flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div>
                    <h3 class="text-base font-bold text-blue-800">{{ app()->getLocale()==='ar' ? 'جاري إنشاء عرض السعر...' : 'Creating quotation...' }}</h3>
                    <p class="mt-0.5 text-sm text-blue-600">{{ app()->getLocale()==='ar' ? 'هذا قد يستغرق بضع دقائق. يمكنك الانتظار وستُحدَّث الصفحة تلقائياً.' : 'This may take a few minutes. The page will update automatically.' }}</p>
                </div>
            </div>
            @else
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-emerald-800">{{ app()->getLocale()==='ar' ? '✓ تم إنشاء عرض السعر' : '✓ Quotation Created' }}</h3>
                    <p class="mt-1 text-sm text-emerald-600">
                        {{ $pricedCount }} {{ app()->getLocale()==='ar' ? 'عنصر بسعر' : 'priced' }} · {{ $unpricedCount }} {{ app()->getLocale()==='ar' ? 'بدون سعر' : 'without price' }}
                    </p>
                </div>
                <div class="text-end">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ app()->getLocale()==='ar' ? 'المجموع التقديري' : 'Estimated Total' }}</p>
                    <p class="text-2xl font-bold text-emerald-800">{{ number_format($quotationTotal, 2) }} <span class="text-base font-medium">SAR</span></p>
                    <p class="text-xs text-emerald-500 mt-0.5">{{ app()->getLocale()==='ar' ? '+ ضريبة القيمة المضافة 15%' : '+ 15% VAT' }}</p>
                </div>
            </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <h2 class="text-sm font-semibold text-slate-800">{{ app()->getLocale()==='ar' ? 'تفاصيل عرض السعر' : 'Quotation Details' }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[220px]">{{ __('app.description') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.unit') }}</th>
                            <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ app()->getLocale()==='ar' ? 'سعر الوحدة' : 'Unit Price' }}</th>
                            <th class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ app()->getLocale()==='ar' ? 'الإجمالي' : 'Total' }}</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pricedItems as $pi)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 text-slate-700">{{ $pi['description'] }}</td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ number_format($pi['quantity'], 2) }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ $pi['unit'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-end font-medium {{ $pi['unit_price'] ? 'text-slate-800' : 'text-red-400' }}">
                                        {{ $pi['unit_price'] ? number_format($pi['unit_price'], 2).' SAR' : (app()->getLocale()==='ar'?'لم يُسعَّر':'Not priced') }}
                                    </td>
                                    <td class="px-4 py-3 text-end font-semibold text-slate-800">{{ $pi['line_total'] > 0 ? number_format($pi['line_total'], 2).' SAR' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50"><td colspan="4" class="px-4 py-3 text-end text-sm font-semibold text-slate-600">{{ app()->getLocale()==='ar'?'المجموع':'Subtotal' }}</td><td class="px-4 py-3 text-end font-bold text-slate-800">{{ number_format($quotationTotal, 2) }} SAR</td></tr>
                            <tr class="bg-slate-50"><td colspan="4" class="px-4 py-2 text-end text-xs text-slate-500">{{ app()->getLocale()==='ar'?'ضريبة القيمة المضافة (15%)':'VAT (15%)' }}</td><td class="px-4 py-2 text-end text-sm text-slate-600">{{ number_format($quotationTotal * 0.15, 2) }} SAR</td></tr>
                            <tr class="bg-emerald-50"><td colspan="4" class="px-4 py-3 text-end text-sm font-bold text-emerald-700">{{ app()->getLocale()==='ar'?'الإجمالي شامل الضريبة':'Grand Total incl. VAT' }}</td><td class="px-4 py-3 text-end text-lg font-bold text-emerald-700">{{ number_format($quotationTotal * 1.15, 2) }} SAR</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($unpricedCount > 0)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ __('app.unpriced_items_block_checkout') }} ({{ $unpricedCount }})
            </div>
            @endif

            <div class="flex justify-end">
                <button type="button" wire:click="proceedToAddress" @if($pricesFetching || $unpricedCount > 0) disabled @endif
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    @if($pricesFetching)
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ app()->getLocale()==='ar' ? 'جاري إنشاء عرض السعر...' : 'Creating quotation...' }}
                    @else
                        {{ app()->getLocale()==='ar' ? 'التالي: العنوان والدفع' : 'Next: Address & Payment' }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </button>
            </div>
        </div>
        @endif {{-- end step 2 --}}

        {{-- ═══════════════════════════════════════
             STEP 3 – العنوان والدفع
        ═══════════════════════════════════════ --}}
        @if($currentStep === 3)
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Left: Address + Payment --}}
                <div class="lg:col-span-2 space-y-6">

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800">{{ app()->getLocale()==='ar' ? 'عنوان التوصيل' : 'Delivery Address' }}</h2>
                                <p class="text-xs text-slate-400">{{ app()->getLocale()==='ar' ? 'اختر كيف تريد إدخال عنوان التوصيل' : 'Choose how to enter the delivery address' }}</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-5">

                            {{-- Radio cards --}}
                            <div class="grid grid-cols-2 gap-4">

                                {{-- National Address --}}
                                <label class="relative flex flex-col items-center text-center cursor-pointer gap-3 rounded-2xl border-2 px-4 py-5 transition {{ $deliveryAddressMode === 'national' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:border-emerald-200 hover:bg-slate-50 bg-white' }}">
                                    <input type="radio" wire:model.live="deliveryAddressMode" value="national" class="sr-only">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $deliveryAddressMode === 'national' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </span>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold {{ $deliveryAddressMode === 'national' ? 'text-emerald-800' : 'text-slate-700' }}">{{ app()->getLocale()==='ar' ? 'العنوان الوطني' : 'National Address' }}</p>
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold tracking-wide text-emerald-700">{{ app()->getLocale()==='ar' ? 'موصى به' : 'Recommended' }}</span>
                                        <p class="text-[11px] leading-relaxed {{ $deliveryAddressMode === 'national' ? 'text-emerald-600' : 'text-slate-400' }}">{{ app()->getLocale()==='ar' ? 'رمز وصل المكون من 8 خانات' : '8-char Saudi Post code' }}</p>
                                    </div>
                                    @if($deliveryAddressMode === 'national')
                                        <svg class="absolute top-3 end-3 h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </label>

                                {{-- Detailed Address --}}
                                <label class="relative flex flex-col items-center text-center cursor-pointer gap-3 rounded-2xl border-2 px-4 py-5 transition {{ $deliveryAddressMode === 'detailed' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:border-emerald-200 hover:bg-slate-50 bg-white' }}">
                                    <input type="radio" wire:model.live="deliveryAddressMode" value="detailed" class="sr-only">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $deliveryAddressMode === 'detailed' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </span>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold {{ $deliveryAddressMode === 'detailed' ? 'text-emerald-800' : 'text-slate-700' }}">{{ app()->getLocale()==='ar' ? 'عنوان تفصيلي' : 'Detailed Address' }}</p>
                                        <p class="text-[11px] leading-relaxed {{ $deliveryAddressMode === 'detailed' ? 'text-emerald-600' : 'text-slate-400' }}">{{ app()->getLocale()==='ar' ? 'شارع، حي، مدينة، منطقة' : 'Street, district, city' }}</p>
                                    </div>
                                    @if($deliveryAddressMode === 'detailed')
                                        <svg class="absolute top-3 end-3 h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </label>

                            </div>

                            {{-- National Address Form --}}
                            @if($deliveryAddressMode === 'national')
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 space-y-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                    {{ app()->getLocale()==='ar' ? 'العنوان الوطني' : 'National Address' }}
                                </span>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'رمز العنوان الوطني' : 'National Address Code' }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="deliveryShortAddress"
                                        placeholder="RJHH6392" maxlength="8"
                                        class="h-12 w-full rounded-xl border bg-white px-4 font-mono text-base uppercase tracking-widest text-slate-700 shadow-sm outline-none transition placeholder:normal-case placeholder:text-slate-400 @error('deliveryShortAddress') border-red-400 ring-2 ring-red-100 @else border-emerald-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                    <p class="mt-1.5 text-xs text-slate-400">{{ app()->getLocale()==='ar' ? '8 خانات من الحروف والأرقام الإنجليزية — من wasel.com.sa' : '8 alphanumeric chars (e.g. RJHH6392) from wasel.com.sa' }}</p>
                                    @error('deliveryShortAddress')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            @endif

                            {{-- Detailed Address Form --}}
                            @if($deliveryAddressMode === 'detailed')
                            <div class="space-y-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ app()->getLocale()==='ar' ? 'أدخل تفاصيل العنوان' : 'Enter Address Details' }}
                                </span>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'الشارع' : 'Street' }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="deliveryStreet"
                                        placeholder="{{ app()->getLocale()==='ar' ? 'مثال: طريق الملك فهد' : 'e.g. King Fahd Road' }}"
                                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryStreet') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                    @error('deliveryStreet')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'الحي' : 'District' }} <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.blur="deliveryDistrict"
                                            placeholder="{{ app()->getLocale()==='ar' ? 'مثال: العليا' : 'e.g. Al Olaya' }}"
                                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryDistrict') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                        @error('deliveryDistrict')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'المدينة' : 'City' }} <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.blur="deliveryCity"
                                            placeholder="{{ app()->getLocale()==='ar' ? 'مثال: الرياض' : 'e.g. Riyadh' }}"
                                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryCity') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                        @error('deliveryCity')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'المنطقة' : 'Region' }} <span class="text-red-500">*</span></label>
                                        <select wire:model.live="deliveryRegion"
                                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryRegion') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                            <option value="">{{ app()->getLocale()==='ar' ? 'اختر المنطقة' : 'Select Region' }}</option>
                                            @foreach(['الرياض','مكة المكرمة','المدينة المنورة','القصيم','المنطقة الشرقية','عسير','تبوك','حائل','الحدود الشمالية','جازان','نجران','الباحة','الجوف'] as $region)
                                                <option value="{{ $region }}" @selected($deliveryRegion === $region)>{{ $region }}</option>
                                            @endforeach
                                        </select>
                                        @error('deliveryRegion')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale()==='ar' ? 'الرمز البريدي' : 'Postal Code' }} <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.blur="deliveryPostalCode"
                                            placeholder="12345" maxlength="5" inputmode="numeric"
                                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryPostalCode') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                        @error('deliveryPostalCode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </span>
                            <h2 class="text-sm font-semibold text-slate-800">{{ app()->getLocale()==='ar' ? 'طريقة الدفع' : 'Payment Method' }}</h2>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach([
                                ['value'=>'bank_transfer','ar'=>'تحويل بنكي','en'=>'Bank Transfer','icon'=>'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                                ['value'=>'cash',         'ar'=>'نقد',       'en'=>'Cash',         'icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                                ['value'=>'credit',       'ar'=>'ائتمان',    'en'=>'Credit',       'icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ] as $pm)
                                <label class="flex items-center gap-3 rounded-xl border-2 p-4 cursor-pointer transition {{ $paymentMethod===$pm['value']?'border-emerald-500 bg-emerald-50':'border-slate-200 hover:border-slate-300' }}">
                                    <input type="radio" wire:model.live="paymentMethod" value="{{ $pm['value'] }}" class="sr-only">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $paymentMethod===$pm['value']?'bg-emerald-500 text-white':'bg-slate-100 text-slate-500' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $pm['icon'] }}"/></svg>
                                    </span>
                                    <span class="text-sm font-semibold {{ $paymentMethod===$pm['value']?'text-emerald-700':'text-slate-600' }}">{{ app()->getLocale()==='ar'?$pm['ar']:$pm['en'] }}</span>
                                    @if($paymentMethod===$pm['value'])<svg class="ms-auto h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>@endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right: Order Summary sidebar --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                            <h3 class="text-sm font-bold text-slate-800">{{ app()->getLocale()==='ar'?'ملخص الطلب':'Order Summary' }}</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'المشروع':'Project' }}</span><span class="font-medium text-slate-700 max-w-[130px] truncate">{{ $boq->project?->name ?? '—' }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'عدد العناصر':'Items' }}</span><span class="font-medium text-slate-700">{{ count($pricedItems) }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'المجموع':'Subtotal' }}</span><span class="font-medium text-slate-700">{{ number_format($quotationTotal, 2) }} SAR</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'ضريبة (15%)':'VAT (15%)' }}</span><span class="font-medium text-slate-700">{{ number_format($quotationTotal * 0.15, 2) }} SAR</span></div>
                            <div class="border-t border-slate-100 pt-3 flex justify-between"><span class="font-bold text-slate-800">{{ app()->getLocale()==='ar'?'الإجمالي':'Grand Total' }}</span><span class="font-bold text-emerald-600 text-lg">{{ number_format($quotationTotal * 1.15, 2) }} SAR</span></div>
                        </div>
                        <div class="border-t border-slate-100 px-6 py-5 space-y-3">
                            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" @if($unpricedCount > 0) disabled @endif
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95 disabled:opacity-60">
                                <svg wire:loading wire:target="placeOrder" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <svg wire:loading.remove wire:target="placeOrder" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span wire:loading.remove wire:target="placeOrder">{{ app()->getLocale()==='ar'?'تأكيد الطلب':'Place Order' }}</span>
                                <span wire:loading wire:target="placeOrder">{{ app()->getLocale()==='ar'?'جاري الإنشاء...':'Placing...' }}</span>
                            </button>
                            <button type="button" wire:click="goBack" class="w-full text-center text-xs text-slate-400 hover:text-slate-600 py-1">
                                {{ app()->getLocale()==='ar'?'← العودة لمراجعة الأسعار':'← Back to price review' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif {{-- end step 3 --}}

        {{-- ═══════════════════════════════════════
             STEP 4 – تأكيد الطلب (Success)
        ═══════════════════════════════════════ --}}
        @if($currentStep === 4)
        <div class="flex flex-col items-center py-12 text-center space-y-8">

            <div class="relative flex h-28 w-28 items-center justify-center">
                <div class="absolute inset-0 animate-ping rounded-full bg-emerald-100 opacity-60"></div>
                <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-emerald-500 shadow-xl shadow-emerald-200">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ app()->getLocale()==='ar'?'تم إنشاء الطلب بنجاح! 🎉':'Order Placed Successfully! 🎉' }}</h1>
                <p class="mt-2 text-sm text-slate-500 max-w-md">{{ app()->getLocale()==='ar'?'تم استلام طلبك وسيتم مراجعته من قِبل فريقنا في أقرب وقت ممكن.':'Your order has been received and will be reviewed by our team shortly.' }}</p>
            </div>

            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-sm text-start overflow-hidden">
                <div class="bg-emerald-600 px-6 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">{{ app()->getLocale()==='ar'?'رقم الطلب':'Order Number' }}</p>
                    <p class="mt-1 text-xl font-bold text-white">{{ $orderNo }}</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'المشروع':'Project' }}</span><span class="text-sm font-semibold text-slate-700">{{ $boq->project?->name ?? '—' }}</span></div>
                    <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'عدد العناصر':'Items' }}</span><span class="text-sm font-semibold text-slate-700">{{ count($pricedItems) }}</span></div>
                    <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'طريقة الدفع':'Payment' }}</span><span class="text-sm font-semibold text-slate-700">{{ match($paymentMethod){'bank_transfer'=>app()->getLocale()==='ar'?'تحويل بنكي':'Bank Transfer','cash'=>app()->getLocale()==='ar'?'نقد':'Cash',default=>app()->getLocale()==='ar'?'ائتمان':'Credit'} }}</span></div>
                    <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'عنوان التوصيل':'Delivery' }}</span><span class="text-sm font-semibold text-slate-700 max-w-[180px] text-end">{{ implode(', ', array_filter([$deliveryStreet, $deliveryDistrict, $deliveryCity])) }}</span></div>
                    <div class="flex justify-between bg-emerald-50 px-6 py-4"><span class="text-sm font-bold text-emerald-700">{{ app()->getLocale()==='ar'?'الإجمالي الكلي':'Grand Total' }}</span><span class="text-lg font-bold text-emerald-700">{{ number_format($orderGrandTotal, 2) }} SAR</span></div>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-start max-w-md">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                <p class="text-sm text-amber-700">{{ app()->getLocale()==='ar'?'سيتواصل معك فريق المبيعات لتأكيد الطلب وتفاصيل الدفع والتوصيل.':'Our sales team will contact you to confirm the order, payment, and delivery details.' }}</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                @if($orderUuid)
                    <a href="{{ route('enduser.orders.show', $orderUuid) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ app()->getLocale()==='ar'?'عرض الطلب':'View Order' }}
                    </a>
                @endif
                <a href="{{ route('enduser.boqs.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    {{ app()->getLocale()==='ar'?'العودة لجداول الكميات':'Back to BOQs' }}
                </a>
            </div>
        </div>
        @endif {{-- end step 4 --}}

    </div>
</div>
