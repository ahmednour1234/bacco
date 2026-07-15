{{-- Spec-questions step: shown between confirmation and pricing when the AI
     flagged items as needing more information before an accurate price. Uses the
     component's $questionItems array (present in both CreateBoq and ShowBoq). --}}
@php $isAr = app()->getLocale() === 'ar'; @endphp
<div class="space-y-5">
    <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <h2 class="text-sm font-bold text-amber-800">
                    {{ $isAr ? 'بعض البنود تحتاج توضيحًا قبل التسعير' : 'Some items need clarification before pricing' }}
                </h2>
                <p class="mt-1 text-xs leading-relaxed text-amber-700">
                    {{ $isAr
                        ? 'عشان نطلّع تسعيرة دقيقة، محتاجين مواصفات إضافية للبنود التالية. جاوب على اللي تقدر عليه — البنود المكتملة تم اعتمادها تلقائيًا.'
                        : 'For an accurate quotation we need a few extra specs for the items below. Answer what you can — complete items were approved automatically.' }}
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($questionItems as $qIdx => $qItem)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $qItem['description'] }}</p>
                        @if(!empty($qItem['unit']))
                            <p class="mt-0.5 text-xs text-slate-400">{{ $isAr ? 'الوحدة' : 'Unit' }}: {{ $qItem['unit'] }}</p>
                        @endif
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600">
                        {{ $isAr ? 'مواصفات ناقصة' : 'Needs info' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach(($qItem['missing_specs'] ?? []) as $spec)
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">{{ $spec['question'] ?? ($spec['key'] ?? '') }}</label>
                            <input
                                type="text"
                                wire:model="questionItems.{{ $qIdx }}.answers.{{ $spec['key'] ?? '' }}"
                                placeholder="{{ !empty($spec['example']) ? (($isAr ? 'مثال: ' : 'e.g. ') . $spec['example']) : '' }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between gap-3">
        <button type="button" wire:click="skipSpecAnswers" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-500 shadow-sm hover:bg-slate-50 disabled:opacity-60">
            {{ $isAr ? 'تخطّي والتسعير بالتقدير' : 'Skip & price as estimate' }}
        </button>
        <button type="button" wire:click="submitSpecAnswers" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60">
            <svg wire:loading wire:target="submitSpecAnswers" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span wire:loading.remove wire:target="submitSpecAnswers">{{ $isAr ? 'متابعة التسعير' : 'Continue to pricing' }}</span>
            <span wire:loading wire:target="submitSpecAnswers">{{ $isAr ? 'جاري التسعير...' : 'Pricing...' }}</span>
        </button>
    </div>
</div>
