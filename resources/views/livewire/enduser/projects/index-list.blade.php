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

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl shadow-md sm:h-12 sm:w-12"
                     style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                    <svg class="h-5 w-5 text-white sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight sm:text-2xl">{{ __('app.my_projects') }}</h1>
                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">{{ __('app.manage_projects_desc') }}</p>
                </div>
            </div>
            <a href="{{ route('enduser.boqs.create') }}"
                class="inline-flex items-center justify-center gap-2.5 px-5 py-2.5 text-sm font-bold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5 sm:px-6 sm:py-3"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 999px; box-shadow: 0 6px 20px rgba(16,185,129,0.40); letter-spacing:0.01em;">
                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full" style="background:rgba(255,255,255,0.25);">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                {{ __('app.new_boq') }}
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3 sm:gap-4">
            {{-- Total --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #e2e8f0;">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
                <div class="flex items-center gap-3 px-3 pt-5 pb-4 sm:gap-4 sm:px-5 sm:pt-6 sm:pb-5">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl sm:h-11 sm:w-11 sm:rounded-2xl"
                         style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 4px 12px rgba(99,102,241,0.30);">
                        <svg class="h-4 w-4 text-white sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-black leading-none text-slate-900 text-xl sm:text-3xl">{{ $stats['total'] }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-slate-400 sm:text-[11px]">{{ __('app.total_projects') }}</p>
                    </div>
                </div>
            </div>

            {{-- Active --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #d1fae5;">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
                <div class="flex items-center gap-3 px-3 pt-5 pb-4 sm:gap-4 sm:px-5 sm:pt-6 sm:pb-5">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl sm:h-11 sm:w-11 sm:rounded-2xl"
                         style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); box-shadow: 0 4px 12px rgba(16,185,129,0.30);">
                        <svg class="h-4 w-4 text-white sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-black leading-none text-slate-900 text-xl sm:text-3xl">{{ $stats['active'] }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide sm:text-[11px]" style="color:#059669;">{{ __('app.active') }}</p>
                    </div>
                </div>
            </div>

            {{-- Completed --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                 style="border: 1px solid #dbeafe;">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                <div class="flex items-center gap-3 px-3 pt-5 pb-4 sm:gap-4 sm:px-5 sm:pt-6 sm:pb-5">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl sm:h-11 sm:w-11 sm:rounded-2xl"
                         style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); box-shadow: 0 4px 12px rgba(59,130,246,0.30);">
                        <svg class="h-4 w-4 text-white sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-black leading-none text-slate-900 text-xl sm:text-3xl">{{ $stats['completed'] }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-blue-500 sm:text-[11px]">{{ __('app.completed') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="relative flex-1 min-w-[160px]">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('app.search_projects') }}"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 shadow-sm">
            </div>

            <select wire:model.live="status"
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 shadow-sm">
                <option value="">{{ __('app.all_statuses') }}</option>
                @foreach(\App\Enums\ProjectStatusEnum::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="perPage"
                class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none shadow-sm">
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
            <div class="rounded-3xl bg-white" style="border: 1px solid #d1fae5; box-shadow: 0 4px 32px rgba(16,185,129,0.07);">
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
            <div class="space-y-3">
                @foreach($projects as $project)
                    @php
                        $statusColor = match($project->status->value ?? 'pending') {
                            'active'    => ['strip' => 'linear-gradient(90deg,#10b981,#34d399)', 'bg' => 'rgba(16,185,129,0.10)', 'text' => '#065f46', 'border' => 'rgba(16,185,129,0.25)'],
                            'completed' => ['strip' => 'linear-gradient(90deg,#3b82f6,#60a5fa)', 'bg' => 'rgba(59,130,246,0.10)', 'text' => '#1e40af', 'border' => 'rgba(59,130,246,0.25)'],
                            'on_hold'   => ['strip' => 'linear-gradient(90deg,#f59e0b,#fbbf24)', 'bg' => 'rgba(245,158,11,0.10)', 'text' => '#92400e', 'border' => 'rgba(245,158,11,0.25)'],
                            'cancelled' => ['strip' => 'linear-gradient(90deg,#ef4444,#f87171)', 'bg' => 'rgba(239,68,68,0.10)', 'text' => '#991b1b', 'border' => 'rgba(239,68,68,0.25)'],
                            default     => ['strip' => 'linear-gradient(90deg,#94a3b8,#cbd5e1)', 'bg' => 'rgba(100,116,139,0.08)', 'text' => '#475569', 'border' => 'rgba(100,116,139,0.20)'],
                        };
                    @endphp
                    <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                         style="border: 1px solid #e2e8f0;">
                        {{-- Color strip --}}
                        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: {{ $statusColor['strip'] }};"></div>

                        <div class="flex items-center gap-4 px-5 pt-6 pb-5">
                            {{-- Icon --}}
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                                 style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 4px 12px rgba(99,102,241,0.25);">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900 truncate">{{ $project->name }}</h3>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                          style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; border: 1px solid {{ $statusColor['border'] }};">
                                        {{ $project->status->label() }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-[11px] font-mono text-slate-400">{{ $project->project_no }}</p>
                                @if($project->description)
                                    <p class="mt-1.5 text-xs text-slate-500 line-clamp-1">{{ $project->description }}</p>
                                @endif
                                <div class="mt-2.5 flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
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

                            {{-- Actions --}}
                            <div class="flex flex-shrink-0 items-center gap-2">
                                <a href="{{ route('enduser.boqs.create.project', $project->uuid) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold text-white transition-all hover:opacity-90"
                                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 3px 10px rgba(16,185,129,0.30);">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ __('app.new_boq') }}</span>
                                </a>
                                <a href="{{ route('enduser.projects.show', $project->uuid) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:border-slate-300">
                                    {{ __('app.view_arrow') }}
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
