@props([
    'prop',           // Livewire property name
    'currentValue',   // Current PHP value (string|int|null)
    'options',        // array of ['v' => mixed, 'l' => string]
    'placeholder' => 'Select...',
    'label'       => null,
    'errorField'  => null,
])

@php $errorField = $errorField ?? $prop; @endphp

<div class="relative"
     x-data="{
         open: false,
         query: '',
         value: '',
         opts: [],
         get filtered() {
             return this.query === ''
                 ? this.opts
                 : this.opts.filter(o => o.l.toLowerCase().includes(this.query.toLowerCase()));
         },
         labelText() {
             const f = this.opts.find(o => String(o.v) === String(this.value));
             return f ? f.l : '{{ addslashes($placeholder) }}';
         },
         pick(v) {
             this.value = (v !== null && v !== undefined && v !== '') ? String(v) : '';
             $wire.set('{{ $prop }}', v);
             this.open = false;
             this.query = '';
         },
         clear() {
             this.value = '';
             $wire.set('{{ $prop }}', '');
             this.open = false;
             this.query = '';
         }
     }"
     x-init="opts = @js($options); value = @js($currentValue ?? '');"
     @click.outside="open = false">

    @if ($label)
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <button
        type="button"
        @click="open = !open; if (open) $nextTick(() => $refs['ss_{{ $prop }}']?.focus())"
        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition focus:outline-none"
        :class="open ? 'border-emerald-400 ring-2 ring-emerald-100' : 'hover:border-slate-300'">
        <span x-text="labelText()" :class="value !== '' ? 'text-slate-800' : 'text-slate-400'"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-150"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">

        {{-- Search input --}}
        <div class="border-b border-slate-100 p-2">
            <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-1.5">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    x-ref="ss_{{ $prop }}"
                    x-model="query"
                    type="search"
                    placeholder="Search…"
                    autocomplete="off"
                    class="w-full bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none"
                    @keydown.escape="open = false">
            </div>
        </div>

        {{-- Options list --}}
        <div class="max-h-48 overflow-y-auto py-1">
            <button type="button" @click="clear()"
                    class="w-full px-4 py-2.5 text-left text-sm italic text-slate-400 transition-colors hover:bg-slate-50">
                — None —
            </button>
            <template x-for="opt in filtered" :key="String(opt.v)">
                <button type="button" @click="pick(opt.v)"
                        class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-left text-sm transition-colors"
                        :class="String(value) === String(opt.v)
                            ? 'bg-emerald-50 text-emerald-700 font-medium'
                            : 'text-slate-700 hover:bg-slate-50'">
                    <span x-text="opt.l"></span>
                    <svg x-show="String(value) === String(opt.v)"
                         class="h-3.5 w-3.5 shrink-0 text-emerald-500"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </template>
            <p x-show="filtered.length === 0"
               class="px-4 py-3 text-center text-sm text-slate-400">No results</p>
        </div>
    </div>

    @error($errorField)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
