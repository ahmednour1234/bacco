@extends('layouts.enduser-app')

@section('title', 'Dashboard – Qimta')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Home</span>
    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-xs text-slate-500 font-medium">Dashboard</span>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════
     STATS CARDS
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

    {{-- Total Quotations --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['total_quotations'] ?? 0 }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Quotations</p>
    </div>

    {{-- Active Quotations --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_quotations'] ?? 0 }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Quotations</p>
    </div>

    {{-- Completed Quotations --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['completed_quotations'] ?? 0 }}</p>
        <p class="text-sm text-slate-500 mt-1">Completed Quotations</p>
    </div>

    {{-- Active Projects --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-violet-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['active_projects'] ?? 0 }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Projects</p>
    </div>

    {{-- Completed Projects --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
        <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ $stats['completed_projects'] ?? 0 }}</p>
        <p class="text-sm text-slate-500 mt-1">Completed Projects</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TWO-COLUMN ROW: Track Quotations + Accepted Quotations
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ─────────────────────────────────────────────────────
         TRACK QUOTATIONS (wider column)
    ───────────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Track Quotations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest quotation requests</p>
            </div>
            <a href="#"
               class="text-xs font-medium text-emerald-600 hover:text-emerald-700
                      bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                View all
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                            Quotation ID
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                            Date
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                            Items
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                            Status
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentQuotations ?? [] as $quotation)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-900">#{{ $quotation->id ?? 'QT-0001' }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ isset($quotation->created_at) ? $quotation->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $quotation->items_count ?? 0 }} items
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $status = $quotation->status ?? 'pending';
                                $badge = match($status) {
                                    'accepted', 'approved' => ['bg-emerald-100 text-emerald-700', 'Accepted'],
                                    'pending'              => ['bg-amber-100 text-amber-700',   'Pending'],
                                    'rejected', 'cancelled'=> ['bg-red-100 text-red-700',       'Rejected'],
                                    'in_review'            => ['bg-blue-100 text-blue-700',      'In Review'],
                                    default                => ['bg-slate-100 text-slate-600',    ucfirst($status)],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    {{-- Placeholder rows --}}
                    @foreach([
                        ['QT-0042', 'Mar 12, 2026', 5,  'Accepted',  'bg-emerald-100 text-emerald-700'],
                        ['QT-0041', 'Mar 10, 2026', 3,  'Pending',   'bg-amber-100 text-amber-700'],
                        ['QT-0039', 'Mar 08, 2026', 8,  'In Review', 'bg-blue-100 text-blue-700'],
                        ['QT-0037', 'Mar 05, 2026', 2,  'Rejected',  'bg-red-100 text-red-700'],
                        ['QT-0035', 'Mar 01, 2026', 6,  'Accepted',  'bg-emerald-100 text-emerald-700'],
                    ] as [$id, $date, $items, $status, $class])
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-900">#{{ $id }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $date }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $items }} items</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                View →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────
         ACCEPTED QUOTATIONS (narrower column)
    ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Accepted Quotations</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ready for order</p>
            </div>
            <span class="text-xs font-semibold bg-emerald-500 text-white w-6 h-6 rounded-full flex items-center justify-center">
                {{ $stats['accepted_quotations'] ?? 3 }}
            </span>
        </div>

        {{-- List --}}
        <div class="divide-y divide-slate-100">
            @forelse($acceptedQuotations ?? [] as $quotation)
            <div class="px-6 py-4 hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-900 truncate">#{{ $quotation->id }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ isset($quotation->created_at) ? $quotation->created_at->format('M d, Y') : '-' }}
                        </p>
                    </div>
                    <a href="#"
                       class="text-xs font-medium text-white bg-emerald-500 hover:bg-emerald-600
                              px-3 py-1.5 rounded-lg transition-colors shrink-0">
                        Order
                    </a>
                </div>
            </div>
            @empty
            {{-- Placeholder items --}}
            @foreach([
                ['QT-0042', 'Mar 12, 2026', '5 items', 'SAR 12,400'],
                ['QT-0040', 'Mar 09, 2026', '3 items', 'SAR 8,750'],
                ['QT-0035', 'Mar 01, 2026', '6 items', 'SAR 22,100'],
            ] as [$id, $date, $items, $amount])
            <div class="px-6 py-4 hover:bg-slate-50/60 transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-slate-900">#{{ $id }}</p>
                            <span class="text-xs text-slate-400">·</span>
                            <p class="text-xs text-slate-400">{{ $items }}</p>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $date }}</p>
                        <p class="text-sm font-semibold text-emerald-600 mt-1">{{ $amount }}</p>
                    </div>
                    <a href="#"
                       class="text-xs font-medium text-white bg-emerald-500 hover:bg-emerald-600
                              px-3 py-1.5 rounded-lg transition-colors shrink-0 mt-0.5">
                        Place Order
                    </a>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>

        {{-- Footer link --}}
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
            <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                View all accepted →
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ACTIVE PROJECTS ROW
══════════════════════════════════════════════════════════ --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Active Projects</h2>
            <p class="text-xs text-slate-400 mt-0.5">Your ongoing construction projects</p>
        </div>
        <a href="#"
           class="text-xs font-medium text-emerald-600 hover:text-emerald-700
                  bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
            View all
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Project</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Start Date</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Progress</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($activeProjects ?? [] as $project)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $project->name ?? '-' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">#{{ $project->id }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ isset($project->start_date) ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        @php $pct = $project->progress ?? 0; @endphp
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-100 rounded-full h-2 min-w-[80px]">
                                <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500 shrink-0">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                            View →
                        </a>
                    </td>
                </tr>
                @empty
                {{-- Placeholder rows --}}
                @foreach([
                    ['Al-Noor Tower', 'PRJ-001', 'Jan 15, 2026', 65],
                    ['Riyadh Residences', 'PRJ-002', 'Feb 01, 2026', 42],
                    ['Commercial Hub B', 'PRJ-003', 'Feb 20, 2026', 18],
                ] as [$name, $id, $date, $pct])
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">#{{ $id }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-500">{{ $date }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-100 rounded-full h-2 min-w-[80px]">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500 shrink-0">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                            View →
                        </a>
                    </td>
                </tr>
                @endforeach
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
