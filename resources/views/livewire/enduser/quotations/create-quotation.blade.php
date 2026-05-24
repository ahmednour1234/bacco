<div
    x-data="{
        step: {{ $showPricing && ! empty($items) ? 4 : (! empty($items) ? 2 : 1) }},
        dragOver: false,
        deleteConfirm: null,
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
                () => { this.tempUploading = false; this.uploadReady = true; },
                () => { this.tempUploading = false; this.uploadReady = false; this.selectedFileName = null; this.selectedFileSize = null; this.showToast('{{ __('app.file_upload_failed') }}', 'error'); },
                () => {}
            );
        }
    }"
    x-init="
        @if(session('success')) showToast('{{ session('success') }}', 'success') @endif
        @if(session('error'))   showToast('{{ session('error') }}',   'error')   @endif
        @if(session('warning')) showToast('{{ session('warning') }}', 'warning') @endif
    "
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>

    {{-- ───── Toast notification ──────────────────────────────────────────── --}}
    <div
        x-show="toast !== null"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type === 'success',
            'bg-red-50 text-red-700 border border-red-200':             toast?.type === 'error',
            'bg-amber-50 text-amber-700 border border-amber-200':       toast?.type === 'warning',
        }"
    >
        <span x-text="toast?.message"></span>
        <button @click="toast = null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ───── Submit / Calculating loading overlay ────────────────────────────── --}}
    <div
        x-show="submitting"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-100"
        style="display:none"
    >
        <div class="w-full max-w-sm rounded-3xl bg-white px-10 py-12 shadow-2xl text-center">

            {{-- Logo --}}
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg">
                <svg class="h-9 w-9 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>

            {{-- Animated ring --}}
            <div class="mx-auto mb-6 h-16 w-16 relative">
                <svg class="h-16 w-16 -rotate-90" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="5"/>
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#10b981" stroke-width="5"
                        stroke-dasharray="176"
                        stroke-dashoffset="176"
                        stroke-linecap="round"
                        style="animation: qimta-ring 2.5s ease forwards;"/>
                </svg>
                <svg class="absolute inset-0 m-auto h-7 w-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </div>

            <h2 class="text-lg font-bold text-slate-900 mb-1">{{ __('app.calculating_quotation') }}</h2>
            <p class="text-sm text-slate-500 mb-8">{{ __('app.please_wait_seconds') }}</p>

            {{-- Progress bar --}}
            <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-400">
                <span>{{ __('app.processing_data') }}</span>
                <span x-text="progressPct + '%'"></span>
            </div>
            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    :style="'width:' + progressPct + '%'"></div>
            </div>

            <p class="mt-6 text-xs text-slate-400">{{ __('app.ai_analyzing_rates') }}</p>
        </div>

        <style>
            @keyframes qimta-ring {
                0%   { stroke-dashoffset: 176; }
                100% { stroke-dashoffset: 0; }
            }
        </style>
    </div>

    {{-- Generic loading overlay --}}
    <div
        wire:loading
        wire:loading.except.target="submit"
        x-data="{
            dismissed: false,
            ar: ['جاري المعالجة...', 'جاري التحديث...', 'لحظة بس ⚡', 'جاري الاستخراج...', 'تقريباً خلصنا...'],
            en: ['Processing...', 'Updating data...', 'Just a moment ⚡', 'Extracting...', 'Almost done...'],
            idx: 0,
            isAr: document.documentElement.dir === 'rtl',
            init() {
                setInterval(() => { this.idx = (this.idx + 1) % this.ar.length; }, 1800);
                new MutationObserver(() => {
                    if (this.$el.style.display !== 'none') { this.dismissed = false; }
                }).observe(this.$el, { attributes: true, attributeFilter: ['style'] });
            }
        }"
        style="display: none; position: fixed; inset: 0; z-index: 99999; pointer-events: none;"
    >
        <div x-show="!dismissed" style="position:absolute;inset:0;background:rgba(15,23,42,0.60);backdrop-filter:blur(7px);"></div>
        <div
            x-show="!dismissed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            style="pointer-events:auto;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:28px;padding:40px 44px 36px;text-align:center;width:340px;max-width:calc(100vw - 40px);box-shadow:0 40px 100px rgba(0,0,0,.25);font-family:'Cairo',sans-serif;"
            x-bind:dir="isAr ? 'rtl' : 'ltr'"
        >
            <button @click="dismissed=true" type="button" style="position:absolute;top:14px;left:14px;width:30px;height:30px;border-radius:50%;border:none;background:#f1f5f9;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div style="position:relative;width:88px;height:88px;margin:0 auto 32px;">
                <svg style="position:absolute;inset:0;width:88px;height:88px;animation:gcw 1.4s linear infinite;" viewBox="0 0 88 88">
                    <circle cx="44" cy="44" r="38" fill="none" stroke="#d1fae5" stroke-width="6"/>
                    <circle cx="44" cy="44" r="38" fill="none" stroke="#10b981" stroke-width="6" stroke-linecap="round" stroke-dasharray="66 172"/>
                </svg>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                    <div style="width:18px;height:18px;border-radius:50%;background:#10b981;animation:gpulse 1.4s ease-in-out infinite;box-shadow:0 0 0 0 #10b98140;"></div>
                </div>
            </div>
            <p x-text="isAr ? ar[idx] : en[idx]" style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:10px;min-height:2.2rem;"></p>
            <p x-text="isAr ? 'يتم تنفيذ العملية، الرجاء الانتظار' : 'Operation in progress, please wait…'" style="font-size:0.83rem;color:#94a3b8;font-weight:500;"></p>
            <p @click="dismissed=true" style="font-size:0.75rem;color:#cbd5e1;margin-top:12px;cursor:pointer;text-decoration:underline;" x-text="isAr ? 'إخفاء ومتابعة التصفح ←' : 'Hide & keep browsing →'"></p>
        </div>
        <template x-if="dismissed"><span x-init="$store.bgJob.active = true"></span></template>
        <style>
            @keyframes gcw { to { transform: rotate(360deg); } }
            @keyframes gpulse { 0%,100%{transform:scale(1);box-shadow:0 0 0 0 #10b98140;}50%{transform:scale(1.35);box-shadow:0 0 0 10px #10b9810;} }
        </style>
    </div>

    <div class="space-y-6">
        @php
            $createStepCurrent = 1;

            if (trim($projectName) !== '' && trim($projectStatus) !== '') {
                $createStepCurrent = 2;
            }

            if (! empty($items)) {
                $createStepCurrent = 3;
            }

            if ($showPricing && ! empty($items)) {
                $createStepCurrent = 4;
            }

            $createSteps = [
                __('app.create_quote_step_info'),
                __('app.create_quote_step_boq'),
                __('app.create_quote_step_pricing'),
                __('app.create_quote_step_submit'),
            ];
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="relative flex items-start justify-between gap-3 py-2">
                <div class="absolute top-6 start-0 end-0 mx-12 hidden h-0.5 bg-slate-200 sm:block"></div>
                <div
                    class="absolute top-6 start-12 hidden h-0.5 bg-emerald-400 transition-all duration-500 sm:block"
                    :style="'width: calc(' + ((step - 1) / 3) + ' * (100% - 6rem))'"
                ></div>

                @foreach($createSteps as $index => $label)
                    @php
                        $stepNumber = $index + 1;
                        $isCompleted = $createStepCurrent > $stepNumber;
                        $isCurrent = $createStepCurrent === $stepNumber;
                    @endphp
                    <div class="relative z-10 flex min-w-0 flex-1 flex-col items-center gap-2 text-center">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-xs font-bold transition-all duration-300"
                            :class="{
                                'border-emerald-500 bg-emerald-500 text-white shadow-md shadow-emerald-200': step > {{ $stepNumber }},
                                'border-emerald-500 bg-white text-emerald-600 ring-4 ring-emerald-50': step === {{ $stepNumber }},
                                'border-slate-200 bg-white text-slate-400': step < {{ $stepNumber }}
                            }"
                        >
                            <span x-show="step <= {{ $stepNumber }}">{{ $stepNumber }}</span>
                            <span x-show="step > {{ $stepNumber }}" x-cloak>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </div>
                        <span
                            class="text-[11px] font-semibold leading-snug transition-colors duration-300"
                            :class="step >= {{ $stepNumber }} ? 'text-emerald-600' : 'text-slate-400'"
                        >
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- Section 1: Quotation Information                                    --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div
            x-show="step === 1"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-3"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_quotation_info') }}</h2>
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
                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition
                            @error('projectName') border-red-400 focus:ring-2 focus:ring-red-100
                            @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
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
                            class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition
                                @error('projectStatus') border-red-400 focus:ring-2 focus:ring-red-100
                                @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                        >
                            <option value="">{{ __('app.select_status') }}</option>
                            @foreach($projectStatuses as $ps)
                                <option value="{{ $ps->value }}" @selected($projectStatus === $ps->value)>
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

            <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    @click="step = 2; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                    <span>{{ __('app.create_quote_continue_boq') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        </div>

        {{-- Section 2: BOQ Upload & Management                                  --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div
            x-show="step === 2"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-3"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_boq_upload') }}</h2>
            </div>

            <div class="p-6 space-y-6">

                {{-- Sub-section A: Upload --}}
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('app.subsection_upload_boq') }}
                    </p>

                    <label
                        for="boq-upload"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="
                            dragOver = false;
                            if ($event.dataTransfer.files.length) {
                                const dt = new DataTransfer();
                                dt.items.add($event.dataTransfer.files[0]);
                                const input = document.getElementById('boq-upload');
                                input.files = dt.files;
                                startUpload({ target: input });
                            }
                        "
                        :class="dragOver ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40'"
                        class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-12 text-center transition"
                    >
                        {{-- Upload / spinner icon --}}
                        <div class="relative">
                            <svg x-show="!tempUploading" class="h-10 w-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <svg x-show="tempUploading" x-cloak class="h-10 w-10 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>

                        {{-- Dynamic label text (Alpine-driven for immediate feedback) --}}
                        <template x-if="selectedFileName">
                            <span class="text-sm font-medium text-emerald-600">
                                <span x-text="selectedFileName"></span>
                                <span class="text-slate-400" x-text="' (' + selectedFileSize + ' KB)'"></span>
                            </span>
                        </template>
                        <template x-if="selectedFileName && tempUploading">
                            <span class="text-xs text-slate-400">{{ __('app.uploading_please_wait') }}</span>
                        </template>
                        <template x-if="selectedFileName && uploadReady">
                            <span class="text-xs text-slate-400">{{ __('app.file_ready_extract') }}</span>
                        </template>
                        <template x-if="!selectedFileName">
                            <div class="space-y-1">
                                @if($boqFileName)
                                    <span class="block text-sm font-medium text-slate-600">{{ $boqFileName }}</span>
                                    <span class="block text-xs text-slate-400">{{ __('app.previously_uploaded') }}</span>
                                @else
                                    <span class="block text-sm font-medium text-slate-700">{{ __('app.click_upload_drag') }}</span>
                                    <span class="block text-xs text-slate-400">{{ __('app.file_formats_supported') }}</span>
                                @endif
                            </div>
                        </template>

                        <input
                            id="boq-upload"
                            type="file"
                            @change="startUpload($event)"
                            accept=".pdf,.xlsx,.xls,.csv"
                            class="hidden"
                        >
                    </label>

                    @error('boqFile')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Extract button: only enabled after $wire.upload() callback confirms $boqFile is committed --}}
                    <div x-show="uploadReady || {{ $boqFileName ? 'true' : 'false' }}" x-cloak class="mt-3 flex justify-end">
                        <button
                            type="button"
                            wire:click="uploadBoq"
                            wire:loading.attr="disabled"
                            wire:target="uploadBoq"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="uploadBoq" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span wire:loading.remove wire:target="uploadBoq">{{ __('app.extract_items_ai') }}</span>
                            <span wire:loading wire:target="uploadBoq">{{ __('app.extracting') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Sub-section B: Item Table --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('app.subsection_add_manually') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!empty($items))
                                <button
                                    type="button"
                                    wire:click="approveAllItems"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ __('app.approve_all') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="rejectAllItems"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    {{ __('app.reject_all') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="selectAllPricingItems"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                >
                                    {{ __('app.select_all_for_pricing') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="clearPricingSelection"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                                >
                                    {{ __('app.clear_selection') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="clearAllItems"
                                    wire:confirm="{{ __('app.remove_all_items_confirm') }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    {{ __('app.remove_all_rows') }}
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click="addManualItem"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.add_new_row') }}
                            </button>
                        </div>
                    </div>

                    @if(empty($items))
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                            {{ __('app.no_items_upload_or_add') }}
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50">
                                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-600 w-20">{{ __('app.for_pricing') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.unit') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.brand') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($items as $index => $item)
                                        <tr class="group transition-colors hover:bg-slate-50/60
                                            @if(($item['status'] ?? '') === 'rejected') opacity-60 @endif">

                                            <td class="px-3 py-2.5 text-center">
                                                <input
                                                    type="checkbox"
                                                    @checked(!empty($item['is_selected']))
                                                    wire:change="updateItem({{ $index }}, 'is_selected', $event.target.checked)"
                                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-40"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Description --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['description'] }}"
                                                    wire:change="updateItem({{ $index }}, 'description', $event.target.value)"
                                                    placeholder="{{ __('app.item_description_placeholder') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Quantity --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="number"
                                                    value="{{ $item['quantity'] }}"
                                                    wire:change="updateItem({{ $index }}, 'quantity', $event.target.value)"
                                                    min="0"
                                                    step="any"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Unit --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['unit'] }}"
                                                    wire:change="updateItem({{ $index }}, 'unit', $event.target.value)"
                                                    placeholder="{{ __('app.pcs') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Category --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['category'] }}"
                                                    wire:change="updateItem({{ $index }}, 'category', $event.target.value)"
                                                    placeholder="{{ __('app.category') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Brand --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['brand'] }}"
                                                    wire:change="updateItem({{ $index }}, 'brand', $event.target.value)"
                                                    placeholder="{{ __('app.brand') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Status badge --}}
                                            <td class="px-4 py-2.5">
                                                @php
                                                    $statusVal = $item['status'] ?? 'pending';
                                                    $badgeClass = match($statusVal) {
                                                        'sourcing' => 'bg-emerald-100 text-emerald-700',
                                                        'sourced'  => 'bg-blue-100  text-blue-700',
                                                        'rejected' => 'bg-red-100   text-red-700',
                                                        default    => 'bg-amber-100 text-amber-700',
                                                    };
                                                    $badgeLabel = match($statusVal) {
                                                        'sourcing' => 'Confirmed',
                                                        'sourced'  => 'Sourced',
                                                        'rejected' => 'Rejected',
                                                        default    => 'Pending',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>

                                            {{-- Engineering checkbox --}}
                                            <td class="px-4 py-2.5 text-center">
                                                <input
                                                    type="checkbox"
                                                    @checked(!empty($item['engineering_required']))
                                                    wire:change="updateItem({{ $index }}, 'engineering_required', $event.target.checked)"
                                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Actions --}}
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if(($item['status'] ?? 'pending') === 'pending')
                                                        {{-- Approve --}}
                                                        <button
                                                            type="button"
                                                            wire:click="approveItem({{ $index }})"
                                                            title="{{ __('app.approve') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Reject --}}
                                                        <button
                                                            type="button"
                                                            wire:click="rejectItem({{ $index }})"
                                                            title="{{ __('app.reject') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'sourcing')
                                                        {{-- Undo approve back to pending --}}
                                                        <button
                                                            type="button"
                                                            wire:click="rejectItem({{ $index }})"
                                                            title="{{ __('app.reject') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'rejected')
                                                        {{-- Restore to pending --}}
                                                        <button
                                                            type="button"
                                                            wire:click="approveItem({{ $index }})"
                                                            title="{{ __('app.restore') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-600"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    {{-- Delete --}}
                                                    <button
                                                        type="button"
                                                        wire:click="deleteItem({{ $index }})"
                                                        wire:confirm="{{ __('app.delete_this_item') }}"
                                                        title="{{ __('app.delete') }}"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
            <div
                x-show="step === 2"
                x-cloak
                class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <button
                    type="button"
                    @click="step = 1; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.back') }}
                </button>

                <button
                    type="button"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(empty($items)) disabled @endif
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
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="submit">{{ __('app.create_quote_continue_pricing') }}</span>
                    <span wire:loading wire:target="submit">{{ __('app.processing') }}</span>
                    <svg wire:loading.remove wire:target="submit" class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Section 2b: Pricing Review (shown after "Get Pricing" is clicked)   --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        @if($showPricing && !empty($items))
        <div
            x-show="step === 3"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-3"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="rounded-2xl border border-indigo-200 bg-white shadow-sm"
        >

            <div class="flex items-center gap-3 border-b border-indigo-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_pricing_review') }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.review_prices_desc') }}</p>
                </div>
            </div>

            <div class="p-6 space-y-5">

                {{-- Pricing table --}}
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[180px]">{{ __('app.description') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-16">{{ __('app.qty') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.unit') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.category') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.brand') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.price_sar') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">{{ __('app.total_sar') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-8">{{ __('app.source') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $index => $item)
                                @php
                                    $priceStatus  = $item['price_status'] ?? 'pending';
                                    $unitPrice    = is_numeric($item['unit_price'] ?? null) ? (float) $item['unit_price'] : null;
                                    $lineTotal    = $unitPrice !== null ? $unitPrice * (float) ($item['quantity'] ?? 0) : null;
                                    $priceSource  = $item['price_source'] ?? null;

                                    $rowClass = match($priceStatus) {
                                        'approved' => 'bg-emerald-50/40',
                                        'rejected' => 'opacity-50 bg-red-50/20',
                                        default    => '',
                                    };
                                    $badgeClass = match($priceStatus) {
                                        'approved' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default    => 'bg-amber-100 text-amber-700',
                                    };
                                    $badgeLabel = match($priceStatus) {
                                        'approved' => __('app.status_approved'),
                                        'rejected' => __('app.status_rejected'),
                                        default    => __('app.status_pending'),
                                    };
                                @endphp
                                <tr class="transition-colors {{ $rowClass }}">
                                    <td class="px-4 py-3 text-sm text-slate-700 font-medium">{{ $item['description'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ number_format((float)($item['quantity'] ?? 0)) }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item['unit'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item['category'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item['brand'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if(!empty($item['engineering_required']))
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-emerald-100">
                                                <svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="inline-block h-5 w-5 rounded border border-slate-200 bg-slate-50"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-slate-700">
                                        @if($unitPrice !== null)
                                            {{ number_format($unitPrice, 2) }}
                                        @else
                                            <span class="text-xs text-slate-400 italic">{{ __('app.not_found') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm font-medium text-slate-800">
                                        @if($lineTotal !== null)
                                            {{ number_format($lineTotal, 2) }}
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($priceSource === 'products')
                                            <span title="{{ __('app.price_from_products') }}" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-xs font-bold">P</span>
                                        @elseif($priceSource === 'gemini')
                                            <span title="{{ __('app.price_estimated_ai') }}" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-purple-100 text-purple-600 text-xs font-bold">AI</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($priceStatus !== 'approved')
                                                <button
                                                    type="button"
                                                    wire:click="approvePriceItem({{ $index }})"
                                                    title="{{ __('app.approve_price') }}"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($priceStatus !== 'rejected')
                                                <button
                                                    type="button"
                                                    wire:click="rejectPriceItem({{ $index }})"
                                                    title="{{ __('app.reject_price') }}"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($priceStatus === 'rejected')
                                                <span class="text-xs text-slate-400 italic">{{ __('app.status_rejected') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Financial Summary --}}
                @php
                    $taxRate   = 0.15;
                    $subtotal  = collect($items)
                        ->filter(fn($i) => ($i['price_status'] ?? 'pending') !== 'rejected' && is_numeric($i['unit_price'] ?? null))
                        ->sum(fn($i) => (float) $i['unit_price'] * (float) ($i['quantity'] ?? 0));
                    $taxAmount = $subtotal * $taxRate;
                    $total     = $subtotal + $taxAmount;
                @endphp
                <div class="flex justify-end">
                    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-slate-700">{{ __('app.financial_summary') }}</h3>

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
                            <span class="font-mono text-lg font-bold text-emerald-600">{{ number_format($total, 2) }} {{ __('app.sar') }}</span>
                        </div>

                        <div class="pt-1 text-xs text-slate-400">
                            {{ __('app.includes_non_rejected') }}
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex flex-col gap-3 border-t border-indigo-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    @click="step = 2; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.back') }}
                </button>

                <button
                    type="button"
                    @click="step = 4; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                    <span>{{ __('app.create_quote_continue_submit') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- Section 3: Review & Submit                                          --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div
            x-show="step === 4"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-3"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_review_submit') }}</h2>
            </div>

            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">

                {{-- Summary cards --}}
                <div class="flex flex-wrap gap-6">

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.total_items') }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($items) }}</p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.status') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $projectStatus
                                ? \App\Enums\QuotationProjectStatusEnum::tryFrom($projectStatus)?->label() ?? $projectStatus
                                : '—' }}
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.project_name') }}</p>
                        <p class="mt-1 max-w-[180px] truncate text-sm font-semibold text-slate-700">
                            {{ $projectName ?: '—' }}
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.boq_attachment') }}</p>
                        <p class="mt-1 text-sm font-semibold {{ $boqFileName ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $boqFileName ?: __('app.no_file') }}
                        </p>
                    </div>

                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="step = 3; window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        {{ __('app.back') }}
                    </button>

                    <button
                        type="button"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="saveDraft" class="h-4 w-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
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
</div>
