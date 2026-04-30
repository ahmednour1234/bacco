<div>
    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative w-full sm:w-80">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="{{ __('app.search') }}…"
                        class="h-8 w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder-slate-400 outline-none focus:ring-0"
                    >

                    @if($search !== '')
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Clear search"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Status filter --}}
            <select wire:model.live="status"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="draft">{{ __('app.status_draft') }}</option>
                <option value="submitted">{{ __('app.status_submitted') }}</option>
                <option value="completed">{{ __('app.status_completed') }}</option>
                <option value="cancelled">{{ __('app.status_cancelled') }}</option>
            </select>

            @if($hasActiveFilters)
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-500 hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('app.clear') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="w-[15%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.name') }}</th>
                        <th class="w-[22%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.project') }}</th>
                        <th class="w-[15%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.type') ?? 'Type' }}</th>
                        <th class="w-[18%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.client') }}</th>
                        <th class="w-[13%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="w-[12%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.created') }}</th>
                        <th class="w-[10%] px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($boqs as $boq)
                        <tr class="group transition hover:bg-slate-50/60">
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-slate-900 truncate block">{{ $boq->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 truncate">
                                {{ $boq->project?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $typeColors = [
                                        'tender'  => 'bg-blue-100 text-blue-700',
                                        'awarded' => 'bg-emerald-100 text-emerald-700',
                                    ];
                                    $color = $typeColors[$boq->type->value ?? ''] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $color }}">
                                    {{ $boq->type?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700 truncate">
                                {{ $boq->client?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'draft'     => 'bg-slate-100 text-slate-700',
                                        'submitted' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-red-100 text-red-600',
                                    ];
                                    $color = $statusColors[$boq->status->value ?? ''] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $color }}">
                                    {{ ucfirst($boq->status->value ?? '') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-400">
                                {{ $boq->created_at?->format('M j, Y') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('admin.boqs.show', $boq->uuid) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ __('app.show') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-slate-500">{{ __('app.no_boqs_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $boqs->links() }}
    </div>
</div>
