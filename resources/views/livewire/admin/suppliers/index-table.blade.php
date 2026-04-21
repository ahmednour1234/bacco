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
                        placeholder="{{ __('app.search_suppliers') }}"
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
            <select wire:model.live="statusFilter"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="active">{{ __('app.status_active') }}</option>
                <option value="inactive">{{ __('app.inactive') }}</option>
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

        <a href="{{ route('admin.suppliers.create') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_supplier') }}
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="w-[16%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.supplier_name') }}</th>
                        <th class="w-[12%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.company') }}</th>
                        <th class="w-[20%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.contact') }}</th>
                        <th class="w-[8%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.products') }}</th>
                        <th class="w-[10%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="w-[10%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.activity') }}</th>
                        <th class="w-[12%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.created') }}</th>
                        <th class="w-[12%] px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                        <tr class="group transition hover:bg-slate-50/60">
                            {{-- Name --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-600">
                                        {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $supplier->name }}</p>
                                        @if($supplier->supplierProfile?->division)
                                            <p class="text-xs text-slate-400">{{ $supplier->supplierProfile->division }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Company --}}
                            <td class="px-5 py-4">
                                <span class="text-sm text-slate-700 truncate block">{{ $supplier->supplierProfile?->company_name ?: '—' }}</span>
                            </td>

                            {{-- Contact --}}
                            <td class="px-5 py-4">
                                <p class="text-sm text-slate-700">{{ $supplier->email }}</p>
                                @if($supplier->phone)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $supplier->phone }}</p>
                                @endif
                            </td>

                            {{-- Products --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $supplier->supplier_products_count }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($supplier->active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('app.status_active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ __('app.inactive') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Activity --}}
                            <td class="px-5 py-4 text-xs text-slate-400">
                                {{ $supplier->updated_at?->diffForHumans() }}
                            </td>

                            {{-- Created --}}
                            <td class="px-5 py-4 text-xs text-slate-400">
                                {{ $supplier->created_at?->format('M j, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View --}}
                                    <a href="{{ route('admin.suppliers.show', $supplier->uuid) }}" wire:navigate
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="View">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.suppliers.edit', $supplier->uuid) }}" wire:navigate
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Toggle status --}}
                                    <form method="POST" action="{{ route('admin.suppliers.toggle-status', $supplier->uuid) }}">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg p-1.5 transition
                                                   {{ $supplier->active
                                                        ? 'text-red-400 hover:bg-red-50 hover:text-red-600'
                                                        : 'text-emerald-500 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                            title="{{ $supplier->active ? __('app.block') : __('app.activate') }}">
                                            @if($supplier->active)
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-500">{{ __('app.no_suppliers_found') }}</p>
                                    @if($hasActiveFilters)
                                        <button wire:click="resetFilters" class="text-xs text-emerald-600 underline hover:text-emerald-700">{{ __('app.clear_filters') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($suppliers->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-400">
                        {{ __('app.showing') }} {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} {{ __('app.of') }} {{ $suppliers->total() }} {{ __('app.suppliers') }}
                    </p>
                    {{ $suppliers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
