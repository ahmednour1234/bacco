<div
    {!! $processing ? 'wire:poll.4000ms="checkAiStatus"' : ($pricesFetching ? 'wire:poll.5000ms="pollPriceStatus"' : '') !!}
    x-data="{
        dragOver: false,
        toast: null,
        selectedFileName: null,
        selectedFileSize: null,
        tempUploading: false,
        uploadReady: false,
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
    x-on:boq-resume-done.window=""
    x-on:boq-ai-started.window="$store.bgJob.active = true"
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

    {{-- Generic loading overlay --}}
    <div
        wire:loading
        wire:loading.except.target="placeOrder,submit"
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

    {{-- ══════════════════════════════════════════════════════
         STEP PROGRESS BAR
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep < 5)
    @php
        $steps = [
            1 => ['ar' => 'الاستخراج',       'en' => 'Extraction'],
            2 => ['ar' => 'التأكيد',          'en' => 'Confirm'],
            3 => ['ar' => 'عرض السعر',        'en' => 'Quotation'],
            4 => ['ar' => 'العنوان والدفع',   'en' => 'Address & Pay'],
        ];
        $isRtl = app()->getLocale() === 'ar';
    @endphp
    <div style="display:flex;align-items:center;margin-bottom:2.5rem;padding-bottom:1.75rem;border-bottom:1px solid #e2e8f0;">
        @foreach($steps as $num => $step)
            @php
                $isDone   = $currentStep > $num;
                $isActive = $currentStep === $num;
                $isLast   = $num === count($steps);
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;{{ $isLast ? '' : 'flex:1;' }}">
                <div style="
                    width:40px;height:40px;border-radius:50%;
                    display:flex;align-items:center;justify-content:center;
                    font-size:15px;font-weight:700;font-family:'Cairo',sans-serif;
                    transition:all .3s;
                    {{ $isDone   ? 'background:#10b981;color:#fff;' : '' }}
                    {{ $isActive ? 'background:#059669;color:#fff;box-shadow:0 0 0 4px #d1fae5;' : '' }}
                    {{ !$isDone && !$isActive ? 'background:#fff;border:2px solid #e2e8f0;color:#94a3b8;' : '' }}
                ">
                    @if($isDone)
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span style="
                    margin-top:8px;font-size:11px;font-weight:600;white-space:nowrap;font-family:'Cairo',sans-serif;
                    {{ $isActive ? 'color:#047857;' : ($isDone ? 'color:#10b981;' : 'color:#94a3b8;') }}
                ">{{ $isRtl ? $step['ar'] : $step['en'] }}</span>
            </div>
            @if(!$isLast)
                <div style="flex:1;height:2px;margin:0 4px;margin-bottom:24px;background:{{ $currentStep > $num ? '#6ee7b7' : '#e2e8f0' }};transition:background .5s;"></div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STEP 1 – الاستخراج  (Project Info + Upload)
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep === 1)
    <div class="space-y-6">

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 style="font-size:13px;font-weight:700;color:#1e293b;margin:0;padding:0;">{{ __('app.section_project_info') }}</h2>
            </div>
            <div class="space-y-7 p-7">
                <div class="grid grid-cols-1 gap-7 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.project_name') }}</label>
                        <input type="text" wire:model.blur="projectName" placeholder="{{ __('app.project_name_placeholder') }}"
                            class="h-12 w-full rounded-xl border bg-white px-4 text-sm text-slate-800 shadow-sm outline-none transition @error('projectName') border-red-400 focus:ring-2 focus:ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                        @error('projectName')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('app.boq_type') }}</label>
                        <select wire:model.blur="boqType" class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                            @foreach($boqTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400">
                        {{ __('app.project_description_label') }} <span class="normal-case font-normal text-slate-300 ms-1">{{ __('app.optional') }}</span>
                    </label>
                    <textarea wire:model.blur="projectDescription" rows="3" placeholder="{{ __('app.describe_project_scope') }}"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition resize-none @error('projectDescription') border-red-400 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"></textarea>
                    @error('projectDescription')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 style="font-size:13px;font-weight:700;color:#1e293b;margin:0;padding:0;">{{ __('app.section_boq_items') }}</h2>
            </div>
            <div class="p-6 space-y-5">
                <label
                    for="boq-upload"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver=false; if($event.dataTransfer.files.length){const dt=new DataTransfer();dt.items.add($event.dataTransfer.files[0]);const inp=document.getElementById('boq-upload');inp.files=dt.files;startUpload({target:inp});}"
                    :class="dragOver ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40'"
                    class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-12 text-center transition"
                >
                    <div class="relative">
                        <svg x-show="!tempUploading" width="40" height="40" style="width:40px;height:40px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <svg x-show="tempUploading" x-cloak width="40" height="40" style="width:40px;height:40px;color:#10b981;" class="animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                    <template x-if="selectedFileName">
                        <span class="text-sm font-medium text-emerald-600"><span x-text="selectedFileName"></span><span class="text-slate-400" x-text="' (' + selectedFileSize + ' KB)'"></span></span>
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
                    <input id="boq-upload" type="file" @change="startUpload($event)" accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png" class="hidden">
                </label>
                @error('boqFile')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror

                <div x-show="uploadReady || {{ $boqFileName ? 'true' : 'false' }}" x-cloak class="flex justify-end">
                    <button type="button" wire:click="uploadBoq" wire:loading.attr="disabled" wire:target="uploadBoq" @if($processing) disabled @endif
                        class="inline-flex items-center gap-2.5 rounded-xl bg-emerald-600 px-7 py-3 text-sm font-bold text-white shadow-md transition hover:bg-emerald-700 active:scale-95 disabled:opacity-60" style="box-shadow:0 4px 14px #10b98140;">
                        <svg wire:loading wire:target="uploadBoq" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg wire:loading.remove wire:target="uploadBoq" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span wire:loading.remove wire:target="uploadBoq">{{ __('app.extract_items_ai') }}</span>
                        <span wire:loading wire:target="uploadBoq">{{ __('app.extracting') }}</span>
                    </button>
                </div>

                @if(!empty($items))
                <div class="flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
                    <span class="text-sm font-medium text-emerald-700">✓ {{ count($items) }} {{ app()->getLocale() === 'ar' ? 'عنصر مستخرج' : 'items extracted' }}</span>
                    <button type="button" wire:click="$set('currentStep', 2)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        {{ app()->getLocale() === 'ar' ? 'التالي: مراجعة العناصر' : 'Next: Review Items' }}
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                @endif

                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('app.boq_items') }}</p>
                        <div class="flex gap-2">
                            @if(!empty($items))
                                <button type="button" wire:click="clearAllItems" wire:confirm="{{ __('app.remove_all_items_confirm') }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-100 transition active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    {{ __('app.remove_all_rows') }}
                                </button>
                            @endif
                            <button type="button" wire:click="addManualItem"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition active:scale-95">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                {{ __('app.add_new_row') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-start">
            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition active:scale-95">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                {{ __('app.save_draft') }}
            </button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STEP 2 – التأكيد  (Review & Confirm Items)
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep === 2)
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تأكيد العناصر' : 'Confirm Items' }}</h2>
                        <p class="text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? 'راجع واعتمد العناصر قبل جلب الأسعار' : 'Review and approve items before fetching prices' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="selectAllItems"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ app()->getLocale() === 'ar' ? 'تحديد الكل' : 'Select All' }}
                    </button>
                    <button type="button" wire:click="deselectAllItems"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        {{ app()->getLocale() === 'ar' ? 'إلغاء تحديد الكل' : 'Deselect All' }}
                    </button>
                    <button type="button" wire:click="approveAllItems"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('app.approve_all') }}
                    </button>
                    <button type="button" wire:click="addManualItem"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('app.add_new_row') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if(empty($items))
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">{{ __('app.no_items_upload_or_add') }}</div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-16">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-emerald-600">{{ app()->getLocale() === 'ar' ? 'للتسعير' : 'Price' }}</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">{{ __('app.description') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">{{ __('app.qty') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.unit') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">{{ __('app.category') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.status') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">{{ __('app.engineering') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $index => $item)
                                    @php $isRejected = ($item['status'] ?? '') === 'rejected'; @endphp
                                    <tr class="group transition-colors hover:bg-slate-50/60 @if($isRejected) opacity-50 @endif">
                                        <td class="px-3 py-2.5 text-center">
                                            <label class="inline-flex cursor-pointer items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    @checked(!empty($item['is_selected']))
                                                    wire:change="updateItem({{ $index }}, 'is_selected', $event.target.checked)"
                                                    @if($isRejected) disabled @endif
                                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-40"
                                                >
                                            </label>
                                        </td>
                                        <td class="px-4 py-2.5"><input type="text" value="{{ $item['description'] }}" wire:change="updateItem({{ $index }}, 'description', $event.target.value)" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200" @if($isRejected) disabled @endif></td>
                                        <td class="px-4 py-2.5"><input type="number" value="{{ $item['quantity'] }}" wire:change="updateItem({{ $index }}, 'quantity', $event.target.value)" min="0" step="any" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200" @if(($item['status'] ?? '') === 'rejected') disabled @endif></td>
                                        <td class="px-4 py-2.5"><input type="text" value="{{ $item['unit'] }}" wire:change="updateItem({{ $index }}, 'unit', $event.target.value)" placeholder="{{ __('app.pcs') }}" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200" @if(($item['status'] ?? '') === 'rejected') disabled @endif></td>
                                        <td class="px-4 py-2.5"><input type="text" value="{{ $item['category'] }}" wire:change="updateItem({{ $index }}, 'category', $event.target.value)" placeholder="{{ __('app.category') }}" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200" @if(($item['status'] ?? '') === 'rejected') disabled @endif></td>
                                        <td class="px-4 py-2.5">
                                            @php $sv=($item['status']??'pending'); $bc=match($sv){'sourcing'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700',default=>'bg-amber-100 text-amber-700'}; $bl=match($sv){'sourcing'=>__('app.status_confirmed'),'rejected'=>__('app.status_rejected'),default=>__('app.status_pending')}; @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $bc }}">{{ $bl }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center"><input type="checkbox" @checked(!empty($item['engineering_required'])) wire:change="updateItem({{ $index }}, 'engineering_required', $event.target.checked)" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @if(($item['status'] ?? '') === 'rejected') disabled @endif></td>
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if(($item['status'] ?? 'pending') === 'pending')
                                                    <button type="button" wire:click="approveItem({{ $index }})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                                                    <button type="button" wire:click="rejectItem({{ $index }})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                @elseif(($item['status'] ?? '') === 'sourcing')
                                                    <button type="button" wire:click="rejectItem({{ $index }})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                @elseif(($item['status'] ?? '') === 'rejected')
                                                    <button type="button" wire:click="approveItem({{ $index }})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                                @endif
                                                <button type="button" wire:click="deleteItem({{ $index }})" wire:confirm="{{ __('app.delete_this_item') }}" class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4 bg-slate-50/50 rounded-b-2xl">
                <div class="flex gap-5 text-center">
                    <div><p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('app.total_items') }}</p><p class="text-xl font-bold text-slate-900">{{ count($items) }}</p></div>
                    <div><p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ app()->getLocale() === 'ar' ? 'مقبولة' : 'Approved' }}</p><p class="text-xl font-bold text-emerald-600">{{ collect($items)->filter(fn($i)=>($i['status']??'')!=='rejected')->count() }}</p></div>
                    <div><p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ app()->getLocale() === 'ar' ? 'للتسعير' : 'For Pricing' }}</p><p class="text-xl font-bold text-emerald-500">{{ collect($items)->filter(fn($i)=>!empty($i['is_selected']) && ($i['status']??'')!=='rejected')->count() }}</p></div>
                    <div><p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ app()->getLocale() === 'ar' ? 'مرفوضة' : 'Rejected' }}</p><p class="text-xl font-bold text-red-500">{{ collect($items)->filter(fn($i)=>($i['status']??'')==='rejected')->count() }}</p></div>
                </div>
                <div class="flex gap-3">
                    <button type="button" wire:click="goBack"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        {{ app()->getLocale() === 'ar' ? 'السابق' : 'Back' }}
                    </button>
                    <button type="button" wire:click="confirmItems" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60">
                        <svg wire:loading wire:target="confirmItems" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="confirmItems">{{ app()->getLocale() === 'ar' ? 'التالي: إنشاء عرض السعر' : 'Next: Create Quotation' }}</span>
                        <span wire:loading wire:target="confirmItems">{{ app()->getLocale() === 'ar' ? 'جاري المعالجة...' : 'Processing...' }}</span>
                        <svg wire:loading.remove wire:target="confirmItems" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STEP 3 – جلب الأسعار  (Prices / Quotation Review)
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep === 3)
    @php $isAr = app()->getLocale() === 'ar'; @endphp
    <div class="space-y-5">

        {{-- ── Still fetching (fallback poll state) ─────────────────────── --}}
        @if($pricesFetching)
        <div style="display:flex;align-items:center;gap:20px;border-radius:20px;border:1px solid #bfdbfe;background:#eff6ff;padding:24px 28px;">
            <div style="flex-shrink:0;width:52px;height:52px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                <svg width="26" height="26" style="animation:gcw 1.2s linear infinite;" fill="none" stroke="#3b82f6" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p style="font-size:15px;font-weight:700;color:#1e40af;margin:0 0 4px;">{{ $isAr ? 'جاري جلب أسعار السوق...' : 'Fetching live market prices…' }}</p>
                <p style="font-size:13px;color:#3b82f6;margin:0;">{{ $isAr ? 'الصفحة ستتحدث تلقائياً عند الانتهاء.' : 'The page will refresh automatically when done.' }}</p>
            </div>
        </div>

        @elseif(empty($pricedItems))
        {{-- ── No items returned (all filtered out by pricing engine) ────── --}}
        <div style="text-align:center;padding:48px 24px;border-radius:20px;border:2px dashed #e2e8f0;background:#f8fafc;">
            <div style="width:64px;height:64px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 style="font-size:16px;font-weight:700;color:#334155;margin:0 0 8px;">{{ $isAr ? 'لم نتمكن من تسعير العناصر' : 'Pricing unavailable for these items' }}</h3>
            <p style="font-size:13px;color:#94a3b8;max-width:360px;margin:0 auto 24px;">{{ $isAr ? 'لم يتم العثور على أسعار للعناصر المحددة. يرجى المراجعة أو إضافة عناصر مختلفة.' : 'No prices were found for the selected items. Please go back and adjust your items.' }}</p>
            <button type="button" wire:click="goBack"
                style="display:inline-flex;align-items:center;gap:8px;border-radius:12px;border:1px solid #e2e8f0;background:#fff;padding:10px 22px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                {{ $isAr ? 'العودة وتعديل العناصر' : 'Back to adjust items' }}
            </button>
        </div>

        @else
        {{-- ── Summary stats strip ─────────────────────────────────────────── --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div style="border-radius:16px;border:1px solid #d1fae5;background:linear-gradient(135deg,#ecfdf5,#f0fdf4);padding:18px 20px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#059669;margin:0 0 6px;">{{ $isAr ? 'عناصر مسعّرة' : 'Priced Items' }}</p>
                <p style="font-size:26px;font-weight:800;color:#064e3b;margin:0;line-height:1;">{{ $pricedCount }}</p>
            </div>
            <div style="border-radius:16px;border:1px solid #fee2e2;background:linear-gradient(135deg,#fef2f2,#fff5f5);padding:18px 20px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#dc2626;margin:0 0 6px;">{{ $isAr ? 'بدون سعر' : 'Unpriced' }}</p>
                <p style="font-size:26px;font-weight:800;color:#7f1d1d;margin:0;line-height:1;">{{ $unpricedCount }}</p>
            </div>
            <div style="border-radius:16px;border:1px solid #d1fae5;background:linear-gradient(135deg,#059669,#047857);padding:18px 20px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#a7f3d0;margin:0 0 4px;">{{ $isAr ? 'الإجمالي التقديري' : 'Est. Total' }}</p>
                <p style="font-size:20px;font-weight:800;color:#fff;margin:0;line-height:1;">{{ number_format($quotationTotal, 0) }} <span style="font-size:13px;font-weight:500;opacity:.8;">SAR</span></p>
                <p style="font-size:10px;color:#6ee7b7;margin:4px 0 0;">{{ $isAr ? 'شامل 15% ضريبة: '.number_format($quotationTotal*1.15,0).' SAR' : 'incl. 15% VAT: '.number_format($quotationTotal*1.15,0).' SAR' }}</p>
            </div>
        </div>

        {{-- ── Guest login overlay wrapper ──────────────────────────────── --}}
        <div class="relative">
            <div class="{{ $guestMode && $showGuestLoginOverlay ? 'select-none pointer-events-none' : '' }}">

                {{-- Table card --}}
                <div style="border-radius:20px;border:1px solid #e2e8f0;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden;{{ $guestMode && $showGuestLoginOverlay ? 'filter:blur(3px);' : '' }}">

                    {{-- Table header --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:16px 24px;background:#fafafa;">
                        <h2 style="font-size:13px;font-weight:700;color:#1e293b;margin:0;">{{ $isAr ? 'تفاصيل التسعير' : 'Pricing Breakdown' }}</h2>
                        <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ count($pricedItems) }} {{ $isAr ? 'عنصر' : 'items' }}</span>
                    </div>

                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                                    <th style="padding:10px 20px;text-align:start;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;min-width:220px;">{{ $isAr ? 'الوصف' : 'Description' }}</th>
                                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;width:70px;">{{ $isAr ? 'الكمية' : 'Qty' }}</th>
                                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;width:70px;">{{ $isAr ? 'الوحدة' : 'Unit' }}</th>
                                    <th style="padding:10px 20px;text-align:end;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;width:120px;">{{ $isAr ? 'سعر الوحدة' : 'Unit Price' }}</th>
                                    <th style="padding:10px 20px;text-align:end;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;width:120px;">{{ $isAr ? 'الإجمالي' : 'Total' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pricedItems as $i => $pi)
                                <tr style="border-bottom:1px solid #f8fafc;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 20px;color:#334155;font-weight:500;line-height:1.4;">{{ $pi['description'] }}</td>
                                    <td style="padding:14px 16px;text-align:center;color:#64748b;">{{ number_format($pi['quantity'], 2) }}</td>
                                    <td style="padding:14px 16px;text-align:center;color:#94a3b8;font-size:12px;">{{ $pi['unit'] ?: '—' }}</td>
                                    <td style="padding:14px 20px;text-align:end;">
                                        @if($pi['unit_price'])
                                            <span style="font-weight:600;color:#1e293b;">{{ number_format($pi['unit_price'], 2) }}</span>
                                            <span style="font-size:11px;color:#94a3b8;margin-{{ $isAr ? 'right' : 'left' }}:3px;">SAR</span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#dc2626;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;border:1px solid #fecaca;">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                {{ $isAr ? 'لم يُسعَّر' : 'No price' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;text-align:end;font-weight:700;color:{{ $pi['line_total'] > 0 ? '#1e293b' : '#cbd5e1' }};">
                                        {{ $pi['line_total'] > 0 ? number_format($pi['line_total'], 2).' SAR' : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid #e2e8f0;background:#fafafa;">
                                    <td colspan="4" style="padding:12px 20px;text-align:end;font-size:13px;font-weight:600;color:#64748b;">{{ $isAr ? 'المجموع' : 'Subtotal' }}</td>
                                    <td style="padding:12px 20px;text-align:end;font-weight:700;color:#1e293b;">{{ number_format($quotationTotal, 2) }} SAR</td>
                                </tr>
                                <tr style="background:#fafafa;">
                                    <td colspan="4" style="padding:8px 20px;text-align:end;font-size:12px;color:#94a3b8;">{{ $isAr ? 'ضريبة القيمة المضافة (15%)' : 'VAT (15%)' }}</td>
                                    <td style="padding:8px 20px;text-align:end;font-size:13px;color:#64748b;">{{ number_format($quotationTotal * 0.15, 2) }} SAR</td>
                                </tr>
                                <tr style="background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border-top:1px solid #d1fae5;">
                                    <td colspan="4" style="padding:16px 20px;text-align:end;font-size:13px;font-weight:700;color:#065f46;">{{ $isAr ? 'الإجمالي شامل الضريبة' : 'Grand Total incl. VAT' }}</td>
                                    <td style="padding:16px 20px;text-align:end;font-size:18px;font-weight:800;color:#065f46;">{{ number_format($quotationTotal * 1.15, 2) }} <span style="font-size:13px;font-weight:500;">SAR</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Guest login overlay ───────────────────────────────────── --}}
            @if($guestMode && $showGuestLoginOverlay)
            <div style="position:absolute;inset:0;z-index:10;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:20px;background:rgba(255,255,255,0.88);backdrop-filter:blur(8px);padding:40px 24px;text-align:center;">
                {{-- Lock icon --}}
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#059669,#047857);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(5,150,105,.3);">
                    <svg width="30" height="30" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 10px;">
                    {{ $isAr ? 'سجّل الدخول لكشف الأسعار' : 'Sign in to Reveal Prices' }}
                </h3>
                <p style="font-size:13px;color:#64748b;max-width:340px;margin:0 auto 28px;line-height:1.6;">
                    {{ $isAr ? 'جدول كميات BOQ الخاص بك جاهز. سيتم ربطه بحسابك فور تسجيل الدخول لتتمكن من تحميل PDF.' : 'Your BOQ is ready. Sign in to unlock full prices and download your PDF quotation.' }}
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;">
                    <button type="button" wire:click="redirectToLogin"
                        style="display:inline-flex;align-items:center;gap:8px;border-radius:12px;background:#059669;color:#fff;padding:12px 28px;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(5,150,105,.3);transition:all .2s;"
                        onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        {{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}
                    </button>
                    <a href="{{ route('enduser.register') }}"
                        style="display:inline-flex;align-items:center;gap:8px;border-radius:12px;border:1.5px solid #e2e8f0;background:#fff;color:#334155;padding:12px 28px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        {{ $isAr ? 'إنشاء حساب مجاني' : 'Create Free Account' }}
                    </a>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Actions row ──────────────────────────────────────────────── --}}
        @if(!$pricesFetching)
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:4px;">
            <button type="button" wire:click="goBack"
                style="display:inline-flex;align-items:center;gap:8px;border-radius:12px;border:1px solid #e2e8f0;background:#fff;padding:11px 22px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                {{ $isAr ? 'السابق' : 'Back' }}
            </button>
            @if(!($guestMode && $showGuestLoginOverlay) && !empty($pricedItems))
            <button type="button" wire:click="proceedToAddress"
                style="display:inline-flex;align-items:center;gap:8px;border-radius:12px;background:#059669;color:#fff;padding:11px 26px;font-size:13px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(5,150,105,.25);">
                {{ $isAr ? 'التالي: العنوان والدفع' : 'Next: Address & Payment' }}
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STEP 4 – العنوان والدفع  (Address & Payment)
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep === 4)
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left: Address + Payment --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- ─── عنوان التوصيل (inline radio picker) ──────────────────── --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                    {{-- Header --}}
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'عنوان التوصيل' : 'Delivery Address' }}</h2>
                        <p class="text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? 'اختر كيف تريد إدخال عنوان التوصيل' : 'Choose how to enter the delivery address' }}</p>
                    </div>

                    {{-- ── Address type selector ─────────────────────────────── --}}
                    <div class="p-6 space-y-5">

                        {{-- Radio cards --}}
                        <div class="grid grid-cols-2 gap-4">

                            {{-- National Address --}}
                            <label class="relative flex flex-col items-center text-center cursor-pointer gap-3 rounded-2xl border-2 px-4 py-5 transition {{ $deliveryAddressMode === 'national' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:border-emerald-200 hover:bg-slate-50 bg-white' }}">
                                <input type="radio" wire:model.live="deliveryAddressMode" value="national" class="sr-only">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $deliveryAddressMode === 'national' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </span>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold {{ $deliveryAddressMode === 'national' ? 'text-emerald-800' : 'text-slate-700' }}">{{ app()->getLocale() === 'ar' ? 'العنوان الوطني' : 'National Address' }}</p>
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold tracking-wide text-emerald-700">{{ app()->getLocale() === 'ar' ? 'موصى به' : 'Recommended' }}</span>
                                    <p class="text-[11px] leading-relaxed {{ $deliveryAddressMode === 'national' ? 'text-emerald-600' : 'text-slate-400' }}">{{ app()->getLocale() === 'ar' ? 'رمز وصل المكون من 8 خانات' : '8-char Saudi Post code' }}</p>
                                </div>
                                @if($deliveryAddressMode === 'national')
                                    <svg class="absolute top-3 end-3 h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @endif
                            </label>

                            {{-- Detailed Address --}}
                            <label class="relative flex flex-col items-center text-center cursor-pointer gap-3 rounded-2xl border-2 px-4 py-5 transition {{ $deliveryAddressMode === 'detailed' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:border-emerald-200 hover:bg-slate-50 bg-white' }}">
                                <input type="radio" wire:model.live="deliveryAddressMode" value="detailed" class="sr-only">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $deliveryAddressMode === 'detailed' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold {{ $deliveryAddressMode === 'detailed' ? 'text-emerald-800' : 'text-slate-700' }}">{{ app()->getLocale() === 'ar' ? 'عنوان تفصيلي' : 'Detailed Address' }}</p>
                                    <p class="text-[11px] leading-relaxed {{ $deliveryAddressMode === 'detailed' ? 'text-emerald-600' : 'text-slate-400' }}">{{ app()->getLocale() === 'ar' ? 'شارع، حي، مدينة، منطقة' : 'Street, district, city' }}</p>
                                </div>
                                @if($deliveryAddressMode === 'detailed')
                                    <svg class="absolute top-3 end-3 h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @endif
                            </label>

                        </div>

                        {{-- National Address Form --}}
                        @if($deliveryAddressMode === 'national')
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 space-y-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                {{ app()->getLocale() === 'ar' ? 'العنوان الوطني' : 'National Address' }}
                            </span>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'رمز العنوان الوطني' : 'National Address Code' }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.blur="deliveryShortAddress"
                                    placeholder="RJHH6392" maxlength="8"
                                    class="h-12 w-full rounded-xl border bg-white px-4 font-mono text-base uppercase tracking-widest text-slate-700 shadow-sm outline-none transition placeholder:normal-case placeholder:text-slate-400 @error('deliveryShortAddress') border-red-400 ring-2 ring-red-100 @else border-emerald-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                <p class="mt-1.5 text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? '8 خانات من الحروف والأرقام الإنجليزية — من wasel.com.sa' : '8 alphanumeric chars (e.g. RJHH6392) from wasel.com.sa' }}</p>
                                @error('deliveryShortAddress')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @endif

                        {{-- Detailed Address Form --}}
                        @if($deliveryAddressMode === 'detailed')
                        <div class="space-y-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ app()->getLocale() === 'ar' ? 'أدخل تفاصيل العنوان' : 'Enter Address Details' }}
                            </span>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'الشارع' : 'Street' }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.blur="deliveryStreet"
                                    placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: طريق الملك فهد' : 'e.g. King Fahd Road' }}"
                                    class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryStreet') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                @error('deliveryStreet')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'الحي' : 'District' }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="deliveryDistrict"
                                        placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: العليا' : 'e.g. Al Olaya' }}"
                                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryDistrict') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                    @error('deliveryDistrict')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="deliveryCity"
                                        placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: الرياض' : 'e.g. Riyadh' }}"
                                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryCity') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                    @error('deliveryCity')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Region' }} <span class="text-red-500">*</span></label>
                                    <select wire:model.live="deliveryRegion"
                                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryRegion') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Region' }}</option>
                                        @foreach(['الرياض','مكة المكرمة','المدينة المنورة','القصيم','المنطقة الشرقية','عسير','تبوك','حائل','الحدود الشمالية','جازان','نجران','الباحة','الجوف'] as $region)
                                            <option value="{{ $region }}" @selected($deliveryRegion === $region)>{{ $region }}</option>
                                        @endforeach
                                    </select>
                                    @error('deliveryRegion')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ app()->getLocale() === 'ar' ? 'الرمز البريدي' : 'Postal Code' }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="deliveryPostalCode"
                                        placeholder="12345" maxlength="5" inputmode="numeric"
                                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition @error('deliveryPostalCode') border-red-400 ring-2 ring-red-100 @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror">
                                    @error('deliveryPostalCode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- Right: Order Summary sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-800">{{ app()->getLocale()==='ar'?'ملخص الطلب':'Order Summary' }}</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'المشروع':'Project' }}</span><span class="font-medium text-slate-700 max-w-[130px] truncate">{{ $projectName ?: '—' }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'عدد العناصر':'Items' }}</span><span class="font-medium text-slate-700">{{ count($pricedItems) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'المجموع':'Subtotal' }}</span><span class="font-medium text-slate-700">{{ number_format($quotationTotal, 2) }} SAR</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">{{ app()->getLocale()==='ar'?'ضريبة (15%)':'VAT (15%)' }}</span><span class="font-medium text-slate-700">{{ number_format($quotationTotal * 0.15, 2) }} SAR</span></div>
                        <div class="border-t border-slate-100 pt-3 flex justify-between"><span class="font-bold text-slate-800">{{ app()->getLocale()==='ar'?'الإجمالي':'Grand Total' }}</span><span class="font-bold text-emerald-600 text-lg">{{ number_format($quotationTotal * 1.15, 2) }} SAR</span></div>
                    </div>
                    <div class="border-t border-slate-100 px-6 py-5 space-y-3">
                        <button type="button" wire:click="placeOrder" wire:loading.attr="disabled"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95 disabled:opacity-60">
                            <svg wire:loading wire:target="placeOrder" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <svg wire:loading.remove wire:target="placeOrder" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span wire:loading.remove wire:target="placeOrder">{{ app()->getLocale()==='ar'?'تأكيد الطلب':'Place Order' }}</span>
                            <span wire:loading wire:target="placeOrder">{{ app()->getLocale()==='ar'?'جاري الإنشاء...':'Placing...' }}</span>
                        </button>
                        <button type="button" wire:click="goBack" class="w-full text-center text-xs text-slate-400 hover:text-slate-600 py-1">
                            {{ app()->getLocale()==='ar'?'← العودة لمراجعة الأسعار':'← Back to price review' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STEP 5 – تأكيد الطلب  (Amazon-style Confirmation)
    ══════════════════════════════════════════════════════ --}}
    @if($currentStep === 5)
    <div class="flex flex-col items-center py-12 text-center space-y-8">

        <div class="relative flex h-28 w-28 items-center justify-center">
            <div class="absolute inset-0 animate-ping rounded-full bg-emerald-100 opacity-60"></div>
            <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-emerald-500 shadow-xl shadow-emerald-200">
                <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ app()->getLocale()==='ar'?'تم إنشاء الطلب بنجاح! 🎉':'Order Placed Successfully! 🎉' }}</h1>
            <p class="mt-2 text-sm text-slate-500 max-w-md">{{ app()->getLocale()==='ar'?'تم استلام طلبك وسيتم مراجعته من قِبل فريقنا في أقرب وقت ممكن.':'Your order has been received and will be reviewed by our team shortly.' }}</p>
        </div>

        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-sm text-start overflow-hidden">
            <div class="bg-emerald-600 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">{{ app()->getLocale()==='ar'?'رقم الطلب':'Order Number' }}</p>
                <p class="mt-1 text-xl font-bold text-white">{{ $orderNo }}</p>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'المشروع':'Project' }}</span><span class="text-sm font-semibold text-slate-700">{{ $projectName }}</span></div>
                <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'عدد العناصر':'Items' }}</span><span class="text-sm font-semibold text-slate-700">{{ count($pricedItems) }}</span></div>
                <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'طريقة الدفع':'Payment' }}</span><span class="text-sm font-semibold text-slate-700">{{ match($paymentMethod){'bank_transfer'=>app()->getLocale()==='ar'?'تحويل بنكي':'Bank Transfer','cash'=>app()->getLocale()==='ar'?'نقد':'Cash',default=>app()->getLocale()==='ar'?'ائتمان':'Credit'} }}</span></div>
                <div class="flex justify-between px-6 py-3.5"><span class="text-sm text-slate-500">{{ app()->getLocale()==='ar'?'عنوان التوصيل':'Delivery' }}</span><span class="text-sm font-semibold text-slate-700 max-w-[180px] text-end">{{ implode(', ', array_filter([$deliveryStreet, $deliveryDistrict, $deliveryCity])) }}</span></div>
                <div class="flex justify-between bg-emerald-50 px-6 py-4"><span class="text-sm font-bold text-emerald-700">{{ app()->getLocale()==='ar'?'الإجمالي الكلي':'Grand Total' }}</span><span class="text-lg font-bold text-emerald-700">{{ number_format($orderGrandTotal, 2) }} SAR</span></div>
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-start max-w-md">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
            <p class="text-sm text-amber-700">{{ app()->getLocale()==='ar'?'سيتواصل معك فريق المبيعات لتأكيد الطلب وتفاصيل الدفع والتوصيل.':'Our sales team will contact you to confirm the order, payment, and delivery details.' }}</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            @if($orderUuid)
                <a href="{{ route('enduser.orders.show', $orderUuid) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ app()->getLocale()==='ar'?'عرض الطلب':'View Order' }}
                </a>
            @endif
            <a href="{{ route('enduser.boqs.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ app()->getLocale()==='ar'?'العودة لجداول الكميات':'Back to BOQs' }}
            </a>
            <a href="{{ route('enduser.boqs.create') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                {{ app()->getLocale()==='ar'?'+ إنشاء جدول كميات جديد':'+ New BOQ' }}
            </a>
        </div>
    </div>
    @endif

</div>
