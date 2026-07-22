@extends('layouts.admin-app')
@section('title', 'Product Families')
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Product Families</h1>
        <a href="{{ route('admin.catalog.research.imports.index') }}" class="text-sm text-emerald-600 hover:underline">← Imports</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3">
        <input name="search" value="{{ request('search') }}" placeholder="Search…"
               class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        <select name="research_status" class="rounded-lg border-gray-300 text-sm">
            <option value="">All statuses</option>
            @foreach(\App\Enums\Catalog\Research\ResearchStatusEnum::cases() as $st)
                <option value="{{ $st->value }}" @selected(request('research_status')===$st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-start">Name</th>
                    <th class="px-4 py-3 text-start">Code</th>
                    <th class="px-4 py-3 text-start">Research Status</th>
                    <th class="px-4 py-3 text-end">Manufacturers</th>
                    <th class="px-4 py-3 text-end">Variants</th>
                    <th class="px-4 py-3 text-end"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $family)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $family->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $family->source_code }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                {{ $family->research_status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end text-gray-600">{{ $family->manufacturers()->count() }}</td>
                        <td class="px-4 py-3 text-end text-gray-600">{{ $family->variants()->count() }}</td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.catalog.research.families.show', $family->uuid) }}" class="text-emerald-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No product families yet. Import a workbook first.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
