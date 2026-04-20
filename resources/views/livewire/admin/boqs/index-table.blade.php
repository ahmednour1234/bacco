<div>
    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('app.search') }}…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:w-64">
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
                        <th class="w-[25%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.project') }}</th>
                        <th class="w-[25%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.client') }}</th>
                        <th class="w-[15%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="w-[20%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.created') }}</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
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
