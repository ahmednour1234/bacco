@extends('layouts.admin-app')
@section('title', 'Research Jobs')
@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Research Jobs</h1>

    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status" class="rounded-lg border-gray-300 text-sm">
            <option value="">All statuses</option>
            @foreach(\App\Enums\Catalog\Research\ResearchJobStatusEnum::cases() as $st)
                <option value="{{ $st->value }}" @selected(request('status')===$st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                <th class="px-4 py-3 text-start">Type</th>
                <th class="px-4 py-3 text-start">Family</th>
                <th class="px-4 py-3 text-start">Status</th>
                <th class="px-4 py-3 text-start">Attempts</th>
                <th class="px-4 py-3 text-end"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $job)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $job->job_type->label() }}</td>
                        <td class="px-4 py-3">{{ $job->family?->name }}</td>
                        <td class="px-4 py-3">{{ $job->status->label() }}</td>
                        <td class="px-4 py-3">{{ $job->attempts }}/{{ $job->max_attempts }}</td>
                        <td class="px-4 py-3 text-end"><a href="{{ route('admin.catalog.research.jobs.show', $job->uuid) }}" class="text-emerald-600 hover:underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No research jobs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
