@extends('layouts.enduser-app')

@section('title', __('app.title_my_profile'))
@section('page-title', __('app.my_profile'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">{{ __('app.my_profile') }}</span>
@endsection

@section('content')

@php
    use App\Helpers\ImageHelper;
    $avatarUrl = ImageHelper::avatarUrl($user->avatar ?? null, $user->name);
    $initials  = strtoupper(substr($user->name ?? 'U', 0, 1) . (str_contains($user->name ?? '', ' ') ? substr(explode(' ', $user->name)[1], 0, 1) : ''));
@endphp

{{-- Flash message --}}
@if (session('success'))
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
    </div>
@endif

{{-- Validation errors --}}
@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
        <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     PROFILE HEADER CARD
══════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
    <form method="POST" action="{{ route('enduser.profile.update') }}"
          enctype="multipart/form-data" id="avatar-form">
        @csrf
        @method('PUT')

        {{-- Hidden fields so partial submit of avatar still carries full profile --}}
        <input type="hidden" name="name"  value="{{ $user->name }}">
        <input type="hidden" name="email" value="{{ $user->email }}">

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">

            {{-- Avatar with upload overlay --}}
            <div class="relative shrink-0 group" x-data="{ preview: '{{ $avatarUrl }}' }">

                {{-- Avatar circle --}}
                <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-slate-100 bg-emerald-500
                            flex items-center justify-center shrink-0 text-white font-bold text-2xl">
                    <template x-if="preview">
                        <img :src="preview" alt="" class="w-full h-full object-cover" @@error="preview = ''">
                    </template>
                    <template x-if="!preview">
                        <span>{{ $initials }}</span>
                    </template>
                </div>

                {{-- Upload overlay --}}
                <label for="avatar-input"
                       class="absolute inset-0 flex items-center justify-center
                              bg-black/40 rounded-full opacity-0 group-hover:opacity-100
                              transition-opacity cursor-pointer">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0"/>
                    </svg>
                </label>

                {{-- Hidden file input --}}
                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden"
                       @change="
                           const file = $event.target.files[0];
                           if (file) {
                               const reader = new FileReader();
                               reader.onload = e => preview = e.target.result;
                               reader.readAsDataURL(file);
                               $nextTick(() => $el.closest('form').submit());
                           }
                       ">
            </div>

            {{-- Name / email / join date --}}
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-xl font-bold text-slateald-900">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-3">
                    <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-50 text-emerald-700 font-medium px-3 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('app.client') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs bg-slate-100 text-slate-600 font-medium px-3 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ __('app.member_since') }} {{ $user->created_at->format('M Y') }}
                    </span>
                    @if ($profile && $profile->company_name)
                        <span class="inline-flex items-center gap-1.5 text-xs bg-blue-50 text-blue-700 font-medium px-3 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $profile->company_name }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Upload hint --}}
            <div class="hidden sm:flex flex-col items-end gap-1 shrink-0">
                <p class="text-xs text-slate-400">{{ __('app.click_photo_change') }}</p>
                <p class="text-xs text-slate-300">{{ __('app.photo_formats') }}</p>
            </div>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     MAIN PROFILE FORM
══════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('enduser.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

        {{-- ─────────────────────────────────────────────────────
             PERSONAL INFORMATION
        ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.personal_information') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.update_name_email_phone') }}</p>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                {{-- Full Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.full_name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           placeholder="Your full name"
                           class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                  text-slate-900 placeholder-slate-400
                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                  transition @error('name') border-red-300 bg-red-50 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.email_address') }}</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="name@company.com"
                               class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                      transition @error('email') border-red-300 bg-red-50 @enderror">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.phone_number') }}</label>
                    <div class="relative">
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               placeholder="+966 5x xxx xxxx"
                               class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                      transition @error('phone') border-red-300 bg-red-50 @enderror">
                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                    </div>
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             BUSINESS INFORMATION
        ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('app.business_information') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('app.company_registration_details') }}</p>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                {{-- Company Name + Trade Name --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.company_name') }}</label>
                        <input type="text" name="company_name"
                               value="{{ old('company_name', $profile->company_name ?? '') }}"
                               placeholder="Acme Construction Co."
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.trade_name') }}</label>
                        <input type="text" name="trade_name"
                               value="{{ old('trade_name', $profile->trade_name ?? '') }}"
                               placeholder="Acme"
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                </div>

                {{-- CR Number + VAT Number --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.cr_number') }}</label>
                        <input type="text" name="cr_number"
                               value="{{ old('cr_number', $profile->cr_number ?? '') }}"
                               placeholder="1010xxxxxx"
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.vat_number') }}</label>
                        <input type="text" name="vat_number"
                               value="{{ old('vat_number', $profile->vat_number ?? '') }}"
                               placeholder="300xxxxxxxxx"
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.address') }}</label>
                    <input type="text" name="address"
                           value="{{ old('address', $profile->address ?? '') }}"
                           placeholder="Street address"
                           class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                  text-slate-900 placeholder-slate-400
                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                {{-- City + Country --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.city') }}</label>
                        <input type="text" name="city"
                               value="{{ old('city', $profile->city ?? '') }}"
                               placeholder="Riyadh"
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.country') }}</label>
                        <input type="text" name="country"
                               value="{{ old('country', $profile->country ?? '') }}"
                               placeholder="Saudi Arabia"
                               class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Save button for personal + business info --}}
    <div class="flex justify-end mb-6">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900
                       text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ __('app.save_changes') }}
        </button>
    </div>
</form>

{{-- ══════════════════════════════════════════════════════════
     CHANGE PASSWORD
══════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('enduser.profile.update') }}">
    @csrf
    @method('PUT')

    {{-- Carry name+email so validation passes --}}
    <input type="hidden" name="name"  value="{{ $user->name }}">
    <input type="hidden" name="email" value="{{ $user->email }}">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
            <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ __('app.change_password') }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('app.leave_blank_password') }}</p>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                {{-- Current Password --}}
                <div x-data="{ show: false }">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.current_password') }}</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password"
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                      transition @error('current_password') border-red-300 bg-red-50 @enderror">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div x-data="{ show: false }">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.new_password') }}</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="new_password"
                               placeholder="Min. 8 characters"
                               class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                      transition @error('new_password') border-red-300 bg-red-50 @enderror">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div x-data="{ show: false }">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('app.confirm_new_password') }}</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="new_password_confirmation"
                               placeholder="Repeat new password"
                               class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl
                                      text-slate-900 placeholder-slate-400
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-5">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 active:bg-black
                               text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ __('app.update_password') }}
                </button>
            </div>
        </div>
    </div>

</form>

@endsection
