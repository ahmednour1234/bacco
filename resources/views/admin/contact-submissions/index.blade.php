@extends('layouts.admin-app')

@section('title', __('app.contact_submissions'))

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ __('app.contact_submissions') }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('app.contact_submissions_sub') }}</p>
        </div>
        {{-- Stat chips --}}
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>{{ __('app.all') }}&nbsp;<span class="text-slate-900">{{ $counts['all'] }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>{{ __('app.status_new') }}&nbsp;<span>{{ $counts['new'] }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>{{ __('app.status_read') }}&nbsp;<span>{{ $counts['read'] }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('app.status_replied') }}&nbsp;<span>{{ $counts['replied'] }}</span>
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="{{ __('app.search_name_email_company') }}"
                        class="flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 placeholder-slate-400 outline-none focus:ring-0">
                </div>
            </div>
            <select name="status"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                <option value="">{{ __('app.all_statuses') }}</option>
                <option value="new"     {{ request('status') === 'new'     ? 'selected' : '' }}>{{ __('app.status_new') }}</option>
                <option value="read"    {{ request('status') === 'read'    ? 'selected' : '' }}>{{ __('app.status_read') }}</option>
                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>{{ __('app.status_replied') }}</option>
            </select>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                {{ __('app.filter') }}
            </button>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('admin.contact-submissions.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-500 shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('app.clear') }}
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="w-12 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.name') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.email') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.company') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.type') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.date') }}</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($submissions as $sub)
                        <tr class="group transition hover:bg-slate-50/60 {{ $sub->status === 'new' ? 'bg-blue-50/30' : '' }}">

                            {{-- # --}}
                            <td class="px-5 py-4 text-xs font-medium text-slate-400">{{ $sub->id }}</td>

                            {{-- Name --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl
                                        {{ $sub->status === 'new' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}
                                        text-xs font-bold">
                                        {{ strtoupper(substr($sub->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-900">{{ $sub->name }}</span>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-5 py-4">
                                <a href="mailto:{{ $sub->email }}"
                                    class="text-slate-600 transition hover:text-emerald-600 hover:underline">
                                    {{ $sub->email }}
                                </a>
                            </td>

                            {{-- Company --}}
                            <td class="px-5 py-4">
                                @if($sub->company)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        {{ $sub->company }}
                                    </span>
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td class="px-5 py-4">
                                @if($sub->inquiry_type)
                                    @php
                                        $typeColors = [
                                            'boq'        => 'bg-indigo-50 text-indigo-700',
                                            'brand'      => 'bg-purple-50 text-purple-700',
                                            'enterprise' => 'bg-amber-50 text-amber-700',
                                            'general'    => 'bg-slate-100 text-slate-600',
                                        ];
                                        $tc = $typeColors[$sub->inquiry_type] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $tc }}">
                                        {{ ucfirst($sub->inquiry_type) }}
                                    </span>
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($sub->status === 'new')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>{{ __('app.status_new') }}
                                    </span>
                                @elseif($sub->status === 'replied')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('app.status_replied') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>{{ __('app.status_read') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-slate-700">{{ $sub->created_at->format('d M Y') }}</span>
                                    <span class="text-xs text-slate-400">{{ $sub->created_at->format('H:i') }}</span>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.contact-submissions.show', $sub) }}"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ __('app.view') }}
                                    </a>
                                    <form method="POST" action="{{ route('admin.contact-submissions.destroy', $sub) }}"
                                        onsubmit="return confirm(@js(__('app.delete_submission_confirm')))">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('app.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-slate-500">{{ __('app.no_submissions_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($submissions->hasPages())
        <div>{{ $submissions->links() }}</div>
    @endif

</div>
@endsection
