<div class="w-full">
    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        {{-- Search + Filter --}}
        <div class="flex flex-1 flex-col gap-2.5 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative flex-1 sm:max-w-sm">
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition-all focus-within:border-emerald-400 focus-within:shadow-md focus-within:ring-2 focus-within:ring-emerald-100">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="{{ __('app.search_products') }}"
                        class="h-7 w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder-slate-400 outline-none focus:ring-0"
                    >
                    @if($search !== '')
                        <button type="button" wire:click="$set('search', '')"
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Status filter --}}
            <select wire:model.live="status"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="">{{ __('app.all_status') }}</option>
                <option value="pending">{{ __('app.pending_approval') }}</option>
                <option value="approved">{{ __('app.approved') }}</option>
                <option value="rejected">{{ __('app.rejected') }}</option>
            </select>
        </div>

        {{-- Add button --}}
        <a href="{{ route('supplier.products.create') }}" wire:navigate
            class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_product') }}
        </a>
    </div>

    {{-- Flash from delete --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-gradient-to-b from-slate-50 to-slate-50/60">
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.product') }}</th>
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.price') }}</th>
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.lead_time') }}</th>
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.min_qty') }}</th>
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.status') }}</th>
                        <th class="px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.added') }}</th>
                        <th class="px-5 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplierProducts as $sp)
                        @php $initial = mb_substr($sp->product?->name ?? '?', 0, 1); @endphp
                        <tr class="group transition-colors hover:bg-slate-50/70
                            @if($sp->approval_status === 'rejected') opacity-60 @endif">

                            {{-- Product --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar --}}
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-600 ring-1 ring-emerald-100">
                                        {{ $initial }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-slate-900">
                                            {{ $sp->product?->name ?? '—' }}
                                        </p>
                                        @if($sp->notes)
                                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $sp->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Price --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="text-sm font-bold text-slate-800">{{ number_format($sp->price, 2) }}</span>
                                @if($sp->currency)
                                    <span class="ms-1 text-[11px] font-medium text-slate-400">{{ $sp->currency }}</span>
                                @endif
                            </td>

                            {{-- Lead Time --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                @if($sp->lead_time_days)
                                    <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $sp->lead_time_days }} {{ __('app.days') }}
                                    </div>
                                @else
                                    <span class="text-sm text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Min Qty --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                @if($sp->min_order_qty)
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        {{ $sp->min_order_qty }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                @if($sp->approval_status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
                                        {{ __('app.pending_approval') }}
                                    </span>
                                @elseif($sp->approval_status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ __('app.approved') }}
                                    </span>
                                @elseif($sp->approval_status === 'rejected')
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            {{ __('app.rejected') }}
                                        </span>
                                        @if($sp->rejection_reason)
                                            <p class="max-w-[160px] truncate text-[11px] text-red-500" title="{{ $sp->rejection_reason }}">{{ $sp->rejection_reason }}</p>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Added --}}
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">
                                {{ $sp->created_at?->format('M j, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-0.5">
                                    {{-- Edit --}}
                                    <a href="{{ route('supplier.products.edit', $sp) }}" wire:navigate
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        title="{{ __('app.edit') }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Toggle active --}}
                                    <button wire:click="toggleActive({{ $sp->id }})"
                                        class="rounded-lg p-2 transition
                                               {{ $sp->active
                                                    ? 'text-amber-400 hover:bg-amber-50 hover:text-amber-600'
                                                    : 'text-emerald-400 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                        title="{{ $sp->active ? __('app.deactivate') : __('app.activate') }}">
                                        @if($sp->active)
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="delete({{ $sp->id }})"
                                        wire:confirm="{{ __('app.remove_product_confirm') }}"
                                        class="rounded-lg p-2 text-slate-300 transition hover:bg-red-50 hover:text-red-500"
                                        title="{{ __('app.remove') }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="flex flex-col items-center gap-3 py-20">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-semibold text-slate-600">{{ __('app.no_products_catalogue') }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ __('app.add_first_product') }}</p>
                                    </div>
                                    <a href="{{ route('supplier.products.create') }}" wire:navigate
                                        class="mt-1 inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ __('app.add_product') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3.5">
            @if($supplierProducts->hasPages())
                {{ $supplierProducts->links() }}
            @else
                <p class="text-xs text-slate-400">
                    {{ __('app.showing_range', ['from' => 1, 'to' => $supplierProducts->count(), 'total' => $supplierProducts->count()]) }}
                </p>
            @endif
        </div>
    </div>
</div>
