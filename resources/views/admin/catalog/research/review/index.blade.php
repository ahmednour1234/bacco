@extends('layouts.admin-app')
@section('title', 'Review Queue')
@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Review Queue</h1>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif

    {{-- Review items --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Items needing review</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Reason</th>
                    <th class="px-4 py-2 text-start">Severity</th>
                    <th class="px-4 py-2 text-start">Subject</th>
                    <th class="px-4 py-2 text-end">Resolve</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-4 py-2">{{ $item->reason }}</td>
                            <td class="px-4 py-2">{{ $item->severity?->label() }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ class_basename($item->reviewable_type) }} #{{ $item->reviewable_id }}</td>
                            <td class="px-4 py-2 text-end">
                                @can('catalog.review.resolve')
                                <form method="POST" action="{{ route('admin.catalog.research.review.resolve', $item->id) }}" class="inline-flex gap-1">@csrf
                                    <button name="status" value="resolved" class="rounded bg-emerald-600 px-2 py-1 text-xs text-white">Resolve</button>
                                    <button name="status" value="dismissed" class="rounded bg-gray-400 px-2 py-1 text-xs text-white">Dismiss</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nothing to review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $items->links() }}</div>
    </div>

    {{-- Duplicate candidates --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Duplicate Candidates</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Variant A</th>
                    <th class="px-4 py-2 text-start">Variant B</th>
                    <th class="px-4 py-2 text-start">Score</th>
                    <th class="px-4 py-2 text-start">Reasons</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($duplicates as $dup)
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs">{{ $dup->first?->manufacturer_sku ?? $dup->first_product_variant_id }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $dup->second?->manufacturer_sku ?? $dup->second_product_variant_id }}</td>
                            <td class="px-4 py-2">{{ number_format($dup->similarity_score * 100) }}%</td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ implode(', ', (array) $dup->match_reasons) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No duplicate candidates.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $duplicates->links() }}</div>
    </div>
</div>
@endsection
