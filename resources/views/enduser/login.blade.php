@extends('layouts.enduser-auth')

@section('title', 'Sign In – Qimta')

{{-- ── Left panel ──────────────────────────────────────────── --}}
@section('left-heading', 'Building the future of construction management.')
@section('left-subtext', 'Streamline your projects, manage teams, and track every detail in one powerful platform designed for the modern builder.')

@section('left-extra')
    {{-- Avatar group + social proof --}}
    <div class="flex items-center gap-3">
        <div class="flex -space-x-2">
            <div class="w-8 h-8 rounded-full bg-emerald-600 border-2 border-white flex items-center justify-center text-white text-xs font-bold">A</div>
            <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-white flex items-center justify-center text-white text-xs font-bold">M</div>
            <div class="w-8 h-8 rounded-full bg-amber-500 border-2 border-white flex items-center justify-center text-white text-xs font-bold">S</div>
        </div>
        <p class="text-slate-500 text-xs leading-snug">
            Join <span class="font-semibold text-slate-700">2,000+</span> construction companies worldwide
        </p>
    </div>
@endsection

{{-- ── Portal button (desktop top-right) ─────────────────── --}}
@section('desktop-header-action')
    <a href="{{ route('supplier.login') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-700
              border border-emerald-600 rounded-xl px-4 py-2
              hover:bg-emerald-50 transition">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0
                     00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2
                     2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Go to Supplier Portal
    </a>
@endsection

@section('mobile-header-action')
    <a href="{{ route('supplier.login') }}"
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700
              border border-emerald-600 rounded-xl px-3 py-1.5 hover:bg-emerald-50 transition">
        Supplier Portal
    </a>
@endsection

{{-- ── Form ──────────────────────────────────────────────────── --}}
@section('form')
    <div class="mb-7">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Sign In</h2>
        <p class="text-slate-500 text-sm mt-1.5">
            Enter your details to access your construction projects.
        </p>
    </div>

    {{-- Success flash --}}
    @if (session('success'))
        <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <p class="text-sm text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('enduser.login.store') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
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

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-slate-700">Password</label>
                <a href="{{ route('enduser.forgot-password') }}"
                   class="text-xs text-emerald-600 font-semibold hover:underline">
                    Forgot password?
                </a>
            </div>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" name="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
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

        {{-- Remember me --}}
        <div class="flex items-center gap-3 pt-0.5">
            <input type="checkbox" name="remember" id="remember"
                class="h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-600
                       focus:ring-emerald-500 cursor-pointer">
            <label for="remember" class="text-xs text-slate-500 cursor-pointer select-none">
                Remember me on this device
            </label>
        </div>

        {{-- Sign In button --}}
        <button type="submit"
            class="w-full flex items-center justify-center gap-2
                   bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900
                   text-white font-semibold py-3 rounded-xl text-sm
                   transition-colors duration-200 mt-2 shadow-sm">
            Sign In
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-xs text-slate-500 pt-1">
            Don't have an account?
            <a href="{{ route('enduser.register') }}"
               class="text-emerald-600 font-semibold hover:underline">
                Create a new account
            </a>
        </p>

    </form>
@endsection
