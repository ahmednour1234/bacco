{{--
    Row pagination for the quotation tables.

    Shown only when there is more than one page. The page numbers are windowed
    around the current page: a 5,000-row BOQ is 52 pages, and rendering every
    number would be its own wall of markup.

    Expects: $page, $totalPages, $totalRows, $perPage
--}}
@if($totalPages > 1)
    @php
        // Window of page numbers around the current page, clamped to the ends.
        $window = 2;
        $from   = max(1, $page - $window);
        $to     = min($totalPages, $page + $window);

        $firstRow = (($page - 1) * $perPage) + 1;
        $lastRow  = min($page * $perPage, $totalRows);
    @endphp

    <div class="flex flex-col items-center gap-3 border-t border-slate-100 py-4">
        <p class="text-xs text-slate-500">
            {{ __('app.showing_rows_range', [
                'from'  => $firstRow,
                'to'    => $lastRow,
                'total' => $totalRows,
            ]) }}
        </p>

        <nav class="flex flex-wrap items-center justify-center gap-1" aria-label="{{ __('app.pagination') }}">
            {{-- Previous --}}
            <button
                type="button"
                wire:click="previousPage"
                @disabled($page <= 1)
                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                       {{ $page <= 1
                            ? 'cursor-not-allowed bg-slate-50 text-slate-300'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
                {{ __('app.previous') }}
            </button>

            {{-- Jump to first, when the window has moved away from it --}}
            @if($from > 1)
                <button type="button" wire:click="goToPage(1)"
                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">1</button>
                @if($from > 2)
                    <span class="px-1 text-xs text-slate-400">…</span>
                @endif
            @endif

            @for($p = $from; $p <= $to; $p++)
                <button
                    type="button"
                    wire:click="goToPage({{ $p }})"
                    @if($p === $page) aria-current="page" @endif
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                           {{ $p === $page
                                ? 'bg-emerald-500 text-white'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    {{ $p }}
                </button>
            @endfor

            {{-- Jump to last --}}
            @if($to < $totalPages)
                @if($to < $totalPages - 1)
                    <span class="px-1 text-xs text-slate-400">…</span>
                @endif
                <button type="button" wire:click="goToPage({{ $totalPages }})"
                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">{{ $totalPages }}</button>
            @endif

            {{-- Next --}}
            <button
                type="button"
                wire:click="nextPage"
                @disabled($page >= $totalPages)
                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                       {{ $page >= $totalPages
                            ? 'cursor-not-allowed bg-slate-50 text-slate-300'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
                {{ __('app.next') }}
            </button>
        </nav>
    </div>
@endif
