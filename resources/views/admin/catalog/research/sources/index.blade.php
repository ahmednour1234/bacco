@extends('layouts.admin-app')
@section('title', 'Source Register')
@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Source Register</h1>

    <form method="GET" class="flex flex-wrap gap-3">
        <select name="source_type" class="rounded-lg border-gray-300 text-sm">
            <option value="">All source types</option>
            @foreach(\App\Enums\Catalog\Research\SourceTypeEnum::cases() as $st)
                <option value="{{ $st->value }}" @selected(request('source_type')===$st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="official_only" value="1" @checked(request('official_only')) class="rounded border-gray-300 text-emerald-600"> Official only
        </label>
        <button class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr>
                <th class="px-4 py-3 text-start">Title</th>
                <th class="px-4 py-3 text-start">Type</th>
                <th class="px-4 py-3 text-start">Manufacturer</th>
                <th class="px-4 py-3 text-start">Domain</th>
                <th class="px-4 py-3 text-start">Official</th>
                <th class="px-4 py-3 text-start">Checked</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sources as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($s->source_url)<a href="{{ $s->source_url }}" target="_blank" rel="noopener" class="text-emerald-600 hover:underline">{{ $s->title ?: 'Source' }}</a>
                            @else{{ $s->title }}@endif
                        </td>
                        <td class="px-4 py-3">{{ $s->source_type?->label() }}</td>
                        <td class="px-4 py-3">{{ $s->manufacturer?->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $s->domain }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $s->is_official ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $s->is_official ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $s->checked_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No sources recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sources->links() }}
</div>
@endsection
