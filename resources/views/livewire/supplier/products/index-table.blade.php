<div>
    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search products…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:w-64">
            </div>

            {{-- Status filter --}}
            <select wire:model.live="status"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="">All Status</option>
                <option value="pending">Pending Approval</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <a href="{{ route('supplier.products.create') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
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

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lead Time</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Min. Qty</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Added</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplierProducts as $sp)
                        <tr class="group transition hover:bg-slate-50/60">
                            {{-- Product --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $sp->product?->name ?? '—' }}</p>
                                @if($sp->notes)
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $sp->notes }}</p>
                                @endif
                            </td>

                            {{-- Price --}}
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">
                                {{ number_format($sp->price, 2) }}
                                @if($sp->currency)
                                    <span class="text-xs font-normal text-slate-400">{{ $sp->currency }}</span>
                                @endif
                            </td>

                            {{-- Lead Time --}}
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $sp->lead_time_days ? $sp->lead_time_days . ' days' : '—' }}
                            </td>

                            {{-- Min Qty --}}
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $sp->min_order_qty ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                @if($sp->approval_status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Pending Approval
                                    </span>
                                @elseif($sp->approval_status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Approved
                                    </span>
                                @elseif($sp->approval_status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Rejected
                                    </span>
                                    @if($sp->rejection_reason)
                                        <p class="mt-1 text-xs text-red-500 max-w-[150px] truncate" title="{{ $sp->rejection_reason }}">{{ $sp->rejection_reason }}</p>
                                    @endif
                                @endif
                            </td>

                            {{-- Added --}}
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">
                                {{ $sp->created_at?->format('M j, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <a href="{{ route('supplier.products.edit', $sp) }}" wire:navigate
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Toggle active --}}
                                    <button wire:click="toggleActive({{ $sp->id }})"
                                        class="rounded-lg p-1.5 transition
                                               {{ $sp->active
                                                    ? 'text-amber-500 hover:bg-amber-50 hover:text-amber-600'
                                                    : 'text-emerald-500 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                        title="{{ $sp->active ? 'Deactivate' : 'Activate' }}">
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
                                        wire:confirm="Remove this product from your catalogue?"
                                        class="rounded-lg p-1.5 text-red-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-500">No products in your catalogue yet</p>
                                    <a href="{{ route('supplier.products.create') }}" wire:navigate
                                        class="text-xs text-emerald-600 underline hover:text-emerald-700">Add your first product</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($supplierProducts->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-400">
                        Showing {{ $supplierProducts->firstItem() }}–{{ $supplierProducts->lastItem() }} of {{ $supplierProducts->total() }} products
                    </p>
                    {{ $supplierProducts->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
