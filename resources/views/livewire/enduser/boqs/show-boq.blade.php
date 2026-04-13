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
                    <h2 class="text-sm font-semibold text-slate-800">BOQ: {{ $boq->boq_no }}</h2>
                    <p class="text-xs text-slate-400">Project: {{ $boq->project?->name ?? '—' }}</p>
                </div>
                <div class="ml-auto">
                    @php
                        $statusBadge = match($boq->status->value ?? 'draft') {
                            'submitted'  => 'bg-blue-100 text-blue-700',
                            'completed'  => 'bg-emerald-100 text-emerald-700',
                            'cancelled'  => 'bg-red-100 text-red-700',
                            default      => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                        {{ $boq->status->label() }}
                    </span>
                </div>
            </div>

            @if($boq->project?->description)
                <div class="px-6 py-4 text-sm text-slate-600">
                    <span class="font-medium text-slate-700">Project Description:</span>
                    {{ $boq->project->description }}
                </div>
            @endif
        </div>

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
                    <h2 class="text-sm font-semibold text-slate-800">BOQ Items</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Select items to include in a quotation, then click "Create Quotation".</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" wire:click="selectAll"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Select All
                    </button>
                    <button type="button" wire:click="deselectAll"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                        Deselect All
                    </button>
                </div>
            </div>

            <div class="p-6">
                @if(empty($items))
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                        No items in this BOQ.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-12">Select</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">QTY</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">Brand</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">Engineering</th>
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

                                        <td class="px-4 py-2.5 text-sm text-slate-700 font-medium">{{ $item['description'] ?? '—' }}</td>
                                        <td class="px-4 py-2.5 text-sm text-slate-600">{{ number_format((float)($item['quantity'] ?? 0), 0) }}</td>
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
                                                    'sourcing' => 'Confirmed',
                                                    'sourced'  => 'Sourced',
                                                    'rejected' => 'Rejected',
                                                    default    => 'Pending',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                {{ $badgeLabel }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2.5 text-center">
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
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Items</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($items) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Selected</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $selectedCount }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('enduser.projects.show', $boq->project?->uuid ?? '') }}"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        &larr; Back to Project
                    </a>

                    <button
                        type="button"
                        wire:click="createQuotation"
                        wire:loading.attr="disabled"
                        wire:confirm="Create a quotation from {{ $selectedCount }} selected item(s)?"
                        @if($selectedCount === 0) disabled @endif
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="createQuotation" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Create Quotation ({{ $selectedCount }} items) &rarr;
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
