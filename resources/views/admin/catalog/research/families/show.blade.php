@extends('layouts.admin-app')
@section('title', $family->name)
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $family->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Status: <b>{{ $family->research_status->label() }}</b> · Progress: {{ $progress }}%
            </p>
        </div>
        <a href="{{ route('admin.catalog.research.families.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
    </div>

    @foreach(['success' => 'emerald', 'error' => 'red'] as $key => $tone)
        @if(session($key))
            <div class="rounded-lg bg-{{ $tone }}-50 border border-{{ $tone }}-200 text-{{ $tone }}-800 px-4 py-3 text-sm">{{ session($key) }}</div>
        @endif
    @endforeach

    {{-- Research controls --}}
    <div class="flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        @can('catalog.research.start')
            @if($family->research_status->canStart() && $family->research_status !== \App\Enums\Catalog\Research\ResearchStatusEnum::Paused)
                <form method="POST" action="{{ route('admin.catalog.research.families.research', $family->uuid) }}">@csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Start Research</button>
                </form>
            @endif
            @if($family->research_status === \App\Enums\Catalog\Research\ResearchStatusEnum::Paused)
                <form method="POST" action="{{ route('admin.catalog.research.families.resume', $family->uuid) }}">@csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Resume</button>
                </form>
            @endif
        @endcan
        @can('catalog.research.pause')
            <form method="POST" action="{{ route('admin.catalog.research.families.pause', $family->uuid) }}">@csrf
                <button class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Pause</button>
            </form>
        @endcan
        @can('catalog.research.cancel')
            <form method="POST" action="{{ route('admin.catalog.research.families.cancel', $family->uuid) }}">@csrf
                <button class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Cancel</button>
            </form>
        @endcan
    </div>

    {{-- Manufacturers --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Manufacturers</h2>
        <div class="flex flex-wrap gap-2">
            @forelse($family->manufacturers as $m)
                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $m->name }}</span>
            @empty
                <span class="text-sm text-gray-400">None linked.</span>
            @endforelse
        </div>
    </div>

    {{-- Discovered variants --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Discovered Variants ({{ $family->variants->count() }})</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Manufacturer</th>
                    <th class="px-4 py-2 text-start">SKU</th>
                    <th class="px-4 py-2 text-start">Verification</th>
                    <th class="px-4 py-2 text-start">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($family->variants as $v)
                        <tr>
                            <td class="px-4 py-2">{{ $v->manufacturer?->name }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $v->manufacturer_sku ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $v->verification_level?->label() }}</td>
                            <td class="px-4 py-2">{{ $v->verification_status?->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No variants discovered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Research jobs --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Research Jobs</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                    <th class="px-4 py-2 text-start">Type</th>
                    <th class="px-4 py-2 text-start">Status</th>
                    <th class="px-4 py-2 text-start">Attempts</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jobs as $job)
                        <tr>
                            <td class="px-4 py-2">{{ $job->job_type->label() }}</td>
                            <td class="px-4 py-2">{{ $job->status->label() }}</td>
                            <td class="px-4 py-2">{{ $job->attempts }}/{{ $job->max_attempts }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">No jobs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $jobs->links() }}</div>
    </div>
</div>
@endsection
