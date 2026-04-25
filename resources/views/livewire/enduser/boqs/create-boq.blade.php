<div
    x-data="{
        dragOver: false,
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
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>

    {{-- Toast notification --}}
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

    {{-- Loading overlay --}}
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
            <div class="mx-auto mb-6 h-16 w-16 relative">
                <svg class="h-16 w-16 -rotate-90" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="5"/>
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#10b981" stroke-width="5"
                        stroke-dasharray="176" stroke-dashoffset="176" stroke-linecap="round"
                        style="animation: qimta-ring 2.5s ease forwards;"/>
                </svg>
                <svg class="absolute inset-0 m-auto h-7 w-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-1">{{ __('app.extracting_boq_items') }}</h2>
            <p class="text-sm text-slate-500 mb-8">{{ __('app.please_wait_seconds') }}</p>
            <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-400">
                <span>{{ __('app.processing_data') }}</span>
                <span x-text="progressPct + '%'"></span>
            </div>
            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    :style="'width:' + progressPct + '%'"></div>
            </div>
        </div>
        <style>
            @keyframes qimta-ring { 0% { stroke-dashoffset: 176; } 100% { stroke-dashoffset: 0; } }
        </style>
    </div>

    {{-- Generic loading overlay --}}
    <div
        wire:loading
        wire:loading.except.target="submit"
        x-data="{
            dismissed: false,
            ar: ['جاري القراءة...', 'جاري التحديث...', 'لحظة بس ⚡', 'جاري المعالجة...', 'تقريباً خلصنا...'],
            en: ['Reading file...', 'Updating data...', 'Just a moment ⚡', 'Processing...', 'Almost done...'],
            idx: 0,
            isAr: document.documentElement.dir === 'rtl',
            init() {
                setInterval(() => { this.idx = (this.idx + 1) % this.ar.length; }, 1800);
                /* Reset dismissed each time Livewire re-shows this element (new request) */
                new MutationObserver(() => {
                    if (this.$el.style.display !== 'none') {
                        this.dismissed = false;
                    } else if ($wire.draftBoqUuid) {
                        /* Request finished — store the draft UUID so the pill can link back */
                        $store.bgJob.boqUuid = $wire.draftBoqUuid;
                    }
                }).observe(this.$el, { attributes: true, attributeFilter: ['style'] });
            }
        }"
        style="display: none; position: fixed; inset: 0; z-index: 99999; pointer-events: none;"
    >
        {{-- Full-screen backdrop (hidden when dismissed) --}}
        <div
            x-show="!dismissed"
            style="position:absolute;inset:0;background:rgba(15,23,42,0.60);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);"
        ></div>

        {{-- Centered card (hidden when dismissed) --}}
        <div
            x-show="!dismissed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            style="
                pointer-events: auto;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #ffffff;
                border-radius: 28px;
                padding: 40px 44px 36px;
                text-align: center;
                width: 340px;
                max-width: calc(100vw - 40px);
                box-shadow: 0 40px 100px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.04);
                font-family: 'Cairo', sans-serif;
            "
            x-bind:dir="isAr ? 'rtl' : 'ltr'"
        >
            {{-- Dismiss button --}}
            <button
                @click="dismissed = true"
                type="button"
                title="إخفاء ومتابعة"
                style="position:absolute;top:14px;left:14px;width:30px;height:30px;border-radius:50%;border:none;background:#f1f5f9;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#94a3b8;transition:background .15s;"
                onmouseenter="this.style.background='#e2e8f0'"
                onmouseleave="this.style.background='#f1f5f9'"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            {{-- Double animated rings --}}
            <div style="position:relative; width:88px; height:88px; margin:0 auto 32px;">
                <svg style="position:absolute;inset:0;width:88px;height:88px;animation:gcw 1.4s linear infinite;" viewBox="0 0 88 88">
                    <circle cx="44" cy="44" r="38" fill="none" stroke="#d1fae5" stroke-width="6"/>
                    <circle cx="44" cy="44" r="38" fill="none" stroke="#10b981" stroke-width="6"
                            stroke-linecap="round" stroke-dasharray="66 172"/>
                </svg>
                <svg style="position:absolute;inset:10px;width:68px;height:68px;animation:gccw 2s linear infinite;" viewBox="0 0 68 68">
                    <circle cx="34" cy="34" r="28" fill="none" stroke="#a7f3d0" stroke-width="4"/>
                    <circle cx="34" cy="34" r="28" fill="none" stroke="#34d399" stroke-width="4"
                            stroke-linecap="round" stroke-dasharray="34 142"/>
                </svg>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                    <div style="width:18px;height:18px;border-radius:50%;background:#10b981;animation:gpulse 1.4s ease-in-out infinite;box-shadow:0 0 0 0 #10b98140;"></div>
                </div>
            </div>

            {{-- Cycling message --}}
            <p x-text="isAr ? ar[idx] : en[idx]"
               style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:10px;min-height:2.2rem;letter-spacing:-0.01em;"></p>
            <p x-text="isAr ? 'يتم تنفيذ العملية، الرجاء الانتظار' : 'Operation in progress, please wait…'"
               style="font-size:0.83rem;color:#94a3b8;font-weight:500;line-height:1.5;"></p>

            {{-- Dismiss hint --}}
            <p @click="dismissed = true"
               style="font-size:0.75rem;color:#cbd5e1;margin-top:12px;cursor:pointer;text-decoration:underline;text-underline-offset:2px;"
               x-text="isAr ? 'إخفاء ومتابعة التصفح ←' : 'Hide & keep browsing →'"></p>

            {{-- Bouncing dots --}}
            <div style="display:flex;justify-content:center;gap:7px;margin-top:20px;">
                <span style="width:9px;height:9px;border-radius:50%;background:#10b981;animation:gbounce 1.2s ease-in-out infinite 0s;display:inline-block;"></span>
                <span style="width:9px;height:9px;border-radius:50%;background:#34d399;animation:gbounce 1.2s ease-in-out infinite 0.2s;display:inline-block;"></span>
                <span style="width:9px;height:9px;border-radius:50%;background:#6ee7b7;animation:gbounce 1.2s ease-in-out infinite 0.4s;display:inline-block;"></span>
            </div>
        </div>

        {{-- When dismissed → activate the persistent layout pill --}}
        <template x-if="dismissed">
            <span x-init="$store.bgJob.active = true"></span>
        </template>

        <style>
            @keyframes gcw    { to { transform: rotate(360deg); } }
            @keyframes gccw   { to { transform: rotate(-360deg); } }
            @keyframes gpulse { 0%,100% { transform:scale(1);box-shadow:0 0 0 0 #10b98140; } 50% { transform:scale(1.35);box-shadow:0 0 0 10px #10b9810; } }
            @keyframes gbounce { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-9px); } }
        </style>
    </div>

    <div class="space-y-6">

        {{-- Section 1: Project Information --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_project_info') }}</h2>
            </div>

            <div class="space-y-5 p-6">
                {{-- Row 1: Project Name and BOQ Type (side by side) --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ __('app.project_name') }}
                        </label>
                        <input
                            type="text"
                            wire:model.blur="projectName"
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
                            {{ __('app.boq_type') }}
                        </label>
                        <select
                            wire:model.blur="boqType"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        >
                            @foreach($boqTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('boqType')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row 2: Project Description (full width) --}}
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('app.project_description_label') }} <span class="normal-case font-normal text-slate-400">{{ __('app.optional') }}</span>
                    </label>
                    <textarea
                        wire:model.blur="projectDescription"
                        placeholder="{{ __('app.describe_project_scope') }}"
                        rows="3"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition resize-none
                            @error('projectDescription') border-red-400 focus:ring-2 focus:ring-red-100
                            @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                    ></textarea>
                    @error('projectDescription')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: BOQ Upload & Management --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">{{ __('app.section_boq_items') }}</h2>
            </div>

            <div class="p-6 space-y-6">
                {{-- Upload area --}}
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('app.upload_boq_file') }}
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
                                    <span class="block text-xs text-slate-400">{{ __('app.file_formats_boq') }}</span>
                                @endif
                            </div>
                        </template>

                        <input
                            id="boq-upload"
                            type="file"
                            @change="startUpload($event)"
                            accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png"
                            class="hidden"
                        >
                    </label>

                    @error('boqFile')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror

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

                {{-- Manual item management --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('app.boq_items') }}
                        </p>
                        <div class="flex items-center gap-2">
                            @if(!empty($items))
                                <button
                                    type="button"
                                    wire:click="approveAllItems"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ __('app.approve_all') }}
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

                                            <td class="px-4 py-2.5">
                                                <input type="text" value="{{ $item['description'] }}"
                                                    wire:change="updateItem({{ $index }}, 'description', $event.target.value)"
                                                    placeholder="{{ __('app.item_description_placeholder') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

                                            <td class="px-4 py-2.5">
                                                <input type="number" value="{{ $item['quantity'] }}"
                                                    wire:change="updateItem({{ $index }}, 'quantity', $event.target.value)"
                                                    min="0" step="any"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

                                            <td class="px-4 py-2.5">
                                                <input type="text" value="{{ $item['unit'] }}"
                                                    wire:change="updateItem({{ $index }}, 'unit', $event.target.value)"
                                                    placeholder="{{ __('app.pcs') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

                                            <td class="px-4 py-2.5">
                                                <input type="text" value="{{ $item['category'] }}"
                                                    wire:change="updateItem({{ $index }}, 'category', $event.target.value)"
                                                    placeholder="{{ __('app.category') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

                                            <td class="px-4 py-2.5">
                                                <input type="text" value="{{ $item['brand'] }}"
                                                    wire:change="updateItem({{ $index }}, 'brand', $event.target.value)"
                                                    placeholder="{{ __('app.brand') }}"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

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
                                                        'sourcing' => __('app.status_confirmed'),
                                                        'sourced'  => __('app.status_sourced'),
                                                        'rejected' => __('app.status_rejected'),
                                                        default    => __('app.status_pending'),
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-2.5 text-center">
                                                <input type="checkbox"
                                                    @checked(!empty($item['engineering_required']))
                                                    wire:change="updateItem({{ $index }}, 'engineering_required', $event.target.checked)"
                                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif>
                                            </td>

                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if(($item['status'] ?? 'pending') === 'pending')
                                                        <button type="button" wire:click="approveItem({{ $index }})" title="{{ __('app.approve') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" wire:click="rejectItem({{ $index }})" title="{{ __('app.reject') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'sourcing')
                                                        <button type="button" wire:click="rejectItem({{ $index }})" title="{{ __('app.reject') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'rejected')
                                                        <button type="button" wire:click="approveItem({{ $index }})" title="{{ __('app.restore') }}"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-600">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    <button type="button" wire:click="deleteItem({{ $index }})" wire:confirm="{{ __('app.delete_this_item') }}" title="{{ __('app.delete') }}"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-red-50 hover:text-red-500">
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
        </div>

        {{-- Section 3: Review & Submit --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                <div class="flex flex-wrap gap-6">
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.total_items') }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($items) }}</p>
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

                <div class="flex items-center gap-3">
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
                        {{ __('app.submit_boq') }} &rarr;
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
