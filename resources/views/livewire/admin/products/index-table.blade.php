<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

    @if (session('success'))
        <div class="m-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar --}}
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
                placeholder="{{ __('app.search_products') }}"
                class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
            <label for="products-per-page" class="text-sm font-medium text-slate-500">{{ __('app.per_page') }}</label>
            <select
                id="products-per-page"
                wire:model.live="perPage"
                class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($products->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
            <svg class="mb-3 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="text-sm font-medium">{{ __('app.no_products_found') }}</p>
            @if ($search)
                <p class="mt-1 text-xs">{{ __('app.try_adjusting_search') }}</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.product_name') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell">{{ __('app.division') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">{{ __('app.brand') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">{{ __('app.category') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">{{ __('app.model_type') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 xl:table-cell">{{ __('app.unit') }}</th>
                        <th class="px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.price_sar') }}</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                @if ($product->sku)
                                    <p class="mt-0.5 text-xs text-slate-400">{{ __('app.sku_label') }} {{ $product->sku }}</p>
                                @endif
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 sm:table-cell">
                                {{ $product->division ?? '—' }}
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 md:table-cell">
                                {{ $product->brand?->name ?? '—' }}
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 lg:table-cell">
                                {{ $product->category?->name ?? '—' }}
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 lg:table-cell">
                                {{ $product->model_type ?? '—' }}
                            </td>
                            <td class="hidden px-5 py-4 text-slate-600 xl:table-cell">
                                {{ $product->unit?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-end font-medium text-slate-800">
                                {{ $product->unit_price ? number_format((float) $product->unit_price, 2) : '—' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($product->active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ __('app.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> {{ __('app.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-end">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                                        {{ __('app.edit') }}
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="delete('{{ $product->uuid }}')"
                                        wire:confirm="{{ __('app.delete_product') }} '{{ addslashes($product->name) }}'? {{ __('app.this_cannot_be_undone') }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-100">
                                        {{ __('app.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $products->links('livewire::tailwind') }}
        </div>
    @endif
</div>
