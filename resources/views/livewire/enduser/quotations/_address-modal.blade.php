{{-- ═══════════════════════════════════════════════════════════════════════════
     Delivery Address – 3-Step Wizard (Bottom Sheet on mobile, Card on desktop)
     Requires parent Livewire component to expose:
       Properties: $showAddressModal, $addressType, $deliveryStreet, $deliveryDistrict,
                   $deliveryCity, $deliveryRegion, $deliveryPostalCode, $deliveryCountry,
                   $nationalBuildingNo, $nationalStreet, $nationalDistrict, $nationalCity,
                   $nationalPostalCode, $nationalAdditionalNo
       Variable:   $confirmMethod  (e.g. 'confirmConvertToOrder' or 'submitForApproval')
══════════════════════════════════════════════════════════════════════════════ --}}

@if($showAddressModal)
<div
    class="fixed inset-0 z-50 flex flex-col items-stretch justify-end sm:items-center sm:justify-center sm:p-6"
    style="background: rgba(15,23,42,0.65); backdrop-filter: blur(8px);"
    x-data="{ step: 1 }"
    @keydown.escape.window="$wire.showAddressModal = false"
>
    {{-- ── Panel ───────────────────────────────────────────────────────────── --}}
    <div
        class="w-full sm:max-w-md bg-white sm:rounded-3xl rounded-t-[28px] shadow-2xl flex flex-col"
        style="max-height: 92vh;"
        @click.stop
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
        x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
        x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
    >
        {{-- ── Mobile drag handle ──────────────────────────────────────────── --}}
        <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0">
            <div class="h-1 w-10 rounded-full bg-slate-200"></div>
        </div>

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="shrink-0 px-5 pt-2 pb-4">
            {{-- Title row --}}
            <div class="flex items-center justify-between mb-4">
                <button
                    wire:click="$set('showAddressModal', false)"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-slate-800">{{ __('app.delivery_address') }}</span>
                <span class="text-xs font-medium text-slate-400" x-text="step + '/3'"></span>
            </div>

            {{-- Step progress bar --}}
            <div class="relative">
                {{-- Track --}}
                <div class="absolute top-4 start-0 end-0 h-0.5 bg-slate-100 mx-8"></div>
                {{-- Animated fill --}}
                <div
                    class="absolute top-4 start-8 h-0.5 bg-emerald-500 transition-all duration-500"
                    :style="'width: calc(' + ((step-1)/2) + ' * (100% - 4rem))'"
                ></div>
                {{-- Circles --}}
                <div class="relative flex justify-between px-0">
                    <template x-for="s in [1,2,3]" :key="s">
                        <div class="flex flex-col items-center gap-1.5">
                            <div
                                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-300 text-xs font-bold"
                                :class="{
                                    'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-200': step > s,
                                    'bg-white border-emerald-500 text-emerald-600 ring-4 ring-emerald-100': step === s,
                                    'bg-white border-slate-200 text-slate-400': step < s
                                }"
                            >
                                <template x-if="step > s">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="step <= s">
                                    <span x-text="s"></span>
                                </template>
                            </div>
                            <span
                                class="text-[10px] font-medium transition-colors duration-300"
                                :class="step >= s ? 'text-emerald-600' : 'text-slate-400'"
                                x-text="s === 1 ? '{{ __('app.step_type') }}' : (s === 2 ? '{{ __('app.step_details') }}' : '{{ __('app.step_confirm') }}')"
                            ></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <hr class="border-slate-100 shrink-0">

        {{-- ── Scrollable content ──────────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto">

            {{-- ┌─ Step 1: Choose Address Type ─────────────────────────────── --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-5 py-6">
                <div class="text-center mb-6">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">{{ __('app.choose_address_type') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('app.choose_address_type_hint') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Detailed Address card --}}
                    <button
                        type="button"
                        wire:click="$set('addressType', 'detailed')"
                        class="relative flex flex-col items-center gap-3 rounded-2xl border-2 p-4 text-center transition-all duration-200 {{ $addressType === 'detailed' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50/60 hover:border-slate-300' }}"
                    >
                        @if($addressType === 'detailed')
                        <div class="absolute top-2.5 end-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500">
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        @endif
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $addressType === 'detailed' ? 'bg-emerald-100' : 'bg-white border border-slate-200' }}">
                            <svg class="h-6 w-6 {{ $addressType === 'detailed' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold {{ $addressType === 'detailed' ? 'text-emerald-700' : 'text-slate-700' }}">{{ __('app.address_type_detailed') }}</div>
                            <div class="mt-0.5 text-[10px] leading-tight {{ $addressType === 'detailed' ? 'text-emerald-600' : 'text-slate-400' }}">{{ __('app.address_type_detailed_desc') }}</div>
                        </div>
                    </button>

                    {{-- National Address card --}}
                    <button
                        type="button"
                        wire:click="$set('addressType', 'national')"
                        class="relative flex flex-col items-center gap-3 rounded-2xl border-2 p-4 text-center transition-all duration-200 {{ $addressType === 'national' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50/60 hover:border-slate-300' }}"
                    >
                        @if($addressType === 'national')
                        <div class="absolute top-2.5 end-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500">
                            <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        @endif
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $addressType === 'national' ? 'bg-emerald-100' : 'bg-white border border-slate-200' }}">
                            <svg class="h-6 w-6 {{ $addressType === 'national' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold {{ $addressType === 'national' ? 'text-emerald-700' : 'text-slate-700' }}">{{ __('app.address_type_national') }}</div>
                            <div class="mt-0.5 text-[10px] leading-tight {{ $addressType === 'national' ? 'text-emerald-600' : 'text-slate-400' }}">{{ __('app.address_type_national_desc') }}</div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- ┌─ Step 2: Fill Address Details ────────────────────────────── --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-5 py-6">
                <div class="mb-5">
                    <h3 class="text-base font-bold text-slate-800">{{ __('app.fill_address_fields') }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">
                            @if($addressType === 'national'){{ __('app.address_type_national') }}@else{{ __('app.address_type_detailed') }}@endif
                        </span>
                    </p>
                </div>

                @if($addressType === 'national')
                {{-- National Address Fields --}}
                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.national_building_no') }}</label>
                        <input type="text" wire:model="nationalBuildingNo"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.national_street') }}</label>
                        <input type="text" wire:model="nationalStreet"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_district') }}</label>
                        <input type="text" wire:model="nationalDistrict"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_city') }}</label>
                        <input type="text" wire:model="nationalCity"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_postal_code') }}</label>
                        <input type="text" wire:model="nationalPostalCode"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.national_additional_no') }}</label>
                        <input type="text" wire:model="nationalAdditionalNo"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                </div>
                <div class="mt-4 flex gap-2 rounded-xl bg-blue-50 border border-blue-100 px-3 py-3">
                    <svg class="h-4 w-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs text-blue-700">{{ __('app.national_address_info') }}</span>
                </div>

                @else
                {{-- Detailed Address Fields --}}
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_street') }}</label>
                        <input type="text" wire:model="deliveryStreet" placeholder="{{ __('app.address_street_placeholder') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_district') }}</label>
                            <input type="text" wire:model="deliveryDistrict" placeholder="{{ __('app.address_district_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_city') }}</label>
                            <input type="text" wire:model="deliveryCity" placeholder="{{ __('app.address_city_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_region') }}</label>
                            <input type="text" wire:model="deliveryRegion" placeholder="{{ __('app.address_region_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('app.address_postal_code') }}</label>
                            <input type="text" wire:model="deliveryPostalCode"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100/80 outline-none transition">
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ┌─ Step 3: Review & Confirm ─────────────────────────────────── --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-5 py-6">
                <div class="text-center mb-5">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">{{ __('app.review_address') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ __('app.review_address_hint') }}</p>
                </div>

                {{-- Address type badge --}}
                <div class="mb-3 flex justify-center">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                        :class="$wire.addressType === 'national' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                        x-text="$wire.addressType === 'national' ? '{{ __('app.address_type_national') }}' : '{{ __('app.address_type_detailed') }}'"
                    ></span>
                </div>

                {{-- Summary card --}}
                <div class="rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden text-sm">
                    {{-- National address fields --}}
                    <template x-if="$wire.addressType === 'national'">
                        <div class="divide-y divide-slate-100">
                            <template x-if="$wire.nationalBuildingNo">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.national_building_no') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalBuildingNo"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalStreet">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.national_street') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalStreet"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalDistrict">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_district') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalDistrict"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalCity">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_city') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalCity"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalPostalCode">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_postal_code') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalPostalCode"></span>
                                </div>
                            </template>
                            <template x-if="$wire.nationalAdditionalNo">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.national_additional_no') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.nationalAdditionalNo"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Detailed address fields --}}
                    <template x-if="$wire.addressType !== 'national'">
                        <div class="divide-y divide-slate-100">
                            <template x-if="$wire.deliveryStreet">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_street') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryStreet"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryDistrict">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_district') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryDistrict"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryCity">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_city') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryCity"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryRegion">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_region') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryRegion"></span>
                                </div>
                            </template>
                            <template x-if="$wire.deliveryPostalCode">
                                <div class="flex justify-between px-4 py-2.5">
                                    <span class="text-xs text-slate-500">{{ __('app.address_postal_code') }}</span>
                                    <span class="text-xs font-medium text-slate-800" x-text="$wire.deliveryPostalCode"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Info note --}}
                <div class="mt-4 flex gap-2 rounded-xl bg-amber-50 border border-amber-100 px-3 py-3">
                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-xs text-amber-700">{{ __('app.order_create_info') }}</span>
                </div>
            </div>

        </div>{{-- end scrollable content --}}

        <hr class="border-slate-100 shrink-0">

        {{-- ── Footer navigation ───────────────────────────────────────────── --}}
        <div class="shrink-0 flex items-center justify-between gap-3 px-5 py-4">
            {{-- Back button --}}
            <button
                type="button"
                x-show="step > 1"
                @click="step--"
                class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
            >
                {{ __('app.back') }}
            </button>
            {{-- Cancel (shown only on step 1) --}}
            <button
                type="button"
                x-show="step === 1"
                wire:click="$set('showAddressModal', false)"
                class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 transition"
            >
                {{ __('app.cancel') }}
            </button>

            {{-- Next button --}}
            <button
                type="button"
                x-show="step < 3"
                @click="step++"
                class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition"
            >
                {{ __('app.next') }}
            </button>

            {{-- Confirm button --}}
            <button
                type="button"
                x-show="step === 3"
                wire:click="{{ $confirmMethod }}"
                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-lg transition"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 6px 20px -4px rgba(16,185,129,0.5);"
            >
                <span wire:loading.remove wire:target="{{ $confirmMethod }}">{{ __('app.confirm_and_create_order') }}</span>
                <span wire:loading wire:target="{{ $confirmMethod }}">{{ __('app.creating_order') }}</span>
            </button>
        </div>
    </div>
</div>
@endif
