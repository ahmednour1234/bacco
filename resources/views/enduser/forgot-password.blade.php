@extends('layouts.enduser-auth')

@section('title', 'Forgot Password – Qimta')

@section('left-heading', 'Reset your password.')
@section('left-subtext', 'Enter your registered email and we\'ll send you a 4-digit code to securely reset your password.')


@section('form')

    {{-- Back link --}}
    <a href="{{ route('enduser.login') }}"
       class="inline-flex items-center gap-1.5 text-xs text-slate-500
              hover:text-emerald-600 font-medium mb-6 transition-colors">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Sign In
    </a>

    {{-- Icon badge --}}
    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-5">
        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2
                     2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>

    <div class="mb-7">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Forgot your password?</h2>
        <p class="text-slate-500 text-sm mt-1.5">
            We'll send a 4-digit reset code to your email address.
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

    <form method="POST" action="{{ route('enduser.forgot-password.send') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
            <div class="relative">
                <input type="email" name="email" value="{{ old('email') }}"
                    autofocus placeholder="name@company.com"
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2
                                 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
        </div>

        <button type="submit"
            class="w-full flex items-center justify-center gap-2
                   bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900
                   text-white font-semibold py-3 rounded-xl text-sm
                   transition-colors duration-200 shadow-sm">
            Send Reset Code
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-xs text-slate-500 pt-1">
            Remember your password?
            <a href="{{ route('enduser.login') }}" class="text-emerald-600 font-semibold hover:underline">
                Sign in
            </a>
        </p>

    </form>
@endsection
