<div
    x-data="{ toast: null, showToast(m, t='success') { this.toast={message:m,type:t}; setTimeout(()=>this.toast=null,4000); } }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>
    {{-- Toast --}}
    <div x-show="toast !== null" x-cloak x-transition
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
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

    <div class="space-y-5">

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">

            {{-- Total --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #e0e7ff;">
                <div class="flex items-center justify-between px-4 pt-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 4px 12px rgba(99,102,241,0.30);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['total'] }}</p>
                    </div>
                    <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                        <polyline points="0,30 12,20 24,25 36,10 48,18 64,8" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('app.total_projects') }}</p>
            </div>

            {{-- Active --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #d1fae5;">
                <div class="flex items-center justify-between px-4 pt-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); box-shadow: 0 4px 12px rgba(16,185,129,0.30);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['active'] }}</p>
                    </div>
                    <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                        <polyline points="0,32 10,28 22,20 32,24 44,10 64,14" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide" style="color:#059669;">{{ __('app.active') }}</p>
            </div>

            {{-- Completed --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #dbeafe;">
                <div class="flex items-center justify-between px-4 pt-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); box-shadow: 0 4px 12px rgba(59,130,246,0.30);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['completed'] }}</p>
                    </div>
                    <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                        <polyline points="0,20 14,26 26,16 38,22 50,12 64,18" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-blue-500">{{ __('app.completed') }}</p>
            </div>

            {{-- Pending --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #fef3c7;">
                <div class="flex items-center justify-between px-4 pt-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); box-shadow: 0 4px 12px rgba(245,158,11,0.30);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['pending'] }}</p>
                    </div>
                    <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                        <polyline points="0,15 10,22 20,18 30,28 44,20 56,26 64,18" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide" style="color:#d97706;">{{ __('app.pending') }}</p>
            </div>

            {{-- Cancelled --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #fee2e2;">
                <div class="flex items-center justify-between px-4 pt-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); box-shadow: 0 4px 12px rgba(239,68,68,0.30);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['cancelled'] }}</p>
                    </div>
                    <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                        <polyline points="0,10 12,18 24,14 36,24 48,20 64,30" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-red-400">{{ __('app.status_cancelled') }}</p>
            </div>

        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('app.search_projects') }}"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <select wire:model.live="status"
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">{{ __('app.all_statuses') }}</option>
                @foreach(\App\Enums\ProjectStatusEnum::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="perPage"
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none">
                <option value="5">5 / page</option>
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
            </select>
            <button type="button" wire:click="clearFilters"
                class="h-10 rounded-xl border border-red-200 bg-red-50 px-4 text-xs font-semibold text-red-500 transition hover:bg-red-100">
                {{ __('app.clear') }}
            </button>
        </div>

        {{-- Projects list --}}
        @if($projects->isEmpty())
            <div class="rounded-3xl bg-white" style="border: 1px solid #e0e7ff; box-shadow: 0 4px 32px rgba(99,102,241,0.07);">
                <div class="flex flex-col items-center py-20 px-10">
                    <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-3xl"
                         style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 12px 32px rgba(99,102,241,0.30);">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-black text-slate-800">{{ __('app.no_projects_yet') }}</h3>
                    <p class="mt-2 text-sm font-medium text-slate-400">{{ __('app.create_boq_first_project') }}</p>
                    <a href="{{ route('enduser.boqs.create') }}"
                        class="mt-8 inline-flex items-center gap-2.5 px-8 py-3.5 text-sm font-bold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5"
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 999px; box-shadow: 0 8px 24px rgba(16,185,129,0.40);">
                        <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full" style="background:rgba(255,255,255,0.25);">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        {{ __('app.new_boq') }}
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm" style="border: 1px solid #e2e8f0;">
                @foreach($projects as $project)
                    @php
                        $statusColor = match($project->status->value ?? 'pending') {
                            'active'    => ['bg' => 'rgba(16,185,129,0.10)',  'text' => '#065f46', 'border' => 'rgba(16,185,129,0.25)'],
                            'completed' => ['bg' => 'rgba(59,130,246,0.10)',  'text' => '#1e40af', 'border' => 'rgba(59,130,246,0.25)'],
                            'on_hold'   => ['bg' => 'rgba(245,158,11,0.10)',  'text' => '#92400e', 'border' => 'rgba(245,158,11,0.25)'],
                            'cancelled' => ['bg' => 'rgba(239,68,68,0.10)',   'text' => '#991b1b', 'border' => 'rgba(239,68,68,0.25)'],
                            default     => ['bg' => 'rgba(99,102,241,0.10)',  'text' => '#3730a3', 'border' => 'rgba(99,102,241,0.25)'],
                        };
                    @endphp
                    <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50 last:border-b-0">

                        {{-- Icon --}}
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                             style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 4px 10px rgba(99,102,241,0.25);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-bold text-slate-900">{{ $project->name }}</h3>
                            <p class="text-[11px] font-mono text-slate-400">{{ $project->project_no }}</p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                                    {{ $project->boqs_count }} {{ __('app.boq_count') }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $project->quotation_requests_count }} {{ __('app.quotation_count') }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    {{ $project->orders_count }} {{ __('app.order_count') }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $project->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Status badge --}}
                        <span class="hidden flex-shrink-0 rounded-full px-3 py-1 text-[11px] font-bold sm:inline-flex"
                              style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; border: 1px solid {{ $statusColor['border'] }};">
                            {{ $project->status->label() }}
                        </span>

                        {{-- Actions --}}
                        <div class="flex flex-shrink-0 items-center gap-1.5">
                            <a href="{{ route('enduser.boqs.create.project', $project->uuid) }}"
                                class="flex h-9 w-9 items-center justify-center rounded-xl text-white transition hover:opacity-90"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 3px 10px rgba(16,185,129,0.30);"
                                title="{{ __('app.new_boq') }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                            <a href="{{ route('enduser.projects.show', $project->uuid) }}"
                                class="inline-flex h-9 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:border-slate-300">
                                {{ __('app.view') }}
                                <svg class="h-3.5 w-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-2">
                {{ $projects->links() }}
            </div>
        @endif

    </div>
</div>
