@extends('layouts.admin-app')

@section('title', 'تعديل الحساب – Qimta Admin')
@section('page-title', 'Admin Management')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.admins.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Admins</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">تعديل: {{ $admin->name }}</span>
@endsection

@section('content')
<div class="mx-auto max-w-xl">

    {{-- Card --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center gap-3 bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">تعديل عضو الفريق</p>
                <p class="text-xs text-emerald-100/70">{{ $admin->email }}</p>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}" class="px-6 py-6 space-y-4" dir="rtl">
            @csrf
            @method('PUT')

            {{-- Name + Phone --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">الاسم الكامل <span class="text-red-500">*</span></label>
                    <input name="name" type="text" value="{{ old('name', $admin->name) }}" placeholder="محمد أحمد"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">رقم الجوال</label>
                    <input name="phone" type="text" value="{{ old('phone', $admin->phone) }}" placeholder="+966 5x xxx xxxx"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">البريد الإلكتروني <span class="text-red-500">*</span></label>
                <input name="email" type="email" value="{{ old('email', $admin->email) }}" placeholder="name@qimta.com" dir="ltr"
                    class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                        {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Role + Password --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">الصلاحية <span class="text-red-500">*</span></label>
                    <select name="user_type"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('user_type') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                        <option value="employee" {{ old('user_type', $admin->user_type->value) === 'employee' ? 'selected' : '' }}>موظف</option>
                        <option value="admin"    {{ old('user_type', $admin->user_type->value) === 'admin'    ? 'selected' : '' }}>مشرف</option>
                    </select>
                    @error('user_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">
                        كلمة المرور <span class="font-normal text-slate-400">(اختياري)</span>
                    </label>
                    <input name="password" type="password" placeholder="اتركه فارغاً للإبقاء على الحالي" dir="ltr"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition
                            {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Role hint --}}
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs text-slate-500 leading-relaxed">
                <span class="font-semibold text-slate-700">مشرف</span> — صلاحيات كاملة تشمل إدارة الفريق. &nbsp;
                <span class="font-semibold text-slate-700">موظف</span> — جميع الميزات بدون إدارة المشرفين.
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-1">
                <a href="{{ route('admin.admins.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    رجوع
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
