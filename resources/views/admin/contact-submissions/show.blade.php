@extends('layouts.admin-app')

@section('title', 'Submission — ' . $contactSubmission->name)

@section('content')
<div class="px-6 py-8 max-w-4xl mx-auto">

    {{-- Back --}}
    <a href="{{ route('admin.contact-submissions.index') }}"
       class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to submissions
    </a>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Header bar --}}
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $contactSubmission->name }}</h1>
                <p class="text-sm text-slate-500">Submitted {{ $contactSubmission->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Status badge --}}
                @if($contactSubmission->status === 'new')
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> New
                    </span>
                @elseif($contactSubmission->status === 'replied')
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Replied</span>
                @else
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Read</span>
                @endif

                {{-- Status form --}}
                <form method="POST" action="{{ route('admin.contact-submissions.update-status', $contactSubmission) }}"
                      class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-emerald-500">
                        <option value="new"     {{ $contactSubmission->status === 'new'     ? 'selected' : '' }}>New</option>
                        <option value="read"    {{ $contactSubmission->status === 'read'    ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ $contactSubmission->status === 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                    <button type="submit"
                            class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">
                        Save
                    </button>
                </form>

                <a href="mailto:{{ $contactSubmission->email }}"
                   class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                    Reply by Email
                </a>
            </div>
        </div>

        {{-- Details grid --}}
        <div class="grid grid-cols-1 gap-0 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Email</p>
                    <a href="mailto:{{ $contactSubmission->email }}" class="text-sm font-medium text-emerald-700 hover:underline">
                        {{ $contactSubmission->email }}
                    </a>
                </div>
                @if($contactSubmission->phone)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Phone</p>
                    <a href="tel:{{ $contactSubmission->phone }}" class="text-sm text-slate-800">{{ $contactSubmission->phone }}</a>
                </div>
                @endif
                @if($contactSubmission->company)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Company</p>
                    <p class="text-sm text-slate-800">{{ $contactSubmission->company }}</p>
                </div>
                @endif
                @if($contactSubmission->role)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Role</p>
                    <p class="text-sm text-slate-800">{{ $contactSubmission->role }}</p>
                </div>
                @endif
            </div>
            <div class="px-6 py-5 space-y-4">
                @if($contactSubmission->inquiry_type)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Inquiry Type</p>
                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-0.5 text-xs font-semibold text-slate-700">
                        {{ $contactSubmission->inquiry_type }}
                    </span>
                </div>
                @endif
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">IP Address</p>
                    <p class="text-sm text-slate-500 font-mono">{{ $contactSubmission->ip_address ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Submitted At</p>
                    <p class="text-sm text-slate-700">{{ $contactSubmission->created_at->format('d M Y, H:i:s') }}</p>
                </div>
            </div>
        </div>

        {{-- Message --}}
        <div class="border-t border-slate-100 px-6 py-6">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Message</p>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $contactSubmission->message }}</div>
        </div>

        {{-- Delete --}}
        <div class="border-t border-slate-100 px-6 py-4 flex justify-end">
            <form method="POST" action="{{ route('admin.contact-submissions.destroy', $contactSubmission) }}"
                  onsubmit="return confirm('Permanently delete this submission?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                    Delete Submission
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
