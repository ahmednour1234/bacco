@props(['compact' => false])

@php
    $isAr = app()->getLocale() === 'ar';
    $accounts = [
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
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden']) }}>
    <div class="border-b border-slate-100 px-6 py-4" style="background:#fafbfc;">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">
                    {{ $isAr ? 'حسابات التحويل البنكي' : 'Bank Transfer Accounts' }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $isAr ? 'اختر الحساب المناسب حسب عملة التحويل، ثم ارفع إيصال الدفع.' : 'Choose the account that matches your transfer currency, then upload the payment receipt.' }}
                </p>
            </div>
            <div class="rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-700">
                SABBSARI
            </div>
        </div>
    </div>

    <div class="grid gap-4 p-6 {{ $compact ? 'lg:grid-cols-1' : 'lg:grid-cols-3' }}">
        @foreach($accounts as $account)
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
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">
                            {{ $isAr ? 'البنك' : 'Bank' }}
                        </p>
                        <p class="mt-1 text-sm font-bold text-slate-800">
                            {{ $isAr ? 'البنك السعودي الأول SAB' : 'Saudi Awwal Bank SAB' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">
                            {{ $isAr ? 'رقم الحساب' : 'Account Number' }}
                        </p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $account['accountNumber'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">
                            {{ $isAr ? 'رقم الآيبان' : 'IBAN' }}
                        </p>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-900 break-all">{{ $account['iban'] }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                {{ $isAr ? 'سويفت' : 'SWIFT' }}
                            </p>
                            <p class="mt-1 font-mono text-xs font-bold text-slate-700">SABBSARI</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                {{ $isAr ? 'الرقم الموحد' : 'Unified No.' }}
                            </p>
                            <p class="mt-1 font-mono text-xs font-bold text-slate-700">7051075815</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
