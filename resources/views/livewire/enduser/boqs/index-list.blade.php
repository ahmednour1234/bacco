<div
    x-data="{
        statusOpen: false,
        dateOpen: false,
        toast: null,
        deleteModal: { open: false, id: null, no: '' },
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        },
        openDelete(id, no) {
            this.deleteModal = { open: true, id, no };
        },
        confirmDelete() {
            if (this.deleteModal.id) {
                $wire.deleteBoq(this.deleteModal.id);
            }
            this.deleteModal = { open: false, id: null, no: '' };
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

    {{-- ───── Delete Confirmation Modal ─────────────────────────────────────────── --}}
    <div
        x-show="deleteModal.open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none"
    >
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModal.open = false"></div>
        <div
            x-show="deleteModal.open"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-sm rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5"
        >
            <div class="h-1.5 w-full rounded-t-2xl bg-gradient-to-r from-red-400 to-rose-500"></div>
            <div class="px-6 pb-6 pt-5">
                <h3 class="text-center text-base font-bold text-slate-900">{{ __('app.delete_boq') }}</h3>
                <p class="mt-1.5 text-center text-sm text-slate-500">
                    {{ __('app.sure_permanently_delete') }}
                    <span class="font-semibold text-slate-800" x-text="'#' + deleteModal.no"></span>?
                    <br>{{ __('app.cannot_be_undone') }}
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <button type="button" @click="deleteModal.open = false"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="button" @click="confirmDelete()"
                        class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600">
                        {{ __('app.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ───── Page Header ───────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.bills_of_quantities') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('app.manage_boqs_desc') }}</p>
        </div>
        <a
            href="{{ route('enduser.boqs.create') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.new_boq') }}
        </a>
    </div>

    {{-- ───── Stat Cards ────────────────────────────────────────────────────────── --}}
    <div class="mb-7 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.total_boqs') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                <svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_draft') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['draft'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_submitted') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['submitted'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="h-6 w-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-2xl border border-blue-100 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_completed') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['completed'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ───── Section Header + Search ─────────────────────────────────────────── --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <h2 class="flex-1 text-base font-bold text-slate-900">{{ __('app.recent_boqs') }}</h2>

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
                placeholder="{{ __('app.search_boqs') }}"
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

    {{-- ───── BOQ Table ────────────────────────────────────────────────────────── --}}
    @if($boqs->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
            <svg class="mx-auto mb-4 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">{{ __('app.no_boqs_found') }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ __('app.create_boq_get_started') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.id') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.project') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.status') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.type') }}</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.items') }}</th>
                            <th class="px-5 py-3 text-start text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.created') }}</th>
                            <th class="px-5 py-3 text-end text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($boqs as $boq)
                            @php
                                $sv = $boq->status->value ?? '';

                                $badgeClass = match($sv) {
                                    'draft'     => 'bg-slate-100 text-slate-500',
                                    'submitted' => 'bg-blue-50 text-blue-600',
                                    'completed' => 'bg-emerald-50 text-emerald-700',
                                    'cancelled' => 'bg-red-50 text-red-600',
                                    default     => 'bg-slate-100 text-slate-500',
                                };

                                $typeColors = [
                                    'tender'  => 'bg-blue-100 text-blue-700',
                                    'awarded' => 'bg-emerald-100 text-emerald-700',
                                ];
                                $typeColor = $typeColors[$boq->type->value ?? ''] ?? 'bg-slate-100 text-slate-700';

                                $itemCount = $boq->items->count();
                            @endphp
                            <tr class="group hover:bg-slate-50/60 transition-colors">

                                {{-- ID --}}
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-xs text-slate-400">#{{ $boq->boq_no }}</span>
                                </td>

                                {{-- Project --}}
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-800">{{ $boq->project?->name ?? '—' }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeClass }}">
                                        {{ $boq->status->label() }}
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $typeColor }}">
                                        {{ $boq->type?->label() ?? '—' }}
                                    </span>
                                </td>

                                {{-- Items count --}}
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                        {{ $itemCount }}
                                    </span>
                                </td>

                                {{-- Created --}}
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ $boq->created_at?->format('M d, Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($sv === 'draft')
                                            <button
                                                type="button"
                                                wire:click="convertToQuotation('{{ $boq->uuid }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="convertToQuotation('{{ $boq->uuid }}')"
                                                title="{{ __('app.convert_to_quotation') }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 disabled:opacity-60"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                                                </svg>
                                                {{ __('app.convert_to_quotation') }}
                                            </button>
                                        @endif

                                        <a
                                            href="{{ route('enduser.boqs.show', $boq->uuid) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            {{ __('app.view') }}
                                        </a>

                                        @if($sv === 'draft')
                                            <button
                                                type="button"
                                                @click="openDelete({{ $boq->id }}, '{{ $boq->boq_no }}')"
                                                title="{{ __('app.delete') }}"
                                                class="rounded-lg border border-red-200 bg-red-50 p-1.5 text-red-500 transition hover:bg-red-100"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">
                {{ __('app.showing') }}
                <span class="font-semibold text-slate-700">{{ $boqs->firstItem() }}</span>
                {{ __('app.to') }}
                <span class="font-semibold text-slate-700">{{ $boqs->lastItem() }}</span>
                {{ __('app.of') }}
                <span class="font-semibold text-slate-700">{{ $boqs->total() }}</span>
                {{ __('app.results') }}
            </p>

            @if($boqs->hasPages())
            <nav class="flex items-center gap-1">
                @if($boqs->onFirstPage())
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

                @foreach($boqs->getUrlRange(max(1, $boqs->currentPage() - 2), min($boqs->lastPage(), $boqs->currentPage() + 2)) as $page => $url)
                    @if($page == $boqs->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">{{ $page }}</button>
                    @endif
                @endforeach

                @if($boqs->hasMorePages())
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
