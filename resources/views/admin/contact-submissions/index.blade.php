@extends('layouts.admin-app')

@section('title', 'Contact Submissions')

@section('content')
<div class="px-6 py-8 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Contact Submissions</h1>
            <p class="mt-0.5 text-sm text-slate-500">Incoming enquiries from the contact form.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search name, email, company…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>
        <select name="status"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            <option value="">All statuses</option>
            <option value="new"     {{ request('status') === 'new'     ? 'selected' : '' }}>New</option>
            <option value="read"    {{ request('status') === 'read'    ? 'selected' : '' }}>Read</option>
            <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
        </select>
        <button type="submit"
                class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
            Filter
        </button>
        @if(request()->hasAny(['q','status']))
            <a href="{{ route('admin.contact-submissions.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600 shadow-sm transition hover:bg-slate-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Stats chips --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">All: {{ $counts['all'] }}</span>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">New: {{ $counts['new'] }}</span>
        <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">Read: {{ $counts['read'] }}</span>
        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Replied: {{ $counts['replied'] }}</span>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3.5 text-left">#</th>
                    <th class="px-5 py-3.5 text-left">Name</th>
                    <th class="px-5 py-3.5 text-left">Email</th>
                    <th class="px-5 py-3.5 text-left">Company</th>
                    <th class="px-5 py-3.5 text-left">Type</th>
                    <th class="px-5 py-3.5 text-left">Status</th>
                    <th class="px-5 py-3.5 text-left">Date</th>
                    <th class="px-5 py-3.5 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($submissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors {{ $sub->status === 'new' ? 'font-semibold' : '' }}">
                        <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $sub->id }}</td>
                        <td class="px-5 py-3.5 text-slate-900">{{ $sub->name }}</td>
                        <td class="px-5 py-3.5 text-slate-600">
                            <a href="mailto:{{ $sub->email }}" class="hover:text-emerald-600">{{ $sub->email }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $sub->company ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $sub->inquiry_type ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($sub->status === 'new')
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> New
                                </span>
                            @elseif($sub->status === 'replied')
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Replied</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">Read</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">{{ $sub->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.contact-submissions.show', $sub) }}"
                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                    View
                                </a>
                                <form method="POST" action="{{ route('admin.contact-submissions.destroy', $sub) }}"
                                      onsubmit="return confirm('Delete this submission?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-sm text-slate-400">
                            No submissions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($submissions->hasPages())
        <div class="mt-4">
            {{ $submissions->links() }}
        </div>
    @endif

</div>
@endsection
