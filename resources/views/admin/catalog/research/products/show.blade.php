@extends('layouts.admin-app')
@section('title', 'Variant')
@section('content')
<div class="p-6 space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">{{ $variant->manufacturer?->name }} — {{ $variant->manufacturer_sku ?: $variant->variant_name }}</h1>
        <a href="{{ route('admin.catalog.research.products.index') }}" class="text-sm text-gray-500 hover:underline">← Catalog</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2 text-sm">
            <h2 class="font-semibold text-gray-700 mb-2">Specification</h2>
            <div class="flex justify-between"><span class="text-gray-500">Series / Model</span><span>{{ $variant->model?->series?->series_name ?? $variant->model?->model_number }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Size</span><span>{{ $variant->size?->display_value }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Connection</span><span>{{ $variant->connectionType?->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Standard</span><span>{{ $variant->connectionStandard?->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Pressure</span><span>{{ $variant->pressureRating?->rating_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Availability</span><span>{{ $variant->availability_status?->label() }}</span></div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-2 text-sm">
            <h2 class="font-semibold text-gray-700 mb-2">Verification</h2>
            <div class="flex justify-between"><span class="text-gray-500">Level</span><span>{{ $variant->verification_level?->label() }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Status</span><span>{{ $variant->verification_status?->label() }}</span></div>
            @if($variant->technical_notes)<p class="text-xs text-amber-600 pt-2">{{ $variant->technical_notes }}</p>@endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm">Approvals / Certifications</h2>
        <div class="flex flex-wrap gap-2">
            @forelse($variant->approvals as $a)
                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs text-emerald-700">
                    {{ $a->name }} {{ $a->approval_code }}@if($a->pivot->scope) · {{ $a->pivot->scope }}@endif
                </span>
            @empty<span class="text-sm text-gray-400">None.</span>@endforelse
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm">Source Evidence</h2>
        <ul class="space-y-2 text-sm">
            @forelse($variant->evidence as $e)
                <li class="flex items-center justify-between">
                    <span class="text-gray-600">{{ $e->field_name }}</span>
                    @if($e->source?->source_url)
                        <a href="{{ $e->source->source_url }}" target="_blank" rel="noopener" class="text-emerald-600 hover:underline truncate max-w-xs">{{ $e->source->domain }}</a>
                    @endif
                </li>
            @empty<li class="text-gray-400">No source evidence recorded.</li>@endforelse
        </ul>
    </div>
</div>
@endsection
