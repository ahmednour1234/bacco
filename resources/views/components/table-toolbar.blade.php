@php
    $action = $action ?? url()->current();
    $searchName = $searchName ?? 'search';
    $searchValue = request($searchName);
    $searchPlaceholder = $searchPlaceholder ?? 'Search by name';
    $perPageName = $perPageName ?? 'per_page';
    $perPage = (int) request($perPageName, 10);
    $perPageOptions = $perPageOptions ?? [10, 50, 25, 5];
    $except = array_unique(array_merge([$searchName, $perPageName, 'page'], $except ?? []));
    $persisted = request()->except($except);
@endphp

<div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" action="{{ $action }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
        @foreach ($persisted as $key => $value)
            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="relative w-full sm:max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input
                type="search"
                name="{{ $searchName }}"
                value="{{ $searchValue }}"
                placeholder="{{ $searchPlaceholder }}"
                class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        <div class="flex items-center gap-2">
            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                Search
            </button>

            @if ($searchValue || request()->filled($perPageName))
                <a
                    href="{{ $action }}"
                    class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                >
                    Reset
                </a>
            @endif
        </div>
    </form>

    <form method="GET" action="{{ $action }}" class="flex items-center gap-3 self-end sm:self-auto">
        @foreach ($persisted as $key => $value)
            @if ($key === $searchName && blank($searchValue))
                @continue
            @endif

            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        @if (! blank($searchValue))
            <input type="hidden" name="{{ $searchName }}" value="{{ $searchValue }}">
        @endif

        <label for="{{ $perPageName }}" class="text-sm font-medium text-slate-500">Per page</label>
        <select
            id="{{ $perPageName }}"
            name="{{ $perPageName }}"
            onchange="this.form.submit()"
            class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
        >
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </form>
</div>
