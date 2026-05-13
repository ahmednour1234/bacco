@extends('layouts.enduser-app')

@section('title', __('app.title_my_boqs'))
@section('page-title', __('app.bills_of_quantities'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.boq') }}</span>
@endsection

@section('content')
<div
    class="mx-auto max-w-5xl"
    x-data="boqList()"
    x-init="load()"
>
    {{-- Toast --}}
    <div
        x-show="toast !== null"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-[100000] flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
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

    {{-- Delete Modal --}}
    <div
        x-show="deleteModal.open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none"
    >
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModal.open = false"></div>
        <div
            x-show="deleteModal.open"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            @click.stop
            class="relative w-full max-w-xs rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 text-center"
        >
            <div class="px-6 pt-8 pb-6">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('app.delete_boq') }}</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    {{ __('app.sure_permanently_delete') }}
                    <span class="font-semibold text-slate-800" x-text="deleteModal.boq?.boq_no"></span>
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ __('app.cannot_be_undone') }}</p>
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

    {{-- Page Header --}}
    <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl shadow-md sm:h-12 sm:w-12"
                 style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                <svg class="h-5 w-5 text-white sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight sm:text-2xl">{{ __('app.bills_of_quantities') }}</h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">{{ __('app.manage_boqs_desc') }}</p>
            </div>
        </div>
        <a href="{{ route('enduser.boqs.create') }}"
            class="inline-flex items-center justify-center gap-2.5 px-5 py-2.5 text-sm font-bold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5 sm:flex-shrink-0 sm:px-6 sm:py-3"
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 999px; box-shadow: 0 6px 20px rgba(16,185,129,0.40); letter-spacing:0.01em;">
            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full" style="background:rgba(255,255,255,0.25);">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            {{ __('app.new_boq') }}
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="mb-7 grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- Total --}}
        <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
             style="border: 1px solid #d1fae5;">
            <div class="absolute top-0 left-0 right-0 h-1.5 rounded-t-2xl" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="flex items-center gap-3 px-3 pt-6 pb-5 sm:gap-5 sm:px-6 sm:pt-7 sm:pb-6">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl shadow-lg sm:h-[58px] sm:w-[58px] sm:rounded-2xl"
                     style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <svg class="h-5 w-5 text-white sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-black leading-none text-slate-900 text-2xl sm:text-[2.6rem]" x-text="stats.total ?? '—'"></p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider sm:tracking-widest" style="color:#059669;">{{ __('app.total_boqs') }}</p>
                    <p class="mt-0.5 text-[11px] font-medium text-slate-400 hidden sm:block">All time BOQs</p>
                </div>
            </div>
        </div>

        {{-- Draft --}}
        <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
             style="border: 1px solid #d1fae5;">
            <div class="absolute top-0 left-0 right-0 h-1.5 rounded-t-2xl" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="flex items-center gap-3 px-3 pt-6 pb-5 sm:gap-5 sm:px-6 sm:pt-7 sm:pb-6">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl shadow-lg sm:h-[58px] sm:w-[58px] sm:rounded-2xl"
                     style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <svg class="h-5 w-5 text-white sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-black leading-none text-slate-900 text-2xl sm:text-[2.6rem]" x-text="stats.draft ?? '—'"></p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider sm:tracking-widest" style="color:#059669;">{{ __('app.status_draft') }}</p>
                    <p class="mt-0.5 text-[11px] font-medium text-slate-400 hidden sm:block">Awaiting review</p>
                </div>
            </div>
        </div>

        {{-- Submitted --}}
        <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
             style="border: 1px solid #d1fae5;">
            <div class="absolute top-0 left-0 right-0 h-1.5 rounded-t-2xl" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="flex items-center gap-3 px-3 pt-6 pb-5 sm:gap-5 sm:px-6 sm:pt-7 sm:pb-6">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl shadow-lg sm:h-[58px] sm:w-[58px] sm:rounded-2xl"
                     style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <svg class="h-5 w-5 text-white sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-black leading-none text-slate-900 text-2xl sm:text-[2.6rem]" x-text="stats.submitted ?? '—'"></p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider sm:tracking-widest" style="color:#059669;">{{ __('app.status_submitted') }}</p>
                    <p class="mt-0.5 text-[11px] font-medium text-slate-400 hidden sm:block">Under review</p>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
             style="border: 1px solid #d1fae5;">
            <div class="absolute top-0 left-0 right-0 h-1.5 rounded-t-2xl" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="flex items-center gap-3 px-3 pt-6 pb-5 sm:gap-5 sm:px-6 sm:pt-7 sm:pb-6">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl shadow-lg sm:h-[58px] sm:w-[58px] sm:rounded-2xl"
                     style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <svg class="h-5 w-5 text-white sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-black leading-none text-slate-900 text-2xl sm:text-[2.6rem]" x-text="stats.completed ?? '—'"></p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider sm:tracking-widest" style="color:#059669;">{{ __('app.status_completed') }}</p>
                    <p class="mt-0.5 text-[11px] font-medium text-slate-400 hidden sm:block">Successfully completed</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Action Bar --}}
    <div class="mb-5 flex flex-wrap items-center gap-3" @click="closeAll()">

        {{-- Search --}}
        <div class="relative w-full sm:min-w-[220px] sm:flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input
                type="text"
                x-model="filters.search"
                @input.debounce.400ms="resetAndLoad()"
                placeholder="{{ __('app.search_boqs') }}"
                class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-9 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
            <button x-show="filters.search !== ''" @click="filters.search = ''; resetAndLoad()" type="button"
                class="absolute inset-y-0 right-3 flex items-center text-slate-300 hover:text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Type filter --}}
        <div class="relative" @click.stop>
            <button type="button"
                @click="typeOpen = !typeOpen; statusOpen = false; sortOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-3.5 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="typeOpen || filters.type !== '' ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'">
                <span x-show="filters.type !== ''" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Type
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="typeOpen" x-cloak @click.outside="typeOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                <button type="button" @click="filters.type = ''; typeOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.type === '' ? 'font-semibold text-emerald-600' : 'text-slate-700'">All Types</button>
                <button type="button" @click="filters.type = 'tender'; typeOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.type === 'tender' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Tender (Bidding)</button>
                <button type="button" @click="filters.type = 'awarded'; typeOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.type === 'awarded' ? 'font-semibold text-emerald-600' : 'text-slate-700'">On-hand (Awarded)</button>
            </div>
        </div>

        {{-- Status filter --}}
        <div class="relative" @click.stop>
            <button type="button"
                @click="statusOpen = !statusOpen; typeOpen = false; sortOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-3.5 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="statusOpen || filters.status !== '' ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'">
                <span x-show="filters.status !== ''" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                {{ __('app.status') }}
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="statusOpen" x-cloak @click.outside="statusOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                <button type="button" @click="filters.status = ''; statusOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.status === '' ? 'font-semibold text-emerald-600' : 'text-slate-700'">{{ __('app.all_statuses') }}</button>
                <button type="button" @click="filters.status = 'draft'; statusOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.status === 'draft' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Draft</button>
                <button type="button" @click="filters.status = 'submitted'; statusOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.status === 'submitted' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Submitted</button>
                <button type="button" @click="filters.status = 'completed'; statusOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.status === 'completed' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Completed</button>
            </div>
        </div>

        {{-- Sort --}}
        <div class="relative" @click.stop>
            <button type="button"
                @click="sortOpen = !sortOpen; statusOpen = false; typeOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                <span x-text="filters.sort === 'oldest' ? 'Oldest first' : 'Newest first'"></span>
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="sortOpen" x-cloak @click.outside="sortOpen = false"
                class="absolute right-0 top-full z-20 mt-1.5 w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg">
                <button type="button" @click="filters.sort = 'newest'; sortOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.sort === 'newest' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Newest first</button>
                <button type="button" @click="filters.sort = 'oldest'; sortOpen = false; resetAndLoad()"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50"
                    :class="filters.sort === 'oldest' ? 'font-semibold text-emerald-600' : 'text-slate-700'">Oldest first</button>
            </div>
        </div>

        <button x-show="hasActiveFilters()" type="button" @click="clearFilters()"
            class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
            {{ __('app.clear') }}
        </button>

    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="i in 6" :key="i">
            <div class="animate-pulse rounded-2xl bg-white shadow-sm" style="border: 1px solid #e2e8f0;">
                <div class="h-1 rounded-t-2xl bg-slate-200"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-3 w-20 rounded-full bg-slate-100"></div>
                        <div class="h-6 w-16 rounded-full bg-slate-100"></div>
                    </div>
                    <div class="h-5 w-3/4 rounded bg-slate-100 mb-2"></div>
                    <div class="h-3 w-1/3 rounded bg-slate-100 mb-4"></div>
                    <div class="h-1.5 w-full rounded-full bg-slate-100 mb-4"></div>
                    <div class="flex justify-between border-t border-slate-100 pt-3">
                        <div class="h-7 w-16 rounded-lg bg-slate-100"></div>
                        <div class="h-7 w-20 rounded-full bg-slate-100"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && boqs.length === 0"
        class="rounded-3xl bg-white"
        style="border: 1px solid #d1fae5; box-shadow: 0 4px 32px rgba(16,185,129,0.07);">
        <div class="flex flex-col items-center py-20 px-10">

            {{-- Icon tile --}}
            <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-3xl"
                 style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 12px 32px rgba(16,185,129,0.35);">
                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>

            {{-- Text --}}
            <h3 class="mt-6 text-xl font-black text-slate-800">{{ __('app.no_boqs_found') }}</h3>
            <p class="mt-2 text-sm font-medium text-slate-400">{{ __('app.create_boq_get_started') }}</p>

            {{-- CTA button --}}
            <a href="{{ route('enduser.boqs.create') }}"
                class="mt-8 inline-flex items-center gap-2.5 px-8 py-3.5 text-sm font-bold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 999px; box-shadow: 0 8px 24px rgba(16,185,129,0.40); letter-spacing:0.01em;">
                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full" style="background:rgba(255,255,255,0.25);">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                {{ __('app.new_boq') }}
            </a>

        </div>
    </div>

    {{-- BOQ Grid --}}
    <div x-show="!loading && boqs.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        <template x-for="boq in boqs" :key="boq.id">
            <div class="group flex flex-col rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
                 style="border: 1px solid #e2e8f0;">

                {{-- Color accent strip based on status --}}
                <div class="h-1 rounded-t-2xl transition-all"
                    :class="{
                        'bg-gradient-to-r from-amber-400 to-yellow-300': boq.status === 'draft',
                        'bg-gradient-to-r from-blue-500 to-blue-400':    boq.status === 'submitted',
                        'bg-gradient-to-r from-emerald-500 to-teal-400': boq.status === 'completed',
                        'bg-gradient-to-r from-red-400 to-rose-300':     boq.status === 'cancelled',
                        'bg-gradient-to-r from-slate-300 to-slate-200':  !boq.status,
                    }"></div>

                {{-- Card Header --}}
                <div class="flex items-center justify-between px-4 pt-3 pb-1">
                    <span class="max-w-[55%] truncate font-mono text-[10px] font-semibold px-2 py-0.5 rounded-md"
                          style="background:#f1f5f9; color:#94a3b8;"
                          x-text="'#' + boq.boq_no"></span>
                    <div class="flex items-center gap-1.5">
                        {{-- Status badge --}}
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold tracking-wide"
                            :style="{
                                'background': boq.status === 'draft'     ? 'rgba(245,158,11,0.12)'  :
                                              boq.status === 'submitted' ? 'rgba(59,130,246,0.12)'  :
                                              boq.status === 'completed' ? 'rgba(16,185,129,0.12)'  :
                                              boq.status === 'cancelled' ? 'rgba(239,68,68,0.12)'   : 'rgba(100,116,139,0.10)',
                                'color':      boq.status === 'draft'     ? '#b45309'  :
                                              boq.status === 'submitted' ? '#1d4ed8'  :
                                              boq.status === 'completed' ? '#065f46'  :
                                              boq.status === 'cancelled' ? '#b91c1c'  : '#475569',
                                'border':     boq.status === 'draft'     ? '1px solid rgba(245,158,11,0.30)'  :
                                              boq.status === 'submitted' ? '1px solid rgba(59,130,246,0.30)'  :
                                              boq.status === 'completed' ? '1px solid rgba(16,185,129,0.30)'  :
                                              boq.status === 'cancelled' ? '1px solid rgba(239,68,68,0.30)'   : '1px solid rgba(100,116,139,0.20)',
                            }">
                            {{-- Draft icon --}}
                            <template x-if="boq.status === 'draft'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </template>
                            {{-- Submitted icon --}}
                            <template x-if="boq.status === 'submitted'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </template>
                            {{-- Completed icon --}}
                            <template x-if="boq.status === 'completed'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            {{-- Cancelled icon --}}
                            <template x-if="boq.status === 'cancelled'">
                                <svg class="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </template>
                            <span x-text="boq.status_label"></span>
                        </span>

                        {{-- 3-dot menu --}}
                        <div class="relative">
                            <button type="button"
                                @click.stop="activeMenu = activeMenu === boq.id ? null : boq.id"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div x-show="activeMenu === boq.id" x-cloak @click.stop
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="absolute right-0 top-full z-30 mt-1 w-52 rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl">
                                <a :href="boq.view_url"
                                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ __('app.view') }}
                                </a>
                                <template x-if="boq.is_draft">
                                    <a :href="boq.edit_url"
                                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('app.edit') }}
                                    </a>
                                </template>
                                <button type="button" @click="doDuplicate(boq); activeMenu = null"
                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Duplicate
                                </button>
                                <template x-if="boq.is_draft">
                                    <span>
                                        <button type="button" @click="doConvert(boq); activeMenu = null"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                                            </svg>
                                            {{ __('app.convert_to_quotation') }}
                                        </button>
                                        <div class="my-1 border-t border-slate-100"></div>
                                        <button type="button" @click="openDelete(boq); activeMenu = null"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('app.delete') }}
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="flex-1 px-4 pt-2 pb-3">
                    <h3 class="truncate text-base font-bold text-slate-900" x-text="boq.project_name"></h3>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="{
                                'bg-blue-100 text-blue-700':    boq.type === 'tender',
                                'bg-emerald-100 text-emerald-700': boq.type === 'awarded',
                                'bg-slate-100 text-slate-700':  !boq.type,
                            }"
                            x-text="boq.type_label"></span>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span x-text="boq.items_count + ' {{ __('app.items') }}'"></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="boq.created_at"></span>
                        </span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="px-4 pb-3">
                    <div class="h-1.5 w-full rounded-full bg-slate-100">
                        <div class="h-1.5 rounded-full transition-all duration-500"
                            :class="{
                                'bg-emerald-400': boq.status === 'completed',
                                'bg-blue-400':    boq.status === 'submitted',
                                'bg-slate-300':   boq.status === 'draft',
                            }"
                            :style="'width:' + boq.progress + '%'"></div>
                    </div>
                    <p class="mt-1 text-right text-[11px] text-slate-400" x-text="boq.progress + '%'"></p>
                </div>

                {{-- Card Footer --}}
                <div class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5">
                    <div class="flex items-center gap-0.5">
                        <a :href="boq.view_url" title="{{ __('app.view') }}"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <template x-if="boq.is_draft">
                            <a :href="boq.edit_url" title="{{ __('app.edit') }}"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </template>
                    </div>

                    <template x-if="boq.is_draft && boq.items_count > 0">
                        <button type="button" @click="doConvert(boq)"
                            class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                            </svg>
                            Convert
                        </button>
                    </template>
                    <template x-if="boq.is_draft && boq.items_count === 0">
                        <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-xs font-semibold text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                            </svg>
                            No items
                        </span>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Pagination --}}
    <div x-show="!loading && pagination.last_page > 1" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
            {{ __('app.showing') }}
            <span class="font-semibold text-slate-700" x-text="pagination.from"></span>
            {{ __('app.to') }}
            <span class="font-semibold text-slate-700" x-text="pagination.to"></span>
            {{ __('app.of') }}
            <span class="font-semibold text-slate-700" x-text="pagination.total"></span>
            {{ __('app.results') }}
        </p>

        <nav class="flex items-center gap-1">
            {{-- Prev --}}
            <button type="button" @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Page numbers --}}
            <template x-for="p in pageRange()" :key="p">
                <button type="button" @click="goToPage(p)"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition"
                    :class="p === pagination.current_page
                        ? 'border-emerald-500 bg-emerald-500 font-semibold text-white'
                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                    x-text="p">
                </button>
            </template>

            {{-- Next --}}
            <button type="button" @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </nav>
    </div>

