<div
    x-data="{ toast: null, showToast(m, t='success') { this.toast={message:m,type:t}; setTimeout(()=>this.toast=null,4000); } }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>
    {{-- Toast --}}
    <div x-show="toast !== null" x-cloak x-transition
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type===`+"`"+`success`+"`"+`
            'bg-red-50 text-red-700 border border-red-200': toast?.type===`+"`"+`error`+"`"+`
            'bg-amber-50 text-amber-700 border border-amber-200': toast?.type===`+"`"+`warning`+"`"+`
        }">
        <span x-text="toast?.message"></span>
        <button @click="toast=null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
