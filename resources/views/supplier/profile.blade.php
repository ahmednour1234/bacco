@extends('layouts.supplier-app')

@section('title', 'My Profile – Qimta Supplier')
@section('page-title', 'My Profile')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">My Profile</span>
@endsection

@section('content')

@php
    use App\Helpers\ImageHelper;

    $avatarUrl = ImageHelper::avatarUrl($user->avatar ?? null, $user->name);
    $initials  = strtoupper(
        substr($user->name ?? 'S', 0, 1) .
        (str_contains($user->name ?? '', ' ') ? substr(explode(' ', $user->name)[1], 0, 1) : '')
    );

    $completenessMap = [
        'name'         => [$user->name,                       15],
        'email'        => [$user->email,                      15],
        'phone'        => [$user->phone ?? null,              10],
        'avatar'       => [$user->avatar ?? null,             10],
        'company_name' => [$profile?->company_name ?? null,   15],
        'trade_name'   => [$profile?->trade_name ?? null,     10],
        'cr_number'    => [$profile?->cr_number ?? null,      10],
        'vat_number'   => [$profile?->vat_number ?? null,     10],
        'address'      => [$profile?->address ?? null,         5],
    ];
    $completeness = 0;
    foreach ($completenessMap as [$value, $weight]) {
        if (!empty($value)) $completeness += $weight;
    }

    $activeTab = 'profile';
    if ($errors->has('current_password') || $errors->has('new_password')) {
        $activeTab = 'security';
    }
@endphp

