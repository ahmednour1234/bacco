@extends('layouts.admin-app')
@section('title', 'Suppliers')
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Suppliers</h1>
            <p class="text-sm text-gray-500">Who quotes a price — distinct from the manufacturer who makes the product.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.catalog.research.pricing.suppliers.sync') }}">
                @csrf
                <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    Sync from scraper
                </button>
            </form>
            <a href="{{ route('admin.catalog.research.pricing.index') }}" class="text-sm text-gray-500 hover:underline self-center">← Back</a>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Add supplier --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">Add a supplier</div>
        <form method="POST" action="{{ route('admin.catalog.research.pricing.suppliers.store') }}" class="grid gap-4 p-5 md:grid-cols-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Type</label>
                <select name="supplier_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    @foreach(['agent','distributor','retailer','manufacturer_direct','marketplace','unknown'] as $t)
                        <option value="{{ $t }}" @selected(old('supplier_type')===$t)>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Website</label>
                <input type="url" name="website" value="{{ old('website') }}" placeholder="https://…" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Country</label>
                <input type="text" name="country_code" value="{{ old('country_code', 'SA') }}" maxlength="8" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="md:col-span-3">
                <button class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save supplier</button>
            </div>
        </form>
    </div>

    {{-- List --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Supplier</th>
                    <th class="px-4 py-2 text-start">Host</th>
                    <th class="px-4 py-2 text-start">Type</th>
                    <th class="px-4 py-2 text-center">Prices</th>
                    <th class="px-4 py-2 text-start">Notes</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $s->name }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $s->normalized_name }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ ucfirst(str_replace('_',' ', $s->supplier_type)) }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $s->prices_count }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-500">
                            @if($s->notes && str_contains($s->notes, 'Merged'))
                                <span class="rounded bg-sky-100 px-1.5 py-0.5 font-semibold text-sky-700">merged</span>
                            @endif
                            {{ Str::limit($s->notes, 40) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        No suppliers yet — use “Sync from scraper” to import them.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection
