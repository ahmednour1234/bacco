@props(['compact' => false, 'showHeader' => true])

@php
    $isAr = app()->getLocale() === 'ar';
    $sabAccounts = [
        [
            'currency' => 'USD',
            'currencyLabel' => $isAr ? 'حساب الدولار' : 'US Dollar Account',
            'accountNumber' => '611249855080',
            'iban' => 'SA53450000000611249855080',
            'tone' => 'border-blue-100 bg-blue-50 text-blue-700',
        ],
        [
            'currency' => 'EUR',
            'currencyLabel' => $isAr ? 'حساب اليورو' : 'Euro Account',
            'accountNumber' => '611249855081',
            'iban' => 'SA26450000000611249855081',
            'tone' => 'border-violet-100 bg-violet-50 text-violet-700',
        ],
        [
            'currency' => 'SAR',
            'currencyLabel' => $isAr ? 'حساب الريال السعودي' : 'Saudi Riyal Account',
            'accountNumber' => '611249855001',
            'iban' => 'SA52450000000611249855001',
            'tone' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        ],
    ];

    $rajhiAccount = [
        'currency' => 'SAR',
        'currencyLabel' => $isAr ? 'حساب الراجحي' : 'Al Rajhi Account',
        'accountNumber' => '44600001006080444992',
        'iban' => 'SA0680000446608010444992',
        'tone' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
    ];
@endphp

<div
    x-data="{ open: true, activeTab: 'sab' }"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden']) }}
>
    @if($showHeader)
        <button
            type="button"
            class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 text-start transition hover:bg-slate-50"
            style="background:#fafbfc;"
            @click="open = !open"
            :aria-expanded="open.toString()"
        >
            <span class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </span>
                <span>
                    <span class="block text-sm font-bold text-slate-900">
                        {{ $isAr ? 'الدفع بالتحويل البنكي' : 'Bank Transfer Payment' }}
                    </span>
                    <span class="mt-1 block text-xs text-slate-500">
                        {{ $isAr ? 'حوّل المبلغ وارفع الإيصال لتأكيد طلبك' : 'Transfer the amount and upload the receipt to confirm your order' }}
                    </span>
                </span>
            </span>

            <span class="flex items-center gap-3">
                <span class="hidden rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 sm:inline-flex">
                    SABBSARI
                </span>
                <svg class="h-5 w-5 text-slate-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </span>
        </button>
    @endif

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
    >
        <div class="border-b border-slate-100 px-6 pt-4">
            <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-bold transition"
                    :class="activeTab === 'sab' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    @click="activeTab = 'sab'"
                >
                    SAB
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-bold transition"
                    :class="activeTab === 'rajhi' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    @click="activeTab = 'rajhi'"
                >
                    Al Rajhi
                </button>
            </div>
            <p class="my-3 text-xs text-slate-500">
                {{ $isAr ? 'اختر الحساب المناسب حسب البنك والعملة، ثم ارفع إيصال الدفع.' : 'Choose the correct bank and currency account, then upload the payment receipt.' }}
            </p>
        </div>

        <div x-show="activeTab === 'sab'" x-cloak class="grid gap-4 p-6 {{ $compact ? 'lg:grid-cols-1' : 'lg:grid-cols-3' }}">
            @foreach($sabAccounts as $account)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-900">{{ $account['currencyLabel'] }}</p>
                            <p class="mt-0.5 text-xs font-semibold text-slate-400">QIMTA COMPANY TECHNICAL</p>
                        </div>
                        <span class="rounded-lg border px-2.5 py-1 text-xs font-extrabold {{ $account['tone'] }}">
                            {{ $account['currency'] }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'البنك' : 'Bank' }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-800">{{ $isAr ? 'البنك السعودي الأول SAB' : 'Saudi Awwal Bank SAB' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'رقم الحساب' : 'Account Number' }}</p>
                            <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $account['accountNumber'] }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'رقم الآيبان' : 'IBAN' }}</p>
                            <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $account['iban'] }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'سويفت' : 'SWIFT' }}</p>
                                <p class="mt-1 font-mono text-xs font-bold text-slate-700">SABBSARI</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'الرقم الموحد' : 'Unified No.' }}</p>
                                <p class="mt-1 font-mono text-xs font-bold text-slate-700">7051075815</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div x-show="activeTab === 'rajhi'" x-cloak class="p-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">{{ $rajhiAccount['currencyLabel'] }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-slate-400">QIMTA COMPANY TECHNICAL</p>
                    </div>
                    <span class="rounded-lg border px-2.5 py-1 text-xs font-extrabold {{ $rajhiAccount['tone'] }}">
                        {{ $rajhiAccount['currency'] }}
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'البنك' : 'Bank' }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ $isAr ? 'مصرف الراجحي' : 'Al Rajhi Bank' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'اسم المستفيد' : 'Beneficiary' }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ $isAr ? 'شركة كيمتا التقنية' : 'Qimta Technology Company' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'رقم الحساب' : 'Account Number' }}</p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $rajhiAccount['accountNumber'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'رقم الآيبان' : 'IBAN' }}</p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $rajhiAccount['iban'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'العملة' : 'Currency' }}</p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900">{{ $rajhiAccount['currency'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $isAr ? 'الرقم الموحد' : 'Unified No.' }}</p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900">7051075815</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
