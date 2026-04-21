<div>
    <div class="mb-6 overflow-hidden rounded-[28px] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.12),_transparent_28%),linear-gradient(135deg,_#ffffff_0%,_#f8fbff_55%,_#f2f6fb_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Supplier Review Queue
                </div>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ __('app.supplier_products_approval') }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ __('app.review_supplier_products') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-white/70 bg-white/90 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">All</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalCount }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/90 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-600">Pending</p>
                    <p class="mt-2 text-2xl font-bold text-amber-700">{{ $pendingCount }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-600">Approved</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $approvedCount }}</p>
                </div>
                <div class="rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-600">Rejected</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">{{ $rejectedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-5 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative w-full sm:max-w-md">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('app.search_product_supplier') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100">
                </div>

                <select wire:model.live="status"
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    <option value="">{{ __('app.all_status') }}</option>
                    <option value="pending">{{ __('app.pending') }}</option>
                    <option value="approved">{{ __('app.approved') }}</option>
                    <option value="rejected">{{ __('app.rejected') }}</option>
                </select>
            </div>

            <div class="flex items-center gap-2 self-start lg:self-auto">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    {{ $products->total() }} records
                </span>
                @if($pendingCount > 0)
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $pendingCount }} {{ __('app.pending_review') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Review Submitted Products</h3>
                    <p class="mt-1 text-xs text-slate-500">Approve or reject supplier-submitted catalogue items from one place.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="w-[28%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.product_name') }}</th>
                        <th class="w-[18%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.supplier') }}</th>
                        <th class="w-[16%] px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.price_sar') }}</th>
                        <th class="w-[13%] px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="w-[11%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.date') }}</th>
                        <th class="w-[14%] px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $sp)
                        <tr class="group transition hover:bg-emerald-50/30">
                            {{-- Product --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $sp->product?->name ?? '—' }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                    @if($sp->product?->division)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5">{{ $sp->product->division }}</span>
                                    @endif
                                    @if($sp->product?->sku)
                                        <span>{{ $sp->product->sku }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Supplier --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-medium text-slate-700">{{ $sp->supplier?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $sp->supplier?->email }}</p>
                            </td>

                            {{-- Price --}}
                            <td class="whitespace-nowrap px-5 py-4 text-end">
                                <p class="text-sm font-semibold text-slate-700">{{ number_format($sp->price, 2) }} <span class="text-xs font-normal text-slate-400">SAR</span></p>
                                @if($sp->product?->engineering_price > 0)
                                    <p class="text-xs text-slate-400">{{ __('app.eng_short') }} {{ number_format($sp->product->engineering_price, 2) }}</p>
                                @endif
                                @if($sp->product?->installation_price > 0)
                                    <p class="text-xs text-slate-400">{{ __('app.inst_short') }} {{ number_format($sp->product->installation_price, 2) }}</p>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if($sp->approval_status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ __('app.pending') }}
                                    </span>
                                @elseif($sp->approval_status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('app.approved') }}
                                    </span>
                                @elseif($sp->approval_status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ __('app.rejected') }}
                                    </span>
                                @endif
                            </td>
                                        <td colspan="6" class="px-5 py-20 text-center">
                                            <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-base font-semibold text-slate-700">{{ __('app.no_products_review') }}</p>
                                                    <p class="mt-1 text-sm text-slate-500">No supplier products match the current filters. Try switching status or clearing the search term.</p>
                                                </div>
                                                <div class="flex flex-wrap items-center justify-center gap-2 text-xs text-slate-500">
                                                    <span class="rounded-full bg-slate-100 px-3 py-1">Status: {{ $status === '' ? __('app.all_status') : ucfirst($status) }}</span>
                                                    @if($search !== '')
                                                        <span class="rounded-full bg-slate-100 px-3 py-1">Search: {{ $search }}</span>
                                                    @endif
                                                </div>
                                            </div>
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($sp->approval_status === 'pending')
                                        <button wire:click="approve({{ $sp->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ __('app.approve') }}
                                        </button>
                                        <button wire:click="openRejectModal({{ $sp->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            {{ __('app.reject') }}
                                        </button>
                                    @elseif($sp->approval_status === 'rejected')
                                        <button wire:click="approve({{ $sp->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ __('app.approve') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">{{ __('app.approved') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-500">{{ __('app.no_products_review') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- Rejection Reason Modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showRejectModal', false)">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">{{ __('app.reject_product') }}</h3>
            <p class="mb-4 text-sm text-slate-500">{{ __('app.provide_rejection_reason') }}</p>

            <textarea wire:model="rejectionReason" rows="4"
                      placeholder="{{ __('app.rejection_reason_placeholder') }}"
                      class="w-full resize-none rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                             placeholder-slate-400 transition focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100"></textarea>
            @error('rejectionReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-5 flex items-center justify-end gap-3">
                <button wire:click="$set('showRejectModal', false)"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                    {{ __('app.cancel') }}
                </button>
                <button wire:click="confirmReject"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition">
                    {{ __('app.confirm_reject') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
