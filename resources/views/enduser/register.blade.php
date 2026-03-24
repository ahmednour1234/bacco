@extends('layouts.enduser-auth')

@section('title', 'Create Account – Qimta')

@section('left-heading', 'Building the future of construction management.')
@section('left-subtext', 'Streamline your projects, manage teams, and track every detail in one powerful platform designed for the modern builder.')

@section('left-extra')
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

@section('form')
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Create your account</h2>
        <p class="text-slate-500 text-sm mt-1.5">
            Join the Qimta network to manage your projects efficiently.
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

    <form method="POST" action="{{ route('enduser.register.store') }}" class="space-y-3.5">
        @csrf

        {{-- Full Name + Role --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                <div class="relative">
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="John Doe"
                        class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                               rounded-xl text-slate-900 placeholder-slate-400
                               focus:outline-none focus:ring-2 focus:ring-emerald-500
                               focus:border-transparent transition">
                    <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role in Company</label>
                <div class="relative">
                    <select name="role"
                        class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                               rounded-xl text-slate-700 focus:outline-none focus:ring-2
                               focus:ring-emerald-500 focus:border-transparent transition appearance-none">
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                        <option value="project_manager" {{ old('role') == 'project_manager' ? 'selected' : '' }}>Project Manager</option>
                        <option value="site_engineer"   {{ old('role') == 'site_engineer'   ? 'selected' : '' }}>Site Engineer</option>
                        <option value="contractor"      {{ old('role') == 'contractor'      ? 'selected' : '' }}>Contractor</option>
                        <option value="architect"       {{ old('role') == 'architect'       ? 'selected' : '' }}>Architect</option>
                        <option value="owner"           {{ old('role') == 'owner'           ? 'selected' : '' }}>Owner / Developer</option>
                        <option value="other"           {{ old('role') == 'other'           ? 'selected' : '' }}>Other</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Company Name --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Company Name</label>
            <div class="relative">
                <input type="text" name="company" value="{{ old('company') }}"
                    placeholder="Acme Construction Ltd."
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9
                                 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1
                                 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
            <div class="relative">
                <input type="email" name="email" value="{{ old('email') }}"
                    placeholder="name@company.com"
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

        {{-- Phone --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number</label>
            <div class="relative">
                <input type="tel" name="phone" value="{{ old('phone') }}"
                    placeholder="+1 (555) 000-0000"
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502
                                 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0
                                 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1
                                 C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </span>
            </div>
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" name="password"
                    placeholder="Create a password"
                    class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200
                           rounded-xl text-slate-900 placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-emerald-500
                           focus:border-transparent transition">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12
                                 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                                 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97
                                 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
                                 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0
                                 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025
                                 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Terms --}}
        <div class="flex items-start gap-3 pt-0.5">
            <input type="checkbox" name="terms" id="terms"
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300
                       text-emerald-600 focus:ring-emerald-500 cursor-pointer">
            <label for="terms" class="text-xs text-slate-500 leading-relaxed cursor-pointer select-none">
                I agree to the
                <a href="#" class="text-emerald-600 font-semibold hover:underline">Terms of Service</a>
                and
                <a href="#" class="text-emerald-600 font-semibold hover:underline">Privacy Policy</a>
            </label>
        </div>

        {{-- Sign Up --}}
        <button type="submit"
            class="w-full flex items-center justify-center gap-2
                   bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900
                   text-white font-semibold py-3 rounded-xl text-sm
                   transition-colors duration-200 shadow-sm">
            Sign Up
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-xs text-slate-500 pt-0.5">
            Already have an account?
            <a href="{{ route('enduser.login') }}" class="text-emerald-600 font-semibold hover:underline">
                Sign in
            </a>
        </p>

    </form>
@endsection
