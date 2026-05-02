<form wire:submit="save" class="space-y-6" x-data="articleForm()" x-init="init()">
    {{-- ── Basic Info ──────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-sm font-semibold text-slate-700 uppercase tracking-wide">Article Names</h3>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            {{-- name_en --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Name <span class="text-slate-400 font-normal">(English)</span>
                </label>
                <input type="text" wire:model.blur="name_en"
                       placeholder="e.g. Security Best Practices"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('name_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- name_ar --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Name <span class="text-slate-400 font-normal">(Arabic)</span>
                </label>
                <input type="text" wire:model.blur="name_ar" dir="rtl"
                       placeholder="مثال: أفضل ممارسات الأمان"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('name_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- title_en --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Title <span class="text-slate-400 font-normal">(English)</span>
                </label>
                <input type="text" wire:model.blur="title_en"
                       placeholder="e.g. Keeping Your Qimta Account Secure"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('title_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- title_ar --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Title <span class="text-slate-400 font-normal">(Arabic)</span>
                </label>
                <input type="text" wire:model.blur="title_ar" dir="rtl"
                       placeholder="مثال: الحفاظ على أمان حساب قيمتا"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('title_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ── HTML Descriptions ───────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-sm font-semibold text-slate-700 uppercase tracking-wide">Content (HTML Editor)</h3>

        {{-- Hidden file inputs for media insertion --}}
        <input type="file" x-ref="mediaEnInput"  accept="image/*,video/*" class="hidden" @change="insertMediaEn($event)">
        <input type="file" x-ref="mediaArInput"  accept="image/*,video/*" class="hidden" @change="insertMediaAr($event)">

        {{-- ── English Editor ──────────────────────────────── --}}
        <div class="mb-8">
            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                Description
                <span class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-600">EN</span>
            </label>

            {{-- Toolbar EN --}}
            <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 px-3 py-2">

                {{-- Format --}}
                <select @change="execVal('en','formatBlock',$event.target.value); $event.target.value=''" class="editor-select" title="Block format">
                    <option value="">Paragraph</option>
                    <option value="h1">H1</option>
                    <option value="h2">H2</option>
                    <option value="h3">H3</option>
                    <option value="h4">H4</option>
                    <option value="pre">Code</option>
                </select>

                {{-- Font size --}}
                <select @change="execVal('en','fontSize',$event.target.value); $event.target.value=''" class="editor-select" title="Font size">
                    <option value="">Size</option>
                    <option value="1">Tiny</option>
                    <option value="2">Small</option>
                    <option value="3">Normal</option>
                    <option value="4">Large</option>
                    <option value="5">X-Large</option>
                    <option value="6">XX-Large</option>
                </select>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                {{-- Style --}}
                <button type="button" @click="exec('en','bold')"          class="editor-btn font-bold"  title="Bold">B</button>
                <button type="button" @click="exec('en','italic')"        class="editor-btn italic"      title="Italic">I</button>
                <button type="button" @click="exec('en','underline')"     class="editor-btn underline"   title="Underline">U</button>
                <button type="button" @click="exec('en','strikeThrough')" class="editor-btn line-through" title="Strikethrough">S</button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                {{-- Align --}}
                <button type="button" @click="exec('en','justifyLeft')"   class="editor-btn" title="Align left">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>
                </button>
                <button type="button" @click="exec('en','justifyCenter')" class="editor-btn" title="Center">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z"/></svg>
                </button>
                <button type="button" @click="exec('en','justifyRight')"  class="editor-btn" title="Align right">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z"/></svg>
                </button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                {{-- Lists --}}
                <button type="button" @click="exec('en','insertUnorderedList')" class="editor-btn" title="Bullet list">• List</button>
                <button type="button" @click="exec('en','insertOrderedList')"   class="editor-btn" title="Numbered list">1. List</button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                {{-- Link --}}
                <button type="button" @click="insertLink('en')" class="editor-btn" title="Insert link">
                    <svg class="h-3.5 w-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Link
                </button>

                {{-- Media --}}
                <button type="button" @click="$refs.mediaEnInput.click()" class="editor-btn text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100" title="Insert image or video">
                    <svg class="h-3.5 w-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Media
                </button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                {{-- Clear --}}
                <button type="button" @click="exec('en','removeFormat')" class="editor-btn text-red-500" title="Clear formatting">✕</button>
            </div>

            {{-- Editor body EN --}}
            <div id="editor-en"
                 contenteditable="true"
                 dir="ltr"
                 x-ref="editorEn"
                 wire:ignore
                 @input="syncEnDebounced()"
                 @blur="syncEn()"
                 class="editor-body"
                 style="min-height:360px; resize:vertical; overflow:auto;"
            ></div>
            @error('desc_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- ── Arabic Editor ────────────────────────────────── --}}
        <div>
            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                Description
                <span class="rounded-md bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-600">AR</span>
            </label>

            {{-- Toolbar AR --}}
            <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 px-3 py-2">

                <select @change="execVal('ar','formatBlock',$event.target.value); $event.target.value=''" class="editor-select" title="Block format">
                    <option value="">Paragraph</option>
                    <option value="h1">H1</option>
                    <option value="h2">H2</option>
                    <option value="h3">H3</option>
                    <option value="h4">H4</option>
                    <option value="pre">Code</option>
                </select>

                <select @change="execVal('ar','fontSize',$event.target.value); $event.target.value=''" class="editor-select" title="Font size">
                    <option value="">Size</option>
                    <option value="1">Tiny</option>
                    <option value="2">Small</option>
                    <option value="3">Normal</option>
                    <option value="4">Large</option>
                    <option value="5">X-Large</option>
                    <option value="6">XX-Large</option>
                </select>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                <button type="button" @click="exec('ar','bold')"          class="editor-btn font-bold"   title="Bold">B</button>
                <button type="button" @click="exec('ar','italic')"        class="editor-btn italic"       title="Italic">I</button>
                <button type="button" @click="exec('ar','underline')"     class="editor-btn underline"    title="Underline">U</button>
                <button type="button" @click="exec('ar','strikeThrough')" class="editor-btn line-through" title="Strikethrough">S</button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                <button type="button" @click="exec('ar','justifyRight')"  class="editor-btn" title="Align right (RTL start)">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z"/></svg>
                </button>
                <button type="button" @click="exec('ar','justifyCenter')" class="editor-btn" title="Center">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z"/></svg>
                </button>
                <button type="button" @click="exec('ar','justifyLeft')"   class="editor-btn" title="Align left">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>
                </button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                <button type="button" @click="exec('ar','insertUnorderedList')" class="editor-btn" title="Bullet list">• List</button>
                <button type="button" @click="exec('ar','insertOrderedList')"   class="editor-btn" title="Numbered list">1. List</button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                <button type="button" @click="insertLink('ar')" class="editor-btn" title="Insert link">
                    <svg class="h-3.5 w-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Link
                </button>

                <button type="button" @click="$refs.mediaArInput.click()" class="editor-btn text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100" title="Insert image or video">
                    <svg class="h-3.5 w-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Media
                </button>

                <span class="self-stretch border-l border-slate-200 mx-0.5"></span>

                <button type="button" @click="exec('ar','removeFormat')" class="editor-btn text-red-500" title="Clear formatting">✕</button>
            </div>

            <div id="editor-ar"
                 contenteditable="true"
                 dir="rtl"
                 x-ref="editorAr"
                 wire:ignore
                 @input="syncArDebounced()"
                 @blur="syncAr()"
                 class="editor-body"
                 style="min-height:360px; resize:vertical; overflow:auto;"
            ></div>
            @error('desc_ar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- ── Image Upload ────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-sm font-semibold text-slate-700 uppercase tracking-wide">Featured Image</h3>

        @if ($existingImage)
            <div class="mb-4 flex items-center gap-4">
                <img src="{{ Storage::url($existingImage) }}"
                     alt="Current image"
                     class="h-24 w-24 rounded-xl object-cover border border-slate-200">
                <p class="text-xs text-slate-500">Current image. Upload a new one below to replace it.</p>
            </div>
        @endif

        @if ($image)
            <div class="mb-4">
                <img src="{{ $image->temporaryUrl() }}"
                     alt="Preview"
                     class="h-24 w-24 rounded-xl object-cover border border-emerald-200">
                <p class="mt-1 text-xs text-emerald-600">New image preview</p>
            </div>
        @endif

        <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-8 hover:border-emerald-300 hover:bg-emerald-50/40 transition-colors">
            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm text-slate-500">Click to upload image <span class="text-xs text-slate-400">(JPG, PNG, WebP — max 4MB)</span></span>
            <input type="file" wire:model="image" accept="image/*" class="hidden">
        </label>
        @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        <div wire:loading wire:target="image" class="mt-2 text-xs text-emerald-600">Uploading…</div>
    </div>

    {{-- ── Status ──────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <label class="inline-flex cursor-pointer items-center gap-3">
            <input type="checkbox" wire:model="active"
                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm font-medium text-slate-700">Published (visible on site)</span>
        </label>
    </div>

    {{-- ── Actions ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <button type="submit"
                class="inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="save,image">
            <svg wire:loading wire:target="save" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ $isEditing ? 'Update Article' : 'Create Article' }}
        </button>
        <a href="{{ route('admin.articles.index') }}" wire:navigate
           class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
            Cancel
        </a>
    </div>
</form>

<style>
/* ── Toolbar controls ──────────────────────────────────────────────── */
.editor-btn {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 4px 9px;
    font-size: 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    cursor: pointer;
    white-space: nowrap;
    transition: background .12s, color .12s;
    line-height: 1.4;
}
.editor-btn:hover { background: #f1f5f9; color: #0f172a; }
.editor-select {
    height: 28px;
    padding: 0 6px;
    font-size: 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    cursor: pointer;
    outline: none;
}
.editor-select:focus { border-color: #34d399; }

/* ── Editor body ────────────────────────────────────────────────────── */
.editor-body {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
    background: #fff;
    padding: 16px;
    font-size: 14px;
    color: #1e293b;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.editor-body:focus {
    outline: none;
    border-color: #34d399;
    box-shadow: 0 0 0 3px rgba(52,211,153,.15);
}

/* ── Content styles inside editor ───────────────────────────────────── */
.editor-body h1 { font-size: 1.6rem;  font-weight: 800; margin: 1rem 0 .6rem; }
.editor-body h2 { font-size: 1.3rem;  font-weight: 700; margin: .9rem 0 .5rem; }
.editor-body h3 { font-size: 1.1rem;  font-weight: 600; margin: .75rem 0 .4rem; }
.editor-body h4 { font-size: 1rem;    font-weight: 600; margin: .6rem 0 .35rem; }
.editor-body p  { margin-bottom: .6rem; line-height: 1.7; }
.editor-body ul { list-style: disc;    padding-left: 1.4rem; margin: .6rem 0; }
.editor-body ol { list-style: decimal; padding-left: 1.4rem; margin: .6rem 0; }
.editor-body li { margin-bottom: .3rem; }
.editor-body pre { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; font-family:monospace; font-size:13px; overflow:auto; }
.editor-body a { color:#059669; text-decoration:underline; }
.editor-body img  { max-width:100%; height:auto; border-radius:8px; margin:8px 0; display:block; }
.editor-body video { max-width:100%; border-radius:8px; margin:8px 0; display:block; }

/* Selected media outline */
.editor-body img:focus,
.editor-body img.selected { outline: 2px solid #34d399; outline-offset: 2px; }
</style>

<script>
function articleForm() {
    return {
        _timerEn: null,
        _timerAr: null,
        _savedRangeEn: null,
        _savedRangeAr: null,

        init() {
            const descEn = @js($desc_en ?? '');
            const descAr = @js($desc_ar ?? '');
            if (descEn) this.$refs.editorEn.innerHTML = descEn;
            if (descAr) this.$refs.editorAr.innerHTML = descAr;

            // Save selection range before toolbar buttons steal focus
            this.$refs.editorEn.addEventListener('mouseup', () => this._saveRange('en'));
            this.$refs.editorEn.addEventListener('keyup',   () => this._saveRange('en'));
            this.$refs.editorAr.addEventListener('mouseup', () => this._saveRange('ar'));
            this.$refs.editorAr.addEventListener('keyup',   () => this._saveRange('ar'));
        },

        _saveRange(lang) {
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            const range = sel.getRangeAt(0);
            const el = lang === 'en' ? this.$refs.editorEn : this.$refs.editorAr;
            if (el.contains(range.commonAncestorContainer)) {
                if (lang === 'en') this._savedRangeEn = range.cloneRange();
                else               this._savedRangeAr = range.cloneRange();
            }
        },

        _restoreRange(lang) {
            const saved = lang === 'en' ? this._savedRangeEn : this._savedRangeAr;
            const el    = lang === 'en' ? this.$refs.editorEn : this.$refs.editorAr;
            el.focus();
            if (saved) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(saved);
            }
        },

        exec(lang, cmd) {
            this._restoreRange(lang);
            document.execCommand(cmd, false);
            lang === 'en' ? this.syncEn() : this.syncAr();
        },

        execVal(lang, cmd, val) {
            if (!val) return;
            this._restoreRange(lang);
            document.execCommand(cmd, false, val);
            lang === 'en' ? this.syncEn() : this.syncAr();
        },

        insertLink(lang) {
            const url = prompt('Enter URL (include https://):');
            if (!url) return;
            const text = prompt('Link text (leave blank to use URL):') || url;
            this._restoreRange(lang);
            document.execCommand('insertHTML', false, `<a href="${url}" target="_blank" rel="noopener">${text}</a>`);
            lang === 'en' ? this.syncEn() : this.syncAr();
        },

        insertMediaEn(event) {
            const file = event.target.files[0];
            if (!file) return;
            event.target.value = '';
            this._insertMediaFile('en', file);
        },

        insertMediaAr(event) {
            const file = event.target.files[0];
            if (!file) return;
            event.target.value = '';
            this._insertMediaFile('ar', file);
        },

        _insertMediaFile(lang, file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const src = e.target.result;
                let html = '';

                if (file.type.startsWith('image/')) {
                    html = `<img src="${src}" alt="${file.name}" style="max-width:100%;border-radius:8px;margin:8px 0;">`;
                } else if (file.type.startsWith('video/')) {
                    html = `<video src="${src}" controls style="max-width:100%;border-radius:8px;margin:8px 0;"></video>`;
                } else {
                    alert('Unsupported file type. Please use an image or video.');
                    return;
                }

                this._restoreRange(lang);
                document.execCommand('insertHTML', false, html);
                lang === 'en' ? this.syncEn() : this.syncAr();
            };
            reader.readAsDataURL(file);
        },

        syncEn() {
            @this.setDescEn(this.$refs.editorEn.innerHTML);
        },
        syncAr() {
            @this.setDescAr(this.$refs.editorAr.innerHTML);
        },

        syncEnDebounced() {
            clearTimeout(this._timerEn);
            this._timerEn = setTimeout(() => this.syncEn(), 600);
        },
        syncArDebounced() {
            clearTimeout(this._timerAr);
            this._timerAr = setTimeout(() => this.syncAr(), 600);
        },
    };
}
</script>
