<div
    x-data="{
        statusOpen: false,
        companyOpen: false,
        dateOpen: false,
    }"
>

    {{-- ───── Page Header ─────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('app.all_quotations') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('app.track_quotation_status') }}
                {{ __('app.total_colon') }} <span class="font-semibold text-slate-700">{{ $total }} {{ __('app.results') }}</span>
            </p>
        </div>
    </div>

    {{-- ───── Filters Row ──────────────────────────────────────────────────── --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[260px]">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('app.search_quotation_company') }}"
                class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        {{-- Status filter --}}
        <div class="relative">
            <button
                type="button"
                @click="statusOpen = !statusOpen"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="statusOpen || @js($status !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($status !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                {{ __('app.status') }}
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div
                x-show="statusOpen"
                x-cloak
                @click.outside="statusOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <button type="button" wire:click="$set('status', '')" @click="statusOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    {{ __('app.all_statuses') }}
                </button>
                @foreach($statuses as $s)
                    @if($s->value !== 'draft')
                        <button type="button" wire:click="$set('status', '{{ $s->value }}')" @click="statusOpen = false"
                            class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === $s->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                            {{ $s->label() }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Date Created filter --}}
        <div class="relative">
            <button
                type="button"
                @click="dateOpen = !dateOpen"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="dateOpen || @js($created_from !== '' || $created_to !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($created_from !== '' || $created_to !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('app.date_created') }}
            </button>
            <div
                x-show="dateOpen"
                x-cloak
                @click.outside="dateOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-56 rounded-xl border border-slate-200 bg-white p-4 shadow-lg"
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

        {{-- Company filter --}}
        <div class="relative">
            <button
                type="button"
                @click="companyOpen = !companyOpen"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="companyOpen || @js($company !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($company !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ __('app.company') }}
            </button>
            <div
                x-show="companyOpen"
                x-cloak
                @click.outside="companyOpen = false"
                class="absolute right-0 top-full z-20 mt-1.5 w-56 rounded-xl border border-slate-200 bg-white p-3 shadow-lg"
            >
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.filter_by_company') }}</p>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="company"
                    placeholder="{{ __('app.company_name_placeholder') }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none focus:border-emerald-400"
                >
            </div>
        </div>

        @if($hasActiveFilters)
            <button type="button" wire:click="clearFilters"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                {{ __('app.clear_filters') }}
            </button>
        @endif

    </div>

    {{-- ───── Table Card ────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">

            @if($quotations->isEmpty())
                <div class="py-24 text-center">
                    <svg class="mx-auto mb-4 h-12 w-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-400">{{ __('app.no_quotations_found') }}</p>
                    <p class="mt-1 text-xs text-slate-300">{{ __('app.try_adjusting_search_filters') }}</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-start">
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-28">{{ __('app.id') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.company') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.client') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.project') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-32">{{ __('app.created') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-36 text-end">
                                {{ __('app.amount_sar') }}
                            </th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-36">{{ __('app.status') }}</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400 w-24 text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quotations as $quotation)
                            @php
                                $sv = $quotation->status->value ?? '';

                                $badgeClass = match($sv) {
                                    'draft'     => 'bg-slate-100 text-slate-500',
                                    'tender'    => 'bg-blue-50 text-blue-600',
                                    'submitted' => 'bg-indigo-50 text-indigo-600',
                                    'in_review' => 'bg-amber-50 text-amber-600',
                                    'quoted'    => 'bg-emerald-50 text-emerald-600',
                                    'accepted'  => 'bg-green-50 text-green-700',
                                    'rejected'  => 'bg-red-50 text-red-600',
                                    'cancelled' => 'bg-rose-50 text-rose-600',
                                    default     => 'bg-slate-100 text-slate-500',
                                };

                                $amount = $quotation->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity);
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/60">

                                {{-- ID --}}
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs font-bold text-slate-700">#{{ $quotation->quotation_no }}</span>
                                </td>

                                {{-- Company --}}
                                <td class="px-5 py-4">
                                    <span class="block max-w-[160px] truncate font-semibold text-slate-800">
                                        {{ $quotation->client?->clientProfile?->company_name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Client --}}
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $quotation->client?->name ?? '—' }}
                                </td>

                                {{-- Project --}}
                                <td class="px-5 py-4">
                                    <span class="block max-w-[180px] truncate text-slate-700">
                                        {{ $quotation->project_name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Created --}}
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $quotation->created_at?->format('M d, Y') }}
                                </td>

                                {{-- Amount --}}
                                <td class="px-5 py-4 text-end font-mono font-semibold text-slate-800">
                                    {{ $amount > 0 ? number_format($amount, 2) : '—' }}
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                                        {{ $quotation->status->label() }}
                                    </span>
                                </td>

                                {{-- Actions: view only --}}
                                <td class="px-5 py-4 text-center">
                                    <a
                                        href="{{ route('admin.quotations.show', $quotation->uuid) }}"
                                        wire:navigate
                                        title="{{ __('app.view_quotation') }}"
                                        class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Footer / Pagination --}}
        @if(!$quotations->isEmpty())
        <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                {{ __('app.showing') }}
                <span class="font-semibold text-slate-700">{{ $quotations->firstItem() }}</span>
                {{ __('app.to') }}
                <span class="font-semibold text-slate-700">{{ $quotations->lastItem() }}</span>
                {{ __('app.of') }}
                <span class="font-semibold text-slate-700">{{ $quotations->total() }}</span>
                {{ __('app.results') }}
            </p>

            @if($quotations->hasPages())
            <nav class="flex items-center gap-1">
                @if($quotations->onFirstPage())
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

                @foreach($quotations->getUrlRange(max(1, $quotations->currentPage() - 2), min($quotations->lastPage(), $quotations->currentPage() + 2)) as $page => $url)
                    @if($page == $quotations->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm text-slate-600 transition hover:bg-slate-50">{{ $page }}</button>
                    @endif
                @endforeach

                @if($quotations->hasMorePages())
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

</div>
