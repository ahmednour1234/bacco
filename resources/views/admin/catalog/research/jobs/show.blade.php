@extends('layouts.admin-app')
@section('title', 'Research Job')
@section('content')
<div class="p-6 space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">{{ $job->job_type->label() }}</h1>
        <a href="{{ route('admin.catalog.research.jobs.index') }}" class="text-sm text-gray-500 hover:underline">← Jobs</a>
    </div>

    @foreach(['success' => 'emerald', 'error' => 'red'] as $key => $tone)
        @if(session($key))<div class="rounded-lg bg-{{ $tone }}-50 border border-{{ $tone }}-200 text-{{ $tone }}-800 px-4 py-3 text-sm">{{ session($key) }}</div>@endif
    @endforeach

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Family</span><span>{{ $job->family?->name }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Provider</span><span>{{ $job->provider }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Status</span><span>{{ $job->status->label() }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Attempts</span><span>{{ $job->attempts }}/{{ $job->max_attempts }}</span></div>
        @if($job->error_message)<p class="text-xs text-red-600 pt-2">{{ $job->error_message }}</p>@endif
        @can('catalog.research.retry')
            @if($job->status === \App\Enums\Catalog\Research\ResearchJobStatusEnum::Failed)
                <form method="POST" action="{{ route('admin.catalog.research.jobs.retry', $job->uuid) }}" class="pt-2">@csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Retry Job</button>
                </form>
            @endif
        @endcan
    </div>

    @foreach($job->results as $result)
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="font-semibold text-gray-700">Result</span>
                <span class="{{ $result->validation_status === 'valid' ? 'text-emerald-600' : 'text-red-600' }}">{{ ucfirst($result->validation_status) }}</span>
            </div>
            <div class="grid grid-cols-4 gap-3 text-center text-xs">
                <div><div class="text-gray-400">Discovered</div><div class="text-lg font-bold">{{ $result->discovered_count }}</div></div>
                <div><div class="text-gray-400">Accepted</div><div class="text-lg font-bold text-emerald-600">{{ $result->accepted_count }}</div></div>
                <div><div class="text-gray-400">Rejected</div><div class="text-lg font-bold text-red-600">{{ $result->rejected_count }}</div></div>
                <div><div class="text-gray-400">Duplicate</div><div class="text-lg font-bold text-amber-600">{{ $result->duplicate_count }}</div></div>
            </div>
            @if($result->validation_errors)
                <details class="text-xs"><summary class="cursor-pointer text-red-600">Validation errors</summary>
                    <pre class="mt-2 overflow-x-auto rounded bg-gray-50 p-3">{{ json_encode($result->validation_errors, JSON_PRETTY_PRINT) }}</pre>
                </details>
            @endif
            <details class="text-xs"><summary class="cursor-pointer text-gray-500">Raw response</summary>
                <pre class="mt-2 overflow-x-auto rounded bg-gray-50 p-3 max-h-96">{{ \Illuminate\Support\Str::limit($result->raw_response, 4000) }}</pre>
            </details>
        </div>
    @endforeach
</div>
@endsection
