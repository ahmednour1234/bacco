@extends('layouts.enduser-app')

@section('title', __('app.title_my_projects'))
@section('page-title', __('app.projects'))

@section('breadcrumb')
    <span class="text-xs text-slate-400">{{ __('app.home') }}</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-emerald-700 font-medium">{{ __('app.projects') }}</span>
@endsection

@section('content')
<div class="mx-auto max-w-7xl space-y-5">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">

        {{-- Total --}}
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md"
             style="border: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between px-4 pt-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                         style="background:#ecfdf5; border:1px solid #d1fae5;">
                        <svg class="h-5 w-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <svg class="h-10 w-16 flex-shrink-0 opacity-60" viewBox="0 0 64 40" fill="none">
                    <polyline points="0,30 12,22 24,25 36,15 48,18 64,9"
                              stroke="#047857" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                {{ __('app.total_projects') }}
            </p>
        </div>

        {{-- Active --}}
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md"
             style="border: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between px-4 pt-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                         style="background:#ecfdf5; border:1px solid #d1fae5;">
                        <svg class="h-5 w-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['active'] }}</p>
                </div>
                <svg class="h-10 w-16 flex-shrink-0 opacity-60" viewBox="0 0 64 40" fill="none">
                    <polyline points="0,32 10,28 22,20 32,24 44,13 64,8"
                              stroke="#047857" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                {{ __('app.active') }}
            </p>
        </div>

        {{-- Completed --}}
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md"
             style="border: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between px-4 pt-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                         style="background:#ecfdf5; border:1px solid #d1fae5;">
                        <svg class="h-5 w-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['completed'] }}</p>
                </div>
                <svg class="h-10 w-16 flex-shrink-0 opacity-60" viewBox="0 0 64 40" fill="none">
                    <polyline points="0,23 14,26 26,19 38,22 50,15 64,18"
                              stroke="#047857" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                {{ __('app.completed') }}
            </p>
        </div>

        {{-- Pending --}}
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md"
             style="border: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between px-4 pt-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                         style="background:#ecfdf5; border:1px solid #d1fae5;">
                        <svg class="h-5 w-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['pending'] }}</p>
                </div>
                <svg class="h-10 w-16 flex-shrink-0 opacity-60" viewBox="0 0 64 40" fill="none">
                    <polyline points="0,16 10,22 20,18 30,26 44,21 56,24 64,18"
                              stroke="#047857" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                {{ __('app.pending') }}
            </p>
        </div>

        {{-- Cancelled --}}
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md"
             style="border: 1px solid #e5e7eb;">
            <div class="flex items-center justify-between px-4 pt-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl"
                         style="background:#f8fafc; border:1px solid #e5e7eb;">
                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-black leading-none text-slate-900">{{ $stats['cancelled'] }}</p>
                </div>
                <svg class="h-10 w-16 flex-shrink-0 opacity-50" viewBox="0 0 64 40" fill="none">
                    <polyline points="0,12 12,18 24,17 36,24 48,22 64,30"
                              stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="pb-3 pl-4 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                {{ __('app.status_cancelled') }}
            </p>
        </div>

    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('enduser.projects.index') }}" class="flex flex-wrap items-center gap-3">

        <div class="relative min-w-0 flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>

            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="{{ __('app.search_projects') }}"
                   class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>

        <select name="status"
                onchange="this.form.submit()"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            <option value="">{{ __('app.all_statuses') }}</option>
            @foreach(\App\Enums\ProjectStatusEnum::cases() as $s)
                <option value="{{ $s->value }}" @selected($status === $s->value)>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>

        <select name="per_page"
                onchange="this.form.submit()"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            @foreach([5, 10, 25, 50] as $n)
                <option value="{{ $n }}" @selected($perPage === $n)>
                    {{ $n }} / page
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="h-11 rounded-xl border border-emerald-200 bg-emerald-50 px-5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">
            {{ __('app.search') }}
        </button>

        <a href="{{ route('enduser.projects.index') }}"
           class="h-11 inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-bold text-slate-500 transition hover:bg-slate-50">
            {{ __('app.clear') }}
        </a>
    </form>

    {{-- Projects Table --}}
    @if($projects->isEmpty())

        <div class="rounded-3xl bg-white shadow-sm" style="border: 1px solid #e5e7eb;">
            <div class="flex flex-col items-center py-20 px-10">

                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-3xl"
                     style="background:#ecfdf5; border:1px solid #d1fae5;">
                    <svg class="h-10 w-10 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                              d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                </div>

                <h3 class="mt-6 text-xl font-black text-slate-800">
                    {{ __('app.no_projects_yet') }}
                </h3>

                <p class="mt-2 text-sm font-medium text-slate-400">
                    {{ __('app.create_boq_first_project') }}
                </p>

                <a href="{{ route('enduser.boqs.create') }}"
                   class="mt-8 inline-flex items-center gap-2.5 rounded-full bg-emerald-600 px-8 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-700">
                    <span class="text-lg leading-none">+</span>
                    {{ __('app.new_boq') }}
                </a>

            </div>
        </div>

    @else

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm" style="border: 1px solid #e5e7eb;">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            {{ __('app.project') }}
                        </th>

                        <th class="px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden sm:table-cell">
                            {{ __('app.boq_count') }}
                        </th>

                        <th class="px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden md:table-cell">
                            {{ __('app.quotation_count') }}
                        </th>

                        <th class="px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden md:table-cell">
                            {{ __('app.order_count') }}
                        </th>

                        <th class="px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">
                            {{ __('app.created') }}
                        </th>

                        <th class="px-3 py-4 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            {{ __('app.status') }}
                        </th>

                        <th class="px-6 py-4 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            {{ __('app.actions') }}
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($projects as $project)

                        @php
                            $statusColor = match($project->status->value ?? 'pending') {
                                'active'    => [
                                    'bg' => '#ecfdf5',
                                    'text' => '#047857',
                                    'border' => '#a7f3d0'
                                ],
                                'completed' => [
                                    'bg' => '#ecfdf5',
                                    'text' => '#047857',
                                    'border' => '#a7f3d0'
                                ],
                                'on_hold'   => [
                                    'bg' => '#f8fafc',
                                    'text' => '#475569',
                                    'border' => '#e2e8f0'
                                ],
                                'cancelled' => [
                                    'bg' => '#f8fafc',
                                    'text' => '#475569',
                                    'border' => '#e2e8f0'
                                ],
                                default     => [
                                    'bg' => '#ecfdf5',
                                    'text' => '#047857',
                                    'border' => '#a7f3d0'
                                ],
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50">

                            {{-- Name + code --}}
                            <td class="px-6 py-5 max-w-[260px]">
                                <p class="truncate font-bold text-slate-900">
                                    {{ $project->name }}
                                </p>

                                <p class="mt-1 font-mono text-[11px] text-slate-400">
                                    {{ $project->project_no }}
                                </p>
                            </td>

                            {{-- BOQs --}}
                            <td class="px-3 py-5 text-center hidden sm:table-cell">
                                <span class="font-semibold text-slate-600">
                                    {{ $project->boqs_count }}
                                </span>
                            </td>

                            {{-- Quotations --}}
                            <td class="px-3 py-5 text-center hidden md:table-cell">
                                <span class="font-semibold text-slate-600">
                                    {{ $project->quotation_requests_count }}
                                </span>
                            </td>

                            {{-- Orders --}}
                            <td class="px-3 py-5 text-center hidden md:table-cell">
                                <span class="font-semibold text-slate-600">
                                    {{ $project->orders_count }}
                                </span>
                            </td>

                            {{-- Created --}}
                            <td class="px-3 py-5 text-center text-[12px] text-slate-500 hidden lg:table-cell whitespace-nowrap">
                                {{ $project->created_at->diffForHumans() }}
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-5 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold"
                                      style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; border: 1px solid {{ $statusColor['border'] }};">
                                    {{ $project->status->label() }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="pl-3 pr-6 py-5 text-right">
                                <div class="inline-flex items-center gap-2">

                                    <a href="{{ route('enduser.boqs.create.project', $project->uuid) }}"
                                       class="flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-300 bg-white text-lg font-bold text-emerald-700 transition hover:bg-emerald-50"
                                       title="{{ __('app.new_boq') }}">
                                        +
                                    </a>

                                    <a href="{{ route('enduser.projects.show', $project->uuid) }}"
                                       class="inline-flex h-9 items-center gap-1 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:border-slate-300">
                                        {{ __('app.view') }}

                                        <svg class="h-3.5 w-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $projects->links() }}
        </div>

    @endif

</div>
@endsection
