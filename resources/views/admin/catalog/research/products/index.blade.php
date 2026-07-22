@extends('layouts.admin-app')
@section('title', 'Product Catalog')
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Product Catalog</h1>
        @can('catalog.export')
        <a href="{{ route('admin.catalog.research.exports.products', request()->query()) }}"
           class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Export Excel</a>
        @endcan
    </div>

    {{-- Filters --}}
    <form method="GET" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input name="search" value="{{ request('search') }}" placeholder="SKU / name…" class="rounded-lg border-gray-300 text-sm" />
        <select name="manufacturer_id" class="rounded-lg border-gray-300 text-sm">
            <option value="">Manufacturer</option>
            @foreach($manufacturers as $m)<option value="{{ $m->id }}" @selected(request('manufacturer_id')==$m->id)>{{ $m->name }}</option>@endforeach
        </select>
        <select name="size_id" class="rounded-lg border-gray-300 text-sm">
            <option value="">Size</option>
            @foreach($sizes as $s)<option value="{{ $s->id }}" @selected(request('size_id')==$s->id)>{{ $s->display_value }}</option>@endforeach
        </select>
        <select name="connection_type_id" class="rounded-lg border-gray-300 text-sm">
            <option value="">Connection</option>
            @foreach($connections as $c)<option value="{{ $c->id }}" @selected(request('connection_type_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <select name="approval_id" class="rounded-lg border-gray-300 text-sm">
            <option value="">Approval</option>
            @foreach($approvals as $a)<option value="{{ $a->id }}" @selected(request('approval_id')==$a->id)>{{ $a->name }} {{ $a->approval_code }}</option>@endforeach
        </select>
        <select name="verification_level" class="rounded-lg border-gray-300 text-sm">
            <option value="">Verification</option>
            @foreach(\App\Enums\Catalog\Research\VerificationLevelEnum::cases() as $vl)
                <option value="{{ $vl->value }}" @selected(request('verification_level')===$vl->value)>{{ $vl->label() }}</option>
            @endforeach
        </select>
        <label class="col-span-2 flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="fire_protection" value="1" @checked(request('fire_protection')) class="rounded border-gray-300 text-emerald-600">
            Fire Protection suitable only
        </label>
        <button class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                <th class="px-4 py-3 text-start">Manufacturer</th>
                <th class="px-4 py-3 text-start">SKU</th>
                <th class="px-4 py-3 text-start">Series/Model</th>
                <th class="px-4 py-3 text-start">Size</th>
                <th class="px-4 py-3 text-start">Connection</th>
                <th class="px-4 py-3 text-start">Pressure</th>
                <th class="px-4 py-3 text-start">Verification</th>
                <th class="px-4 py-3 text-end"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($variants as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $v->manufacturer?->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $v->manufacturer_sku ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $v->model?->series?->series_name ?? $v->model?->model_number }}</td>
                        <td class="px-4 py-3">{{ $v->size?->display_value }}</td>
                        <td class="px-4 py-3">{{ $v->connectionType?->name }}</td>
                        <td class="px-4 py-3">{{ $v->pressureRating?->rating_name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $v->verification_status?->value === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $v->verification_level?->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.catalog.research.products.show', $v->uuid) }}" class="text-emerald-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No products match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $variants->links() }}
</div>
@endsection