{{-- Flash message --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-cloak
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-6 flex items-center justify-between gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Validation errors --}}
@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
            <p class="text-sm font-semibold text-red-700">Please fix the following errors:</p>
        </div>
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
    <form method="POST" action="{{ route('supplier.profile.update') }}"
          enctype="multipart/form-data" id="avatar-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="name"  value="{{ $user->name }}">
        <input type="hidden" name="email" value="{{ $user->email }}">

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

            {{-- Avatar with upload overlay --}}
            <div class="relative shrink-0 group" x-data="{ preview: '{{ $avatarUrl }}' }">
                <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-white shadow-md bg-emerald-500
                            flex items-center justify-center text-white font-bold text-2xl">
                    <template x-if="preview">
                        <img :src="preview" alt="" class="w-full h-full object-cover" @@error="preview = ''">
                    </template>
                    <template x-if="!preview">
                        <span>{{ $initials }}</span>
                    </template>
                </div>
                <label for="avatar-input"
                       class="absolute inset-0 flex items-center justify-center
                              bg-black/50 rounded-full opacity-0 group-hover:opacity-100
                              transition-opacity cursor-pointer">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0"/>
                    </svg>
                </label>
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
                <span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full p-1.5 shadow-sm ring-2 ring-white">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
            </div>

            {{-- Name / email / badges / completeness --}}
            <div class="flex-1 text-center sm:text-left w-full min-w-0">
                <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">

                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Supplier
                    </span>

                    @if ($profile?->company_name)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-blue-50 text-blue-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $profile->company_name }}
                        </span>
                    @endif

                    @if ($profile?->division)
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-amber-50 text-amber-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            {{ $profile->division }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Since {{ $user->created_at->format('M Y') }}
                    </span>
                </div>

                {{-- Profile completeness --}}
                <div class="mt-4 max-w-xs sm:max-w-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-medium text-slate-500">Profile completeness</span>
                        <span class="text-xs font-bold
                            {{ $completeness >= 80 ? 'text-emerald-600' : ($completeness >= 50 ? 'text-amber-600' : 'text-red-500') }}">
                            {{ $completeness }}%
                        </span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                                    {{ $completeness >= 80 ? 'bg-emerald-500' : ($completeness >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                             style="width: {{ $completeness }}%"></div>
                    </div>
                    @if ($completeness < 100)
                        <p class="text-xs text-slate-400 mt-1">Fill in all fields to reach 100%</p>
                    @else
                        <p class="text-xs text-emerald-500 mt-1 font-medium">Your profile is complete!</p>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex flex-col items-end gap-1 shrink-0">
                <p class="text-xs text-slate-400">Click photo to change</p>
                <p class="text-xs text-slate-300">JPG, PNG, WebP · Max 2 MB</p>
            </div>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     TABS
══════════════════════════════════════════════════════════ --}}
<div x-data="{ tab: '{{ $activeTab }}' }">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-1.5 mb-6 flex gap-1 overflow-x-auto">
        <button @click="tab = 'profile'"
                :class="tab === 'profile' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 whitespace-nowrap">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Info
        </button>
        <button @click="tab = 'security'"
                :class="tab === 'security' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 whitespace-nowrap">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Security
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 1 — PROFILE INFO
    ════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'profile'" x-cloak>
        <form method="POST" action="{{ route('supplier.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

                {{-- ── Personal Information ─────────────────────────────── --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                        <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Personal Information</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Your name, email, and contact number</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-4">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Full Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   placeholder="Your full name"
                                   class="w-full px-4 py-2.5 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                          @error('name') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email Address <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                       placeholder="name@company.com"
                                       class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                              @error('email') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number</label>
                            <div class="relative">
                                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                                       placeholder="+966 5x xxx xxxx"
                                       class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                              @error('phone') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </span>
                            </div>
                            @error('phone')
                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ── Company Information ───────────────────────────────── --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                        <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Company Information</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Business registration and trade details</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-4">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Company Name</label>
                                <input type="text" name="company_name"
                                       value="{{ old('company_name', $profile?->company_name ?? '') }}"
                                       placeholder="e.g. Al-Nour Tech Co."
                                       class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Trade Name</label>
                                <input type="text" name="trade_name"
                                       value="{{ old('trade_name', $profile?->trade_name ?? '') }}"
                                       placeholder="Commercial / brand name"
                                       class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Division / Specialization</label>
                            <input type="text" name="division"
                                   value="{{ old('division', $profile?->division ?? '') }}"
                                   placeholder="e.g. HVAC, Electrical, ICT…"
                                   class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">CR Number</label>
                                <div class="relative">
                                    <input type="text" name="cr_number"
                                           value="{{ old('cr_number', $profile?->cr_number ?? '') }}"
                                           placeholder="Commercial registration"
                                           class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                                  @error('cr_number') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                    <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </span>
                                </div>
                                @error('cr_number')
                                    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">VAT Number</label>
                                <div class="relative">
                                    <input type="text" name="vat_number"
                                           value="{{ old('vat_number', $profile?->vat_number ?? '') }}"
                                           placeholder="Tax registration number"
                                           class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                                  focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                                  @error('vat_number') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                    <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Address Information (full-width) ──────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                    <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Address</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Physical or postal business address</p>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Street Address</label>
                            <textarea name="address" rows="2" placeholder="Building, street, district…"
                                      class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                             focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none">{{ old('address', $profile?->address ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">City</label>
                            <input type="text" name="city"
                                   value="{{ old('city', $profile?->city ?? '') }}"
                                   placeholder="e.g. Riyadh"
                                   class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Country</label>
                            <input type="text" name="country"
                                   value="{{ old('country', $profile?->country ?? 'Saudi Arabia') }}"
                                   placeholder="e.g. Saudi Arabia"
                                   class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    Fields marked <span class="text-red-400 font-semibold">*</span> are required.
                </p>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
                               text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 2 — SECURITY
    ════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'security'" x-cloak>
        <form method="POST" action="{{ route('supplier.profile.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="name"  value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                    <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Change Password</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Leave all fields blank to keep your current password</p>
                    </div>
                </div>
                <div class="px-6 py-5">

                    <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-6">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                        </svg>
                        <div class="text-xs text-blue-700">
                            <p class="font-semibold mb-1">Password requirements</p>
                            <ul class="list-disc list-inside space-y-0.5 text-blue-600">
                                <li>Minimum 8 characters</li>
                                <li>Mix of uppercase, lowercase, and numbers recommended</li>
                                <li>Enter your current password to confirm the change</li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                        {{-- Current Password --}}
                        <div x-data="{ show: false }">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Current Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="current_password"
                                       placeholder="••••••••"
                                       class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                              @error('current_password') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- New Password --}}
                        <div x-data="{ show: false }">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">New Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="new_password"
                                       placeholder="Min. 8 characters"
                                       class="w-full px-4 py-2.5 pr-10 text-sm bg-white border rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition
                                              @error('new_password') border-red-300 bg-red-50 @else border-slate-200 @enderror">
                                <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Confirm New Password --}}
                        <div x-data="{ show: false }">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="new_password_confirmation"
                                       placeholder="Repeat new password"
                                       class="w-full px-4 py-2.5 pr-10 text-sm bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
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
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 active:bg-slate-700
                               text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
