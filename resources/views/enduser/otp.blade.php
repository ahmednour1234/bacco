@extends('layouts.enduser-auth')

@section('title', 'Verify Code – Qimta')

@section('left-heading', 'Check your inbox.')
@section('left-subtext', 'We\'ve sent a 4-digit verification code to your email. The code expires in 10 minutes.')


@section('form')

    {{-- Back --}}
    <a href="{{ route('enduser.forgot-password') }}"
       class="inline-flex items-center gap-1.5 text-xs text-slate-500
              hover:text-emerald-600 font-medium mb-6 transition-colors">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>

    {{-- Icon badge --}}
    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-5">
        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0
                     01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622
                     5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Enter verification code</h2>
        <p class="text-slate-500 text-sm mt-1.5">
            Code sent to
            <span class="font-semibold text-slate-700">{{ session('otp_email') ?? 'your email' }}</span>
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('enduser.otp.verify') }}"
          id="otpForm" class="space-y-6">
        @csrf
        <input type="hidden" name="otp" id="otpHidden">

        {{-- 4 digit boxes --}}
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-4 text-center">
                Enter the 4-digit code
            </label>
            <div class="flex items-center justify-center gap-3 sm:gap-4">
                @for ($i = 1; $i <= 4; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                        data-otp="{{ $i }}"
                        class="otp-input w-14 h-14 sm:w-16 sm:h-16 text-center text-2xl font-bold
                               bg-white border-2 border-slate-200 rounded-2xl text-slate-900
                               focus:outline-none focus:border-emerald-500 focus:ring-2
                               focus:ring-emerald-100 transition caret-transparent">
                @endfor
            </div>
        </div>

        <button type="submit"
            class="w-full flex items-center justify-center gap-2
                   bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900
                   text-white font-semibold py-3 rounded-xl text-sm
                   transition-colors duration-200 shadow-sm">
            Verify Code
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-xs text-slate-500 pb-2 lg:pb-0">
            Didn't receive a code?
            <a href="#" onclick="event.preventDefault(); document.getElementById('resendForm').submit();"
               class="text-emerald-600 font-semibold hover:underline">Resend</a>
        </p>

    </form>

    <form id="resendForm" method="POST"
          action="{{ route('enduser.forgot-password.send') }}" class="hidden">
        @csrf
        <input type="hidden" name="email" value="{{ session('otp_email') }}">
        <input type="hidden" name="resend" value="1">
    </form>

@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.otp-input');
    const hidden = document.getElementById('otpHidden');

    inputs.forEach((input, idx) => {
        input.addEventListener('keydown', (e) => {
            if (!/^\d$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Tab') {
                e.preventDefault();
            }
        });

        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            if (input.value && idx < inputs.length - 1) inputs[idx + 1].focus();
            syncHidden();
        });

        input.addEventListener('keyup', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) inputs[idx - 1].focus();
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 4);
            [...pasted].forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
            inputs[Math.min(pasted.length, 3)]?.focus();
            syncHidden();
        });
    });

    function syncHidden() {
        hidden.value = [...inputs].map(i => i.value).join('');
    }

    document.getElementById('otpForm').addEventListener('submit', (e) => {
        syncHidden();
        if (hidden.value.length < 4) { e.preventDefault(); inputs[0].focus(); }
    });

    inputs[0]?.focus();
</script>
@endpush
