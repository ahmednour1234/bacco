<div class="space-y-6">

    {{-- ══════════════════════════════════════════════════
         TAB BAR
    ══════════════════════════════════════════════════ --}}
    <div class="mb-6 flex items-center gap-1 border-b border-slate-200">
        <button
            wire:click="$set('activeTab', 'manual')"
            class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors
                   {{ $activeTab === 'manual'
                        ? 'border-b-2 border-emerald-500 text-emerald-600'
                        : 'text-slate-500 hover:text-slate-700' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Manual Entry
        </button>
        @if(!$isEditing)
        <button
            wire:click="$set('activeTab', 'ai')"
            class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors
                   {{ $activeTab === 'ai'
                        ? 'border-b-2 border-emerald-500 text-emerald-600'
                        : 'text-slate-500 hover:text-slate-700' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            AI-Assisted Import
        </button>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════
         MANUAL ENTRY TAB
    ══════════════════════════════════════════════════ --}}
    @if ($activeTab === 'manual')

    <form wire:submit="save" class="space-y-6">

        {{-- ── Product Details ────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 flex items-center gap-2 text-base font-semibold text-slate-800">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                Product Details
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- Product Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model.blur="name"
                           placeholder="e.g. Industrial Pressure Sensor X-100"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  placeholder-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Division --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Division</label>
                    <select wire:model="division"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                   transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select Division</option>
                        @foreach($divisions as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('division') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Brand --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Brand</label>
                    <select wire:model="brand_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                   transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Brand name</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Classification --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Classification</label>
                    <select wire:model="category_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                   transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Product category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Type / Model --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Type / Model</label>
                    <input type="text" wire:model.blur="model_type" placeholder="Model number"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  placeholder-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('model_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Unit --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Unit</label>
                    <select wire:model="unit_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                   transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Active --}}
                <div class="flex items-center">
                    <label class="inline-flex cursor-pointer items-center gap-2.5">
                        <input type="checkbox" wire:model="active"
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-medium text-slate-700">Active product</span>
                    </label>
                </div>

            </div>
        </div>

        {{-- ── Pricing & Margin ────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 flex items-center gap-2 text-base font-semibold text-slate-800">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Pricing &amp; Margin
            </h2>

            <div class="grid grid-cols-2 gap-5 sm:grid-cols-4">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Unit Price (SAR)</label>
                    <input type="number" wire:model.live="unit_price" step="0.01" min="0"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('unit_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Engineering Price (SAR)</label>
                    <input type="number" wire:model.live="engineering_price" step="0.01" min="0"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('engineering_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Installation Price (SAR)</label>
                    <input type="number" wire:model.live="installation_price" step="0.01" min="0"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('installation_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Margin (%)</label>
                    <input type="number" wire:model.live="margin_percentage" step="0.01" min="0" max="100"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('margin_percentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Estimated Final Price banner --}}
            <div class="mt-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Estimated Final Price</p>
                        <p class="text-xs text-emerald-600">Total = Unit + Engineering + Installation + Margin</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-emerald-600">
                        {{ number_format($finalPrice, 2) }}
                    </span>
                    <span class="ml-1 text-sm font-medium text-emerald-600">SAR</span>
                    <p class="text-xs text-emerald-500">VAT inclusive</p>
                </div>
            </div>
        </div>

        {{-- ── Availability ────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 flex items-center gap-2 text-base font-semibold text-slate-800">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Availability
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Lead Time (days)</label>
                    <input type="number" wire:model="leadTimeDays" min="0" placeholder="e.g. 7"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('leadTimeDays') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Minimum Order Qty</label>
                    <input type="number" wire:model="minOrderQty" min="0" placeholder="e.g. 10"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                  transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('minOrderQty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- ── Documentation ───────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-5 flex items-center gap-2 text-base font-semibold text-slate-800">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                Documentation
            </h2>

            {{-- Technical Specifications --}}
            <div class="mb-5">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Technical Specifications</label>
                <textarea wire:model.blur="description" rows="5"
                          placeholder="Enter detailed product specifications and features…"
                          class="w-full resize-y rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                 placeholder-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div class="mb-5">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Supplier Notes</label>
                <textarea wire:model.blur="notes" rows="3"
                          placeholder="Optional notes about availability, packaging, lead time…"
                          class="w-full resize-y rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                 placeholder-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Datasheet Upload --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Datasheet Upload</label>

                @if ($existingDatasheet)
                    <div class="mb-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="flex-1 truncate text-sm text-slate-700">{{ basename($existingDatasheet) }}</span>
                        <button type="button" wire:click="removeDatasheet"
                                class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Remove</button>
                    </div>
                @endif

                @if ($datasheet)
                    <div class="mb-3 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="flex-1 truncate text-sm text-emerald-700">{{ $datasheet->getClientOriginalName() }}</span>
                        <button type="button" wire:click="$set('datasheet', null)"
                                class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Remove</button>
                    </div>
                @endif

                <label for="datasheet-input"
                       class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed
                              border-slate-300 bg-slate-50 px-6 py-10 text-center transition
                              hover:border-emerald-400 hover:bg-emerald-50">
                    <svg class="mb-3 h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-700">Click to upload or drag and drop</p>
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, DOCX up to 10 MB</p>
                    <input id="datasheet-input" type="file" wire:model="datasheet"
                           accept=".pdf,.doc,.docx,.xls,.xlsx" class="hidden">
                </label>
                @error('datasheet') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ── Form actions ────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5
                       text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700
                       disabled:opacity-60">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $isEditing ? 'Save Changes' : 'Add to Catalogue' }}
            </button>
            <a href="{{ route('supplier.products.index') }}" wire:navigate
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-6 py-2.5
                      text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                Cancel
            </a>
        </div>

    </form>

    @endif

    {{-- ══════════════════════════════════════════════════
         AI-ASSISTED IMPORT TAB
    ══════════════════════════════════════════════════ --}}
    @if ($activeTab === 'ai')

    <div class="space-y-6">

        {{-- Two-column layout: config + upload --}}
        <div class="grid grid-cols-2 gap-2 max-w-4xl">

            {{-- LEFT: Pricing Context --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden divide-y divide-slate-100">

                <div class="flex items-center gap-2 px-4 py-3">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-slate-800">Pricing Context</h3>
                </div>

                <div class="px-4 py-3">
                    <p class="mb-2 text-xs font-medium text-slate-600">What does this document represent?</p>
                    <div class="space-y-2.5">
                        @foreach ([
                            'vendor' => 'Vendor quotation',
                            'client' => 'Selling price to client',
                            'mixed'  => 'Not sure / Mixed',
                        ] as $val => $label)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 transition
                                          {{ $aiPriceContext === $val
                                               ? 'border-emerald-500 bg-emerald-50'
                                               : 'border-slate-200 hover:border-emerald-200' }}">
                                <input type="radio" wire:model.live="aiPriceContext" value="{{ $val }}"
                                       class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="px-4 py-3">
                    <p class="mb-2 text-xs font-medium text-slate-600">Does the price include engineering?</p>
                    <div class="flex gap-1.5">
                        @foreach (['yes' => 'Yes', 'no' => 'No', 'not_sure' => 'Not sure'] as $val => $label)
                            <button type="button" wire:click="$set('aiIncludesEng', '{{ $val }}')"
                                    class="flex-1 rounded-lg border px-2 py-2 text-xs font-medium transition
                                           {{ $aiIncludesEng === $val
                                                ? 'border-emerald-500 bg-white text-slate-800'
                                                : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-300 hover:text-slate-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="px-4 py-3">
                    <p class="mb-2 text-xs font-medium text-slate-600">Does the price include installation?</p>
                    <div class="flex gap-1.5">
                        @foreach (['yes' => 'Yes', 'no' => 'No', 'not_sure' => 'Not sure'] as $val => $label)
                            <button type="button" wire:click="$set('aiIncludesInst', '{{ $val }}')"
                                    class="flex-1 rounded-lg border px-2 py-2 text-xs font-medium transition
                                           {{ $aiIncludesInst === $val
                                                ? 'border-emerald-500 bg-white text-slate-800'
                                                : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-300 hover:text-slate-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="px-4 py-3">
                    <label class="mb-1.5 block text-xs font-medium text-slate-600">Profit / Margin Handling</label>
                    <select wire:model="aiMarginHandling"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800
                                   transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="auto_20">Apply automatically (20%)</option>
                        <option value="auto_15">Apply automatically (15%)</option>
                        <option value="keep">Keep original price</option>
                        <option value="override">Override manually</option>
                    </select>
                </div>

                <div class="px-4 py-3">
                    <label class="mb-1.5 block text-xs font-medium text-slate-600">Document Currency</label>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <span class="text-base leading-none">🇸🇦</span>
                        <span class="text-xs font-semibold text-slate-800">SAR</span>
                        <span class="text-xs text-slate-500">— Saudi Riyal</span>
                    </div>
                    <input type="hidden" wire:model="aiCurrency" value="SAR">
                </div>

            </div>

            {{-- RIGHT: Upload zone + Paste section stacked --}}
            <div class="flex flex-col gap-4">

                <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed
                              border-emerald-300 bg-white px-6 py-10 text-center transition
                              hover:border-emerald-400 hover:bg-emerald-50/40">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                        <svg class="h-8 w-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="mb-1.5 text-base font-bold text-slate-900">Upload document for AI analysis</p>
                    <p class="mb-6 text-sm text-slate-500 leading-relaxed">
                        Support for PDF, Excel (.xlsx, .csv), Word (.docx), and<br>high-resolution images (JPG, PNG).
                    </p>
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm">Select File</span>
                        <span class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm">Connect Cloud Drive</span>
                    </div>
                    <input type="file" wire:model="aiFile" class="hidden"
                           accept=".pdf,.xlsx,.csv,.docx,.jpg,.jpeg,.png">
                </label>

                @if ($aiFile)
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="flex-1 truncate text-sm text-emerald-700">{{ $aiFile->getClientOriginalName() }}</span>
                        <button type="button" wire:click="$set('aiFile', null)"
                                class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Remove</button>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                        <p class="text-sm font-semibold text-slate-800">Or paste product list details</p>
                        <button type="button"
                                x-data
                                x-on:click="navigator.clipboard.readText().then(t => $wire.set('aiPastedText', t))"
                                class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Paste from clipboard
                        </button>
                    </div>
                    <div class="p-5">
                        <textarea
                            wire:model="aiPastedText"
                            rows="6"
                            placeholder="Example: 5 units of Cisco Catalyst 9200 Switch at 1200 SAR each…"
                            class="w-full resize-none rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800
                                   placeholder-slate-400 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        ></textarea>
                        @error('aiPastedText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end border-t border-slate-100 px-5 py-3.5">
                        <button
                            type="button"
                            wire:click="analyzeText"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5
                                   text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-60">
                            <svg wire:loading wire:target="analyzeText" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg wire:loading.remove wire:target="analyzeText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Analyze Text
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Extraction Preview --}}
        @if (count($aiExtractedProducts) > 0)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Extraction Preview</span>
                    <span class="text-sm font-semibold text-slate-800">Review Extracted Products</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                        {{ count($aiExtractedProducts) }} products identified
                    </span>
                    <button type="button" wire:click="analyzeText" wire:loading.attr="disabled"
                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
                            title="Re-analyze">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3 text-left whitespace-nowrap">Product Name</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Division</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Brand</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Category</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Model / Type</th>
                            <th class="px-3 py-3 text-right whitespace-nowrap">Price</th>
                            <th class="px-3 py-3 text-right whitespace-nowrap">Eng.</th>
                            <th class="px-3 py-3 text-right whitespace-nowrap">Inst.</th>
                            <th class="px-3 py-3 text-center whitespace-nowrap">Margin %</th>
                            <th class="px-3 py-3 text-right whitespace-nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($aiExtractedProducts as $idx => $item)
                            <tr class="align-top hover:bg-slate-50/70">

                                <td class="px-3 py-2 min-w-[160px]">
                                    <input type="text"
                                           wire:model.blur="aiExtractedProducts.{{ $idx }}.name"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-800
                                                  focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                </td>

                                <td class="px-3 py-2 min-w-[110px]">
                                    <select wire:model.live="aiExtractedProducts.{{ $idx }}.division"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-800
                                                   focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                        <option value="">— none —</option>
                                        @foreach (\App\Livewire\Supplier\Products\Form::DIVISIONS as $div)
                                            <option value="{{ $div }}" @selected(($item['division'] ?? '') === $div)>{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2 min-w-[130px]">
                                    <select wire:model.live="aiExtractedProducts.{{ $idx }}.brand_id"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-800
                                                   focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                        <option value="">— none —</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" @selected(($item['brand_id'] ?? '') == $brand->id)>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @if (!empty($item['brand']))
                                        <p class="mt-0.5 text-[10px] text-slate-400 truncate" title="{{ $item['brand'] }}">AI: {{ $item['brand'] }}</p>
                                    @endif
                                </td>

                                <td class="px-3 py-2 min-w-[130px]">
                                    <select wire:model.live="aiExtractedProducts.{{ $idx }}.category_id"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-800
                                                   focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                        <option value="">— none —</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(($item['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-3 py-2 min-w-[110px]">
                                    <input type="text"
                                           wire:model.blur="aiExtractedProducts.{{ $idx }}.model_type"
                                           placeholder="Model"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-800
                                                  focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-100">
                                </td>

                                <td class="px-3 py-2 text-right text-xs font-mono text-slate-700 whitespace-nowrap">
                                    {{ number_format($item['unit_price'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs font-mono text-slate-700 whitespace-nowrap">
                                    {{ number_format($item['engineering_price'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs font-mono text-slate-700 whitespace-nowrap">
                                    {{ number_format($item['installation_price'] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                        {{ $item['margin_percentage'] ?? 20 }}%
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right text-xs font-semibold text-slate-800 whitespace-nowrap">
                                    {{ number_format($item['total'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50 font-semibold">
                            <td colspan="9" class="px-3 py-3 text-right text-sm text-slate-700 uppercase tracking-wide">
                                Total Est. Inventory Value (SAR)
                            </td>
                            <td class="px-3 py-3 text-right text-base font-bold text-emerald-600">
                                {{ number_format(array_sum(array_column($aiExtractedProducts, 'total')), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 px-5 py-4">
                <span class="flex items-center gap-1.5 text-xs text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    AI accuracy for this document: {{ $aiAccuracy }}%
                </span>
                <div class="flex gap-3">
                    <button type="button" wire:click="$set('aiExtractedProducts', [])"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmImport"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors disabled:opacity-60">
                        <svg wire:loading wire:target="confirmImport" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Confirm &amp; Import to Catalogue
                        <svg wire:loading.remove wire:target="confirmImport" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>

    @endif
</div>

