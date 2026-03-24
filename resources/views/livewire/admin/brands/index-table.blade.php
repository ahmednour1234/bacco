<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    @if (session('success'))
        <div class="m-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search brands by name"
                class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
            <label for="brands-per-page" class="text-sm font-medium text-slate-500">Per page</label>
            <select
                id="brands-per-page"
                wire:model.live="perPage"
                class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
                @foreach ([10, 50, 25, 5] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($brands->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
            <svg class="mb-3 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="text-sm font-medium">No brands found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                        <th class="hidden px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">Description</th>
                        <th class="hidden px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">Websites</th>
                        <th class="hidden px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell">Products</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($brands as $brand)
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $brand->name }}</td>
                            <td class="hidden max-w-xs truncate px-5 py-4 text-slate-500 md:table-cell">{{ $brand->description ?? '—' }}</td>
                            <td class="hidden px-5 py-4 lg:table-cell">
                                @if ($brand->websites->isEmpty())
                                    <span class="text-xs text-slate-400">None</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($brand->websites->take(3) as $website)
                                            <span class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $website->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 sm:table-cell">{{ $brand->products_count }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($brand->active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                                        Edit
                                    </a>
                                    <button type="button" onclick="if(!confirm('Delete brand {{ addslashes($brand->name) }}?')) return;" wire:click="delete('{{ $brand->uuid }}')" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-100">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $brands->links('livewire::tailwind') }}
        </div>
    @endif
</div>
