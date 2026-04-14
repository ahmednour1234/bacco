<div>
    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ __('app.supplier_products_approval') }}</h2>
            <p class="text-sm text-slate-500">{{ __('app.review_supplier_products') }}</p>
        </div>
        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                {{ $pendingCount }} {{ __('app.pending_review') }}
            </span>
        @endif
    </div>

    {{-- Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('app.search_product_supplier') }}"
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:w-72">
        </div>

        <select wire:model.live="status"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">{{ __('app.all_status') }}</option>
            <option value="pending">{{ __('app.pending') }}</option>
            <option value="approved">{{ __('app.approved') }}</option>
            <option value="rejected">{{ __('app.rejected') }}</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.product_name') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.supplier') }}</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.price_sar') }}</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.margin_percent') }}</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.date') }}</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $sp)
                        <tr class="group transition hover:bg-slate-50/60">
                            {{-- Product --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $sp->product?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $sp->product?->division }} · {{ $sp->product?->sku }}</p>
                            </td>

                            {{-- Supplier --}}
                            <td class="px-5 py-4">
                                <p class="text-sm font-medium text-slate-700">{{ $sp->supplier?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $sp->supplier?->email }}</p>
                            </td>

                            {{-- Price --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <p class="text-sm font-semibold text-slate-700">{{ number_format($sp->price, 2) }} <span class="text-xs font-normal text-slate-400">SAR</span></p>
                                @if($sp->product?->engineering_price > 0)
                                    <p class="text-xs text-slate-400">{{ __('app.eng_short') }} {{ number_format($sp->product->engineering_price, 2) }}</p>
                                @endif
                                @if($sp->product?->installation_price > 0)
                                    <p class="text-xs text-slate-400">{{ __('app.inst_short') }} {{ number_format($sp->product->installation_price, 2) }}</p>
                                @endif
                            </td>

                            {{-- Margin --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center" x-data="{ editing: false, margin: {{ $sp->product?->margin_percentage ?? 0 }} }">
                                <template x-if="!editing">
                                    <button @click="editing = true" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                        <span x-text="margin + '%'"></span>
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                </template>
                                <template x-if="editing">
                                    <div class="flex items-center gap-1 justify-center">
                                        <input type="number" x-model="margin" min="0" max="100" step="0.5"
                                               class="w-16 rounded-lg border border-slate-200 px-2 py-1 text-xs text-center focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                        <button @click="$wire.setMargin({{ $sp->id }}, margin); editing = false"
                                                class="rounded-lg bg-emerald-500 p-1 text-white hover:bg-emerald-600 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button @click="editing = false"
                                                class="rounded-lg bg-slate-200 p-1 text-slate-500 hover:bg-slate-300 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
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

                            {{-- Date --}}
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-400">
                                {{ $sp->created_at?->format('M j, Y') }}
                                <br>{{ $sp->created_at?->format('h:i A') }}
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
