@extends('layouts.enduser-auth')

@section('title', 'Reset Password – Supplier – Qimta')

@section('left-heading', 'Set your new password.')
@section('left-subtext', 'Choose a strong password to protect your supplier account.')

@section('form')
    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-5">
        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0
                     01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>

    <div class="mb-7">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Reset Password</h2>
        <p class="text-slate-500 text-sm mt-1.5">
            Enter your new password below.
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

    <form method="POST" action="{{ route('supplier.reset-password.update') }}" class="space-y-4" x-data="{}">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">New Password</label>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" name="password"
                    autofocus placeholder="At least 8 characters"
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478
                                 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97
                                 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
                                 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0
                                 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0
                                 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Confirm Password</label>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" name="password_confirmation"
                    placeholder="Repeat your password"
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478
                                 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97
                                 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
                                 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0
                                 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0
                                 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit"
            class="w-full flex items-center justify-center gap-2
                   bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
                   text-white font-semibold py-3 rounded-xl text-sm
                   transition-colors duration-200 mt-2 shadow-sm">
            Set New Password
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>
@endsection
