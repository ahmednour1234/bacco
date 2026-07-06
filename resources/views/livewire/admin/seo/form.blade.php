<div>
    <form wire:submit="save" class="space-y-6">

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
                    <input type="text" wire:model="title_en" dir="ltr"
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('title_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_meta_desc') }} (EN)</label>
                    <textarea wire:model="meta_desc_en" rows="3" dir="ltr"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('meta_desc_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <input type="text" wire:model="title_ar"
                           class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    @error('title_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.seo_meta_desc') }} (AR)</label>
                    <textarea wire:model="meta_desc_ar" rows="3"
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    @error('meta_desc_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