</div>

<script>
function boqList() {
    return {
        loading: true,
        boqs: [],
        stats: { total: 0, draft: 0, submitted: 0, completed: 0 },
        pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 },
        filters: { search: '', status: '', type: '', sort: 'newest' },
        activeMenu: null,
        newBoqOpen: false,
        statusOpen: false,
        typeOpen: false,
        sortOpen: false,
        toast: null,
        toastTimer: null,
        deleteModal: { open: false, boq: null },
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        load() {
            this.loading = true;
            const params = new URLSearchParams({
                search:   this.filters.search,
                status:   this.filters.status,
                type:     this.filters.type,
                sort:     this.filters.sort,
                page:     this.pagination.current_page,
                per_page: this.pagination.per_page,
            });

            fetch(`{{ route('enduser.boqs.data') }}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.boqs       = data.boqs;
                this.stats      = data.stats;
                this.pagination = data.pagination;
                this.loading    = false;
            })
            .catch(() => {
                this.loading = false;
                this.showToast('Failed to load BOQs.', 'error');
            });
        },

        resetAndLoad() {
            this.pagination.current_page = 1;
            this.load();
        },

        goToPage(page) {
            if (page < 1 || page > this.pagination.last_page) return;
            this.pagination.current_page = page;
            this.load();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        pageRange() {
            const cur  = this.pagination.current_page;
            const last = this.pagination.last_page;
            const min  = Math.max(1, cur - 2);
            const max  = Math.min(last, cur + 2);
            const pages = [];
            for (let i = min; i <= max; i++) pages.push(i);
            return pages;
        },

        hasActiveFilters() {
            return this.filters.search !== '' || this.filters.status !== '' || this.filters.type !== '';
        },

        clearFilters() {
            this.filters = { search: '', status: '', type: '', sort: 'newest' };
            this.resetAndLoad();
        },

        closeAll() {
            this.activeMenu = null;
            this.statusOpen = false;
            this.typeOpen   = false;
            this.sortOpen   = false;
            this.newBoqOpen = false;
        },

        openDelete(boq) {
            this.deleteModal = { open: true, boq };
        },

        confirmDelete() {
            const boq = this.deleteModal.boq;
            this.deleteModal.open = false;
            if (!boq) return;

            fetch(boq.delete_url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':    this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':          'application/json',
                }
            })
            .then(async r => {
                const data = await r.json();
                if (r.ok) {
                    this.showToast(data.message ?? 'BOQ deleted.', 'success');
                    this.load();
                } else {
                    this.showToast(data.message ?? 'Delete failed.', 'error');
                }
            })
            .catch(() => this.showToast('Delete failed.', 'error'));
        },

        doDuplicate(boq) {
            fetch(boq.duplicate_url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':    this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':          'application/json',
                }
            })
            .then(async r => {
                const data = await r.json();
                if (r.ok) {
                    this.showToast(data.message ?? 'BOQ duplicated.', 'success');
                    this.load();
                } else {
                    this.showToast(data.message ?? 'Duplicate failed.', 'error');
                }
            })
            .catch(() => this.showToast('Duplicate failed.', 'error'));
        },

        doConvert(boq) {
            if (boq.items_count === 0) {
                this.showToast('Add items to this BOQ before converting.', 'error');
                return;
            }
            fetch(boq.convert_url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':    this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':          'application/json',
                }
            })
            .then(async r => {
                const data = await r.json();
                if (r.ok && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    this.showToast(data.message ?? 'Convert failed.', 'error');
                }
            })
            .catch(() => this.showToast('Convert failed.', 'error'));
        },

        showToast(message, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { message, type };
            this.toastTimer = setTimeout(() => this.toast = null, 4000);
        },
    };
}
</script>
@endsection

