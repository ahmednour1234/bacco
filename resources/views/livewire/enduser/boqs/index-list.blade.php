<div
    x-data="{
        statusOpen: false,
        typeOpen: false,
        sortOpen: false,
        newBoqOpen: false,
        toast: null,
        deleteModal: { open: false, id: null, no: '' },
        activeMenu: null,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        },
        openDelete(id, no) {
            this.deleteModal = { open: true, id, no };
            this.activeMenu = null;
        },
        confirmDelete() {
            if (this.deleteModal.id) {
                $wire.deleteBoq(this.deleteModal.id);
            }
            this.deleteModal = { open: false, id: null, no: '' };
        },
        toggleMenu(id) {
            this.activeMenu = this.activeMenu === id ? null : id;
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
    @click="activeMenu = null; statusOpen = false; typeOpen = false; sortOpen = false; newBoqOpen = false"
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

    {{-- Delete Confirmation Modal --}}
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
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-xs rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 text-center"
            @click.stop
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
                    <span class="font-semibold text-slate-800" x-text="deleteModal.no"></span>
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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('app.bills_of_quantities') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('app.manage_boqs_desc') }}</p>
    </div>

    {{-- Stat Cards --}}
    <div class="mb-7 grid grid-cols-2 gap-4 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-400">{{ __('app.total_boqs') }}</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                    <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            <p class="mt-0.5 text-xs text-slate-400">All time BOQs</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_draft') }}</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                    <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['draft'] }}</p>
            <p class="mt-0.5 text-xs text-slate-400">Awaiting review</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_submitted') }}</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['submitted'] }}</p>
            <p class="mt-0.5 text-xs text-slate-400">Under review</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-400">{{ __('app.status_completed') }}</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['completed'] }}</p>
            <p class="mt-0.5 text-xs text-slate-400">Successfully completed</p>
        </div>

    </div>

    {{-- Action Bar --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">

        {{-- New BOQ split button --}}
        <div class="relative flex" @click.stop>
            <a
                href="{{ route('enduser.boqs.create') }}"
                class="inline-flex items-center gap-2 rounded-l-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.new_boq') }}
            </a>
            <button
                type="button"
                @click="newBoqOpen = !newBoqOpen"
                class="inline-flex items-center rounded-r-xl border-l border-emerald-400 bg-emerald-500 px-2.5 py-2.5 text-white shadow-sm transition hover:bg-emerald-600"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div
                x-show="newBoqOpen"
                x-cloak
                @click.outside="newBoqOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-52 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <a href="{{ route('enduser.boqs.create') }}"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Blank BOQ
                </a>
            </div>
        </div>

        {{-- Search --}}
        <div class="relative min-w-[220px] flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input
                type="text"
                x-ref="searchInput"
                :value="$wire.search"
                @input.debounce.400ms="$wire.set('search', $event.target.value)"
                placeholder="{{ __('app.search_boqs') }}"
                class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-9 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
            <button
                x-show="$wire.search !== ''"
                @click="$wire.set('search', ''); $refs.searchInput.value = ''"
                type="button"
                class="absolute inset-y-0 right-3 flex items-center text-slate-300 hover:text-slate-500"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Type filter --}}
        <div class="relative" @click.stop>
            <button
                type="button"
                @click="typeOpen = !typeOpen; statusOpen = false; sortOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-3.5 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="typeOpen || @js($type !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($type !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                Type
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div
                x-show="typeOpen"
                x-cloak
                @click.outside="typeOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <button type="button" @click="$wire.set('type', ''); typeOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $type === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    All Types
                </button>
                @foreach($types as $typeItem)
                    <button type="button" @click="$wire.set('type', '{{ $typeItem->value }}'); typeOpen = false"
                        class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $type === $typeItem->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                        {{ $typeItem->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Status filter --}}
        <div class="relative" @click.stop>
            <button
                type="button"
                @click="statusOpen = !statusOpen; typeOpen = false; sortOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border bg-white px-3.5 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50"
                :class="statusOpen || @js($status !== '') ? 'border-emerald-400 text-emerald-700' : 'border-slate-200 text-slate-600'"
            >
                @if($status !== '')
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                @endif
                {{ __('app.status') }}
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div
                x-show="statusOpen"
                x-cloak
                @click.outside="statusOpen = false"
                class="absolute left-0 top-full z-20 mt-1.5 w-48 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <button type="button" @click="$wire.set('status', ''); statusOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === '' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    {{ __('app.all_statuses') }}
                </button>
                @foreach($statuses as $statusItem)
                    <button type="button" @click="$wire.set('status', '{{ $statusItem->value }}'); statusOpen = false"
                        class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $status === $statusItem->value ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                        {{ $statusItem->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Sort --}}
        <div class="relative" @click.stop>
            <button
                type="button"
                @click="sortOpen = !sortOpen; statusOpen = false; typeOpen = false"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                {{ $sort === 'oldest' ? 'Oldest first' : 'Newest first' }}
                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div
                x-show="sortOpen"
                x-cloak
                @click.outside="sortOpen = false"
                class="absolute right-0 top-full z-20 mt-1.5 w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
            >
                <button type="button" @click="$wire.set('sort', 'newest'); sortOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $sort === 'newest' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    Newest first
                </button>
                <button type="button" @click="$wire.set('sort', 'oldest'); sortOpen = false"
                    class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-50 {{ $sort === 'oldest' ? 'font-semibold text-emerald-600' : 'text-slate-700' }}">
                    Oldest first
                </button>
            </div>
        </div>

        @if($hasActiveFilters)
            <button type="button" wire:click="clearFilters"
                class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                {{ __('app.clear') }}
            </button>
        @endif

    </div>

    {{-- BOQ Card Grid --}}
    @if($boqs->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
            <svg class="mx-auto mb-4 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">{{ __('app.no_boqs_found') }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ __('app.create_boq_get_started') }}</p>
            <a href="{{ route('enduser.boqs.create') }}"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.new_boq') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach($boqs as $boq)
                @php
                    $sv        = $boq->status->value ?? 'draft';
                    $isDraft   = $sv === 'draft';
                    $itemCount = $boq->items->count();
                    $progress  = match($sv) {
                        'completed' => 100,
                        'submitted' => 50,
                        default     => 0,
                    };

                    $statusBadgeClass = match($sv) {
                        'draft'     => 'bg-slate-100 text-slate-600',
                        'submitted' => 'bg-blue-50 text-blue-600 border border-blue-100',
                        'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'cancelled' => 'bg-red-50 text-red-600 border border-red-100',
                        default     => 'bg-slate-100 text-slate-600',
                    };

                    $typeColor = match($boq->type->value ?? '') {
                        'tender'  => 'bg-blue-100 text-blue-700',
                        'awarded' => 'bg-emerald-100 text-emerald-700',
                        default   => 'bg-slate-100 text-slate-700',
                    };

                    $progressColor = match($sv) {
                        'completed' => 'bg-emerald-400',
                        'submitted' => 'bg-blue-400',
                        default     => 'bg-slate-300',
                    };
                @endphp

                <div class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md hover:border-slate-300">

                    {{-- Card Header --}}
                    <div class="flex items-center justify-between px-4 pt-4 pb-1">
                        <span class="max-w-[55%] truncate font-mono text-[11px] font-medium text-slate-400">#{{ $boq->boq_no }}</span>
                        <div class="flex items-center gap-1.5" @click.stop>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $statusBadgeClass }}">
                                @if($sv === 'completed')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($sv === 'submitted')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                @endif
                                {{ $boq->status->label() }}
                            </span>
                            {{-- 3-dot menu --}}
                            <div class="relative">
                                <button
                                    type="button"
                                    @click.stop="toggleMenu({{ $boq->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>
                                <div
                                    x-show="activeMenu === {{ $boq->id }}"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click.stop
                                    class="absolute right-0 top-full z-30 mt-1 w-52 rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl"
                                >
                                    <a href="{{ route('enduser.boqs.show', $boq->uuid) }}"
                                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ __('app.view') }}
                                    </a>
                                    @if($isDraft)
                                        <a href="{{ route('enduser.boqs.create') . '?draft=' . $boq->uuid }}"
                                            class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('app.edit') }}
                                        </a>
                                    @endif
                                    <button type="button"
                                        @click="$wire.duplicateBoq({{ $boq->id }}); activeMenu = null"
                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Duplicate
                                    </button>
                                    @if($isDraft)
                                        <button type="button"
                                            @click="$wire.convertToQuotation('{{ $boq->uuid }}'); activeMenu = null"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                                            </svg>
                                            {{ __('app.convert_to_quotation') }}
                                        </button>
                                        <div class="my-1 border-t border-slate-100"></div>
                                        <button type="button"
                                            @click.stop="openDelete({{ $boq->id }}, '{{ $boq->boq_no }}')"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('app.delete') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="flex-1 px-4 pt-2 pb-3">
                        <h3 class="truncate text-base font-bold text-slate-900">{{ $boq->project?->name ?? '—' }}</h3>
                        <div class="mt-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeColor }}">
                                {{ $boq->type?->label() ?? '—' }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ $itemCount }} {{ __('app.items') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $boq->created_at?->format('M d, Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="px-4 pb-3">
                        <div class="h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full {{ $progressColor }} transition-all duration-500"
                                style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-[11px] text-slate-400">{{ $progress }}%</p>
                    </div>

                    {{-- Card Footer --}}
                    <div class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5">
                        <div class="flex items-center gap-0.5">
                            <a href="{{ route('enduser.boqs.show', $boq->uuid) }}"
                                title="{{ __('app.view') }}"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($isDraft)
                                <a href="{{ route('enduser.boqs.create') . '?draft=' . $boq->uuid }}"
                                    title="{{ __('app.edit') }}"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>

                        @if($isDraft)
                            <button
                                type="button"
                                @click.stop="$wire.convertToQuotation('{{ $boq->uuid }}')"
                                title="{{ __('app.convert_to_quotation') }}"
                                class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 disabled:opacity-60"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/>
                                </svg>
                                Convert
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
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