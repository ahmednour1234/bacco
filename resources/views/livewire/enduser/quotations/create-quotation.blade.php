<div
    x-data="{
        step: 1,
        toast: null,
        selectedFileName: null,
        selectedFileSize: null,
        tempUploading: false,
        uploadReady: false,
        submitting: false,
        progressPct: 0,

        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        },

        startUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.selectedFileName = file.name;
            this.selectedFileSize = (file.size / 1024).toFixed(1);
            this.tempUploading = true;
            this.uploadReady = false;

            $wire.upload(
                'boqFile',
                file,
                () => {
                    this.tempUploading = false;
                    this.uploadReady = true;
                },
                () => {
                    this.tempUploading = false;
                    this.uploadReady = false;
                    this.selectedFileName = null;
                    this.selectedFileSize = null;
                    this.showToast('{{ __('app.file_upload_failed') }}', 'error');
                },
                () => {}
            );
        }
    }"
    x-init="
        @if(session('success')) showToast('{{ session('success') }}', 'success') @endif
        @if(session('error')) showToast('{{ session('error') }}', 'error') @endif
        @if(session('warning')) showToast('{{ session('warning') }}', 'warning') @endif
    "
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
    class="space-y-6"
>
    {{-- Stepper --}}
    <div class="mb-8">
        <div class="flex items-center justify-center gap-4">
            <template x-for="s in [1,2,3,4]" :key="s">
                <div class="flex flex-col items-center">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full border-2 font-bold text-lg transition-all duration-200"
                        :class="{
                            'border-emerald-500 bg-emerald-500 text-white shadow-md shadow-emerald-200': step > s,
                            'border-emerald-500 text-emerald-600 ring-4 ring-emerald-50': step === s,
                            'border-slate-200 text-slate-400': step < s
                        }"
                    >
                        <template x-if="step > s">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>

                        <template x-if="step <= s">
                            <span x-text="s"></span>
                        </template>
                    </div>

                    <div
                        class="mt-2 text-xs font-semibold"
                        :class="step >= s ? 'text-emerald-600' : 'text-slate-400'"
                        x-text="s === 1 ? '{{ __('app.checkout_step_items') }}' : (s === 2 ? '{{ __('app.checkout_step_pricing') }}' : (s === 3 ? '{{ __('app.checkout_step_address') }}' : '{{ __('app.checkout_step_confirm') }}'))"
                    ></div>
                </div>
            </template>
        </div>

        <div class="relative h-2 mt-4 bg-slate-100 rounded-full overflow-hidden">
            <div
                class="absolute top-0 left-0 h-2 bg-emerald-500 transition-all duration-300"
                :style="'width: calc(' + ((step - 1) / 3) + ' * 100%)'"
            ></div>
        </div>
    </div>

    {{-- Toast --}}
    <div
        x-show="toast !== null"
        x-cloak
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type === 'success',
            'bg-red-50 text-red-700 border border-red-200': toast?.type === 'error',
            'bg-amber-50 text-amber-700 border border-amber-200': toast?.type === 'warning'
        }"
    >
        <span x-text="toast?.message"></span>

        <button type="button" @click="toast = null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Submit loading --}}
    <div
        x-show="submitting"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-100"
        style="display:none"
    >
        <div class="w-full max-w-sm rounded-3xl bg-white px-10 py-12 shadow-2xl text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg">
                <svg class="h-9 w-9 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>

            <h2 class="text-lg font-bold text-slate-900 mb-1">
                {{ __('app.calculating_quotation') }}
            </h2>

            <p class="text-sm text-slate-500 mb-8">
                {{ __('app.please_wait_seconds') }}
            </p>

            <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-400">
                <span>{{ __('app.processing_data') }}</span>
                <span x-text="Math.round(progressPct) + '%'"></span>
            </div>

            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                <div
                    class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    :style="'width:' + progressPct + '%'"
                ></div>
            </div>

            <p class="mt-6 text-xs text-slate-400">
                {{ __('app.ai_analyzing_rates') }}
            </p>
        </div>
    </div>

    {{-- General loading --}}
    <div
        wire:loading.flex
        wire:loading.except.target="submit"
        class="fixed inset-0 z-40 flex items-center justify-center bg-white/60 backdrop-blur-sm"
    >
        <div class="flex flex-col items-center gap-3">
            <svg class="h-8 w-8 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm font-medium text-slate-600">{{ __('app.processing') }}</span>
        </div>
    </div>

    {{-- Step 1 --}}
    <div x-show="step === 1" class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">
                    {{ __('app.section_quotation_info') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('app.project_name') }}
                    </label>

                    <input
                        type="text"
                        wire:model="projectName"
                        placeholder="{{ __('app.project_name_placeholder') }}"
                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('projectName') border-red-400 focus:ring-2 focus:ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                    >

                    @error('projectName')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('app.project_status') }}
                    </label>

                    @if($isEditMode)
                        <select
                            wire:model="projectStatus"
                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('projectStatus') border-red-400 focus:ring-2 focus:ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                        >
                            <option value="">{{ __('app.select_status') }}</option>

                            @foreach($projectStatuses as $ps)
                                <option value="{{ $ps->value }}">
                                    {{ $ps->label() }}
                                </option>
                            @endforeach
                        </select>

                        @error('projectStatus')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @else
                        <div class="h-11 flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700">
                            {{ __('app.status_pending') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">
                    {{ __('app.section_boq_upload') }}
                </h2>
            </div>

            <div class="p-6 space-y-6">
                @include('livewire.enduser.quotations.partials.boq-upload-and-items')
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="button"
                @click="step = 2"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                {{ __('app.next_step') }}
                <span>&rarr;</span>
            </button>
        </div>
    </div>

    {{-- Step 2 --}}
    <div x-show="step === 2" class="space-y-6">
        @if($showPricing && !empty($items))
            <div class="rounded-2xl border border-indigo-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-indigo-100 px-6 py-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            {{ __('app.section_pricing_review') }}
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ __('app.review_prices_desc') }}
                        </p>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.description') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.qty') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.unit') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.category') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.brand') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.price_sar') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.total_sar') }}
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $index => $item)
                                    @php
                                        $priceStatus = $item['price_status'] ?? 'pending';
                                        $unitPrice = is_numeric($item['unit_price'] ?? null) ? (float) $item['unit_price'] : null;
                                        $qty = (float) ($item['quantity'] ?? 0);
                                        $lineTotal = $unitPrice !== null ? $unitPrice * $qty : null;
                                    @endphp

                                    <tr>
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ $item['description'] ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-slate-600">
                                            {{ number_format($qty) }}
                                        </td>

                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $item['unit'] ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $item['category'] ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $item['brand'] ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-right font-mono text-slate-700">
                                            {{ $unitPrice !== null ? number_format($unitPrice, 2) : '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-right font-mono font-medium text-slate-800">
                                            {{ $lineTotal !== null ? number_format($lineTotal, 2) : '—' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($priceStatus !== 'approved')
                                                    <button
                                                        type="button"
                                                        wire:click="approvePriceItem({{ $index }})"
                                                        class="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-100"
                                                    >
                                                        {{ __('app.approve') }}
                                                    </button>
                                                @endif

                                                @if($priceStatus !== 'rejected')
                                                    <button
                                                        type="button"
                                                        wire:click="rejectPriceItem({{ $index }})"
                                                        class="rounded-lg bg-red-50 px-3 py-1 text-xs font-semibold text-red-500 hover:bg-red-100"
                                                    >
                                                        {{ __('app.reject') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @php
                        $taxRate = 0.15;
                        $subtotal = collect($items)
                            ->filter(fn($i) => ($i['price_status'] ?? 'pending') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
                            ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));

                        $taxAmount = $subtotal * $taxRate;
                        $total = $subtotal + $taxAmount;
                    @endphp

                    <div class="flex justify-end">
                        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-3">
                            <h3 class="text-sm font-semibold text-slate-700">
                                {{ __('app.financial_summary') }}
                            </h3>

                            <div class="flex justify-between text-sm text-slate-600">
                                <span>{{ __('app.subtotal') }}</span>
                                <span class="font-mono font-medium">{{ number_format($subtotal, 2) }} {{ __('app.sar') }}</span>
                            </div>

                            <div class="flex justify-between text-sm text-slate-600">
                                <span>{{ __('app.tax_vat_15') }}</span>
                                <span class="font-mono font-medium">{{ number_format($taxAmount, 2) }} {{ __('app.sar') }}</span>
                            </div>

                            <div class="border-t border-slate-200 pt-3 flex justify-between">
                                <span class="text-sm font-bold text-slate-800">{{ __('app.total_amount') }}</span>
                                <span class="font-mono text-lg font-bold text-emerald-600">
                                    {{ number_format($total, 2) }} {{ __('app.sar') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('app.no_data') }}
            </div>
        @endif

        <div class="flex justify-between">
            <button
                type="button"
                @click="step = 1"
                class="rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                &larr; {{ __('app.previous') }}
            </button>

            <button
                type="button"
                @click="step = 3"
                class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                {{ __('app.next_step') }} &rarr;
            </button>
        </div>
    </div>

    {{-- Step 3 / Submit --}}
    <div x-show="step === 3 || step === 4" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-800">
                {{ __('app.section_review_submit') }}
            </h2>
        </div>

        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-6">
                <div class="text-center">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('app.total_items') }}
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">
                        {{ count($items) }}
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('app.status') }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $projectStatus ? \App\Enums\QuotationProjectStatusEnum::tryFrom($projectStatus)?->label() ?? $projectStatus : '—' }}
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('app.project_name') }}
                    </p>
                    <p class="mt-1 max-w-[180px] truncate text-sm font-semibold text-slate-700">
                        {{ $projectName ?: '—' }}
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('app.boq_attachment') }}
                    </p>
                    <p class="mt-1 text-sm font-semibold {{ $boqFileName ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $boqFileName ?: __('app.no_file') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="saveDraft"
                    wire:loading.attr="disabled"
                    class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60"
                >
                    {{ __('app.save_draft') }}
                </button>

                <button
                    type="button"
                    @click="
                        submitting = true;
                        progressPct = 0;
                        let target = 92;
                        let iv = setInterval(() => {
                            if (progressPct < target) {
                                progressPct = Math.min(progressPct + Math.random() * 8 + 2, target);
                            } else {
                                clearInterval(iv);
                            }
                        }, 250);
                        $wire.submit();
                    "
                    @if($processing) disabled @endif
                    class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                >
                    {{ __('app.submit_quotation') }} &rarr;
                </button>
            </div>
        </div>
    </div>
</div>