@extends('layouts.admin-app')
@section('title', 'BOQ Matching')
@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">BOQ Matching</h1>
            <p class="text-sm text-gray-500">Tie BOQ lines to real catalog products, then price them.</p>
        </div>
        <a href="{{ route('admin.catalog.research.pricing.index') }}" class="text-sm text-gray-500 hover:underline">← Pricing</a>
    </div>

    @if(session('success'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">BOQ</th>
                    <th class="px-4 py-2 text-center">Matched</th>
                    <th class="px-4 py-2 text-center">Priced</th>
                    <th class="px-4 py-2 text-center">Selected</th>
                    <th class="px-4 py-2 text-end">Action</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($boqs as $boq)
                    @php $s = $stats[$boq->id] ?? null; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <div class="font-medium text-gray-800">#{{ $boq->id }} {{ Str::limit($boq->name ?? $boq->title ?? '', 50) }}</div>
                            <div class="text-xs text-gray-400">{{ $boq->created_at?->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">{{ $s->matched ?? 0 }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ ($s->priced ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $s->priced ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $s->selected ?? 0 }}</span>
                        </td>
                        <td class="px-4 py-2 text-end">
                            <a href="{{ route('admin.catalog.research.pricing.boq.show', $boq->id) }}"
                               class="text-emerald-600 hover:underline">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No BOQs yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $boqs->links() }}</div>
    </div>
</div>
@endsection
