<div
    x-data="{ toast: null, showToast(m, t='success') { this.toast={message:m,type:t}; setTimeout(()=>this.toast=null,4000); } }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>
    {{-- Toast --}}
    <div x-show="toast !== null" x-cloak
        x-transition class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type==='success',
            'bg-red-50 text-red-700 border border-red-200': toast?.type==='error',
            'bg-amber-50 text-amber-700 border border-amber-200': toast?.type==='warning',
        }">
        <span x-text="toast?.message"></span>
        <button @click="toast=null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="space-y-6">

        {{-- Header with New BOQ button --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">My Projects</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your projects, BOQs, quotations and orders.</p>
            </div>
            <a href="{{ route('enduser.boqs.create') }}"
                class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New BOQ
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Projects</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">Active</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-blue-600">Completed</p>
                <p class="mt-2 text-3xl font-bold text-blue-700">{{ $stats['completed'] }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search projects…"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>

                <select wire:model.live="status"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\ProjectStatusEnum::cases() as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </select>

                <select wire:model.live="perPage"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none">
                    <option value="5">5 / page</option>
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                </select>

                <button type="button" wire:click="clearFilters"
                    class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-500 transition hover:bg-slate-100">
                    Clear
                </button>
            </div>
        </div>

        {{-- Projects list --}}
        @if($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="mt-4 text-sm font-medium text-slate-500">No projects yet</p>
                <p class="mt-1 text-xs text-slate-400">Create a BOQ to start your first project.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($projects as $project)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-sm font-bold text-slate-900">{{ $project->name }}</h3>
                                    @php
                                        $pStatusBadge = match($project->status->value ?? 'pending') {
                                            'active'     => 'bg-emerald-100 text-emerald-700',
                                            'completed'  => 'bg-blue-100 text-blue-700',
                                            'on_hold'    => 'bg-amber-100 text-amber-700',
                                            'cancelled'  => 'bg-red-100 text-red-700',
                                            default      => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pStatusBadge }}">
                                        {{ $project->status->label() }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $project->project_no }}</p>
                                @if($project->description)
                                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $project->description }}</p>
                                @endif

                                <div class="mt-3 flex items-center gap-4 text-xs text-slate-400">
                                    <span>{{ $project->boqs_count }} BOQ(s)</span>
                                    <span>{{ $project->quotation_requests_count }} Quotation(s)</span>
                                    <span>{{ $project->orders_count }} Order(s)</span>
                                    <span>Created {{ $project->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 ml-4">
                                <a href="{{ route('enduser.boqs.create.project', $project->uuid) }}"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    New BOQ
                                </a>
                                <a href="{{ route('enduser.projects.show', $project->uuid) }}"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                    View &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif

    </div>
</div>
