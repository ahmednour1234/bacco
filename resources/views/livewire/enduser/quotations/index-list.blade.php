<div x-data="{ filterOpen: false }">
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Active</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Completed</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['completed'] }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Recent Quotations</h3>
            <p class="mt-0.5 text-xs text-slate-400">{{ $quotations->total() }} {{ \Illuminate\Support\Str::plural('quotation', $quotations->total()) }} found</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-72">
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search quotations" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>

            <label class="text-sm font-medium text-slate-500">Per page</label>
            <select wire:model.live="perPage" class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @foreach ([10, 50, 25, 5] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>

            <button type="button" @click="filterOpen = !filterOpen" class="relative inline-flex h-11 items-center gap-2 rounded-2xl border px-4 text-sm font-medium transition" :class="filterOpen ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700'">
                Filter
                @if ($hasActiveFilters)
                    <span class="absolute -right-1.5 -top-1.5 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                @endif
            </button>

            <a href="{{ route('enduser.quotations.create') }}" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-emerald-500 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Quotation
            </a>
        </div>
    </div>

    <div x-show="filterOpen" x-cloak class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Quotation No</label>
                <input type="text" wire:model.live.debounce.300ms="quotation_no" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select wire:model.live="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $statusItem)
                        <option value="{{ $statusItem->value }}">{{ $statusItem->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Created From</label>
                <input type="date" wire:model.live="created_from" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Created To</label>
                <input type="date" wire:model.live="created_to" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Updated From</label>
                <input type="date" wire:model.live="updated_from" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Updated To</label>
                <input type="date" wire:model.live="updated_to" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
        </div>

        <div class="mt-4 border-t border-slate-100 pt-4">
            <button wire:click="clearFilters" type="button" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @if ($quotations->isEmpty())
            <div class="px-6 py-16 text-center text-sm text-slate-500">No quotations found for the selected filters.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Quotation No</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Source</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Updated</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($quotations as $quotation)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-5 py-4 font-medium text-slate-900">{{ $quotation->quotation_no }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $quotation->status->label() }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $quotation->source_type?->value ?? 'manual' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $quotation->created_at?->format('Y-m-d') }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $quotation->updated_at?->format('Y-m-d') }}</td>
                                <td class="max-w-sm truncate px-5 py-4 text-slate-500">{{ $quotation->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4">
                {{ $quotations->links('livewire::tailwind') }}
            </div>
        @endif
    </div>
</div>
