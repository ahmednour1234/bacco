<div>
    <form wire:submit="save" class="space-y-6">

        {{-- ── AI assist ─────────────────────────────────────────────────── --}}
        @if($aiAvailable)
        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('app.seo_ai_title') }}</h3>
                        <p class="mt-0.5 max-w-xl text-xs leading-relaxed text-slate-500">{{ __('app.seo_ai_hint') }}</p>
                    </div>
                </div>

                <button type="button" wire:click="generateWithAi"
                        wire:loading.attr="disabled" wire:target="generateWithAi"
                        class="inline-flex h-11 shrink-0 items-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg wire:loading wire:target="generateWithAi" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="generateWithAi">{{ __('app.seo_ai_button') }}</span>
                    <span wire:loading wire:target="generateWithAi">{{ __('app.seo_ai_working') }}</span>
                </button>
            </div>

            <div class="mt-4">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">{{ __('app.seo_ai_focus') }}</label>
                <input type="text" wire:model="aiFocus"
                       placeholder="{{ __('app.seo_ai_focus_placeholder') }}"
                       class="h-10 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>

            @if($aiError)
            <div class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <p class="text-xs leading-relaxed text-red-800">{{ $aiError }}</p>
            </div>
            @endif

            @if($aiGenerated)
            <div class="mt-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-xs leading-relaxed text-amber-800">{{ __('app.seo_ai_review') }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ── English / Arabic tabs ─────────────────────────────────────── --}}
        <div x-data="{ tab: 'en' }" class="rounded-2xl border border-slate-200 bg-white">
            <div class="flex border-b border-slate-100">
                <button type="button" @click="tab = 'en'"
                        :class="tab === 'en' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="border-b-2 px-5 py-3 text-sm font-semibold transition">English</button>
                <button type="button" @click="tab = 'ar'"
                        :class="tab === 'ar' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="border-b-2 px-5 py-3 text-sm font-semibold transition">العربية</button>
            </div>

            {{-- English panel --}}
            <div x-show="tab === 'en'" class="space-y-5 p-6">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_title') }} (EN)</label>
                    <input type="text" wire:model.live.debounce.300ms="title_en" dir="ltr"
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('title_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] font-medium" x-data="{ get n() { return ($wire.title_en || '').length } }" :class="n > 60 ? 'text-red-600' : (n > 54 ? 'text-amber-600' : 'text-slate-400')"><span x-text="n"></span> / 60</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_meta_desc') }} (EN)</label>
                    <textarea wire:model.live.debounce.300ms="meta_desc_en" rows="3" dir="ltr"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('meta_desc_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] font-medium" x-data="{ get n() { return ($wire.meta_desc_en || '').length } }" :class="n > 160 ? 'text-red-600' : (n > 144 ? 'text-amber-600' : 'text-slate-400')"><span x-text="n"></span> / 160</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_keywords') }} (EN)</label>
                    <input type="text" wire:model="keywords_en" dir="ltr" placeholder="keyword1, keyword2, ..."
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('keywords_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_schema') }} (EN)</label>
                    <textarea wire:model="schema_en" rows="5" dir="ltr" placeholder="{{ '{\"@context\":\"https://schema.org\", ...}' }}"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('schema_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Arabic panel --}}
            <div x-show="tab === 'ar'" x-cloak class="space-y-5 p-6" dir="rtl">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_title') }} (AR)</label>
                    <input type="text" wire:model.live.debounce.300ms="title_ar"
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('title_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] font-medium" x-data="{ get n() { return ($wire.title_ar || '').length } }" :class="n > 60 ? 'text-red-600' : (n > 54 ? 'text-amber-600' : 'text-slate-400')"><span x-text="n"></span> / 60</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_meta_desc') }} (AR)</label>
                    <textarea wire:model.live.debounce.300ms="meta_desc_ar" rows="3"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('meta_desc_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] font-medium" x-data="{ get n() { return ($wire.meta_desc_ar || '').length } }" :class="n > 160 ? 'text-red-600' : (n > 144 ? 'text-amber-600' : 'text-slate-400')"><span x-text="n"></span> / 160</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_keywords') }} (AR)</label>
                    <input type="text" wire:model="keywords_ar" placeholder="كلمة1، كلمة2، ..."
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('keywords_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_schema') }} (AR)</label>
                    <textarea wire:model="schema_ar" rows="5" dir="ltr" placeholder="{{ '{\"@context\":\"https://schema.org\", ...}' }}"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('schema_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Shared: Open Graph + status ───────────────────────────────── --}}
        <div class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
            <h3 class="text-sm font-semibold text-slate-700">{{ __('app.seo_social') }}</h3>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_og_image') }}</label>
                @if ($existingOgImage && ! $og_image)
                    <img src="{{ Storage::disk('public')->url($existingOgImage) }}" alt=""
                         class="mb-2 h-24 rounded-lg border border-slate-200 object-cover">
                @endif
                @if ($og_image)
                    <img src="{{ $og_image->temporaryUrl() }}" alt=""
                         class="mb-2 h-24 rounded-lg border border-slate-200 object-cover">
                @endif
                <input type="file" wire:model="og_image" accept="image/*"
                       class="block w-full text-sm text-slate-500 file:me-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                <div wire:loading wire:target="og_image" class="mt-1 text-xs text-slate-400">{{ __('app.uploading') }}…</div>
                @error('og_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_og_type') }}</label>
                <input type="text" wire:model="og_type" dir="ltr" placeholder="website"
                       class="h-11 w-full max-w-xs rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @error('og_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="active"
                       class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-sm font-medium text-slate-700">{{ __('app.active') }}</span>
            </label>
        </div>

        {{-- ── Actions ───────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.seo.index') }}" wire:navigate
               class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                {{ __('app.cancel') }}
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                    wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                <span wire:loading wire:target="save">{{ __('app.saving') }}…</span>
            </button>
        </div>
    </form>
</div>
