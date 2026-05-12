@extends('layouts.admin-app')

@section('title', __('app.edit_member') . ' – Qimta Admin')
@section('page-title', __('app.admins_management'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.management_nav') }}</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.admins.index') }}" class="text-xs text-slate-400 hover:text-slate-600">{{ __('app.admins_nav') }}</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">{{ __('app.edit_account_breadcrumb', ['name' => $admin->name]) }}</span>
@endsection

@section('content')
<div class="mx-auto max-w-lg">

    {{-- Card --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 py-5" style="background: linear-gradient(135deg, #059669, #047857);">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(255,255,255,0.18);">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">{{ __('app.edit_member') }}</p>
                <p class="text-xs" style="color:rgba(209,250,229,0.8);">{{ $admin->email }}</p>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}" class="px-6 pt-6 pb-8 space-y-4" dir="rtl">
            @csrf
            @method('PUT')

            {{-- Name + Phone --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">{{ __('app.full_name') }} <span class="text-red-500">*</span></label>
                    <input name="name" type="text" value="{{ old('name', $admin->name) }}" placeholder="محمد أحمد"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">{{ __('app.phone') }}</label>
                    <input name="phone" type="text" value="{{ old('phone', $admin->phone) }}" placeholder="+966 5x xxx xxxx"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">{{ __('app.email_address') }} <span class="text-red-500">*</span></label>
                <input name="email" type="email" value="{{ old('email', $admin->email) }}" placeholder="name@qimta.com" dir="ltr"
                    class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                        {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Role + Password --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">{{ __('app.role') }} <span class="text-red-500">*</span></label>
                    <select name="user_type"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('user_type') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                        <option value="employee" {{ old('user_type', $admin->user_type->value) === 'employee' ? 'selected' : '' }}>{{ __('app.employee') }}</option>
                        <option value="admin"    {{ old('user_type', $admin->user_type->value) === 'admin'    ? 'selected' : '' }}>{{ __('app.admin') }}</option>
                    </select>
                    @error('user_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">
                        {{ __('app.password') }} <span class="font-normal text-slate-400">{{ __('app.password_optional_label') }}</span>
                    </label>
                    <input name="password" type="password" placeholder="{{ __('app.password_keep_blank') }}" dir="ltr"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Role hint --}}
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs text-slate-500 leading-relaxed">
                <span class="font-semibold text-slate-700">{{ __('app.admin') }}</span> — {{ __('app.role_admin_desc') }} &nbsp;
                <span class="font-semibold text-slate-700">{{ __('app.employee') }}</span> — {{ __('app.role_employee_desc') }}
            </div>

            {{-- Actions --}}
            <div class="flex flex-row-reverse items-center justify-between border-t border-slate-100 pt-4">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('app.save_changes') }}
                </button>
                <a href="{{ route('admin.admins.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ __('app.back') }}
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
