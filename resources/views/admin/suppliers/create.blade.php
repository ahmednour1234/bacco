@extends('layouts.admin-app')

@section('title', 'Add Supplier – Qimta Admin')
@section('page-title', 'Add Supplier')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="text-xs font-medium text-slate-500 hover:text-slate-700 transition">Suppliers</a>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Add Supplier</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.suppliers.store') }}" class="space-y-6">
        @csrf

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Account Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3">Account Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>
        </div>

        {{-- Company Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3">Company Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Division / Role in Company</label>
                    <input type="text" name="division" value="{{ old('division') }}" placeholder="e.g. Sales Manager"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Address</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 resize-none">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Country</label>
                    <input type="text" name="country" value="{{ old('country') }}" placeholder="Saudi Arabia"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Supplier
            </button>
            <a href="{{ route('admin.suppliers.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
