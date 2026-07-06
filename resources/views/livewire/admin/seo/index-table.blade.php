<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    {{-- Search --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 end-4 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="{{ __('app.seo_search') }}"
                   class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pe-11 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
        </div>
    </div>

    @if ($pages->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
            <svg class="mb-3 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">{{ __('app.seo_none_found') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.seo_page') }}</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">{{ __('app.title') }}</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</th>
                        <th class="px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($pages as $page)
                        <tr class="transition-colors hover:bg-slate-50">
                            {{-- Page label + route --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900">{{ $page->label ?: $page->route_name }}</p>
                                <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $page->route_name }}</p>
                            </td>

                            {{-- Title (both locales) --}}
                            <td class="hidden max-w-xs px-5 py-4 md:table-cell">
                                <p class="truncate text-slate-700">{{ $page->title_en }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-400" dir="rtl">{{ $page->title_ar }}</p>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center">
                                @if ($page->active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ __('app.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        {{ __('app.inactive') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-end">
                                <a href="{{ route('admin.seo.edit', $page) }}" wire:navigate
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                                    {{ __('app.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $pages->links() }}
            </div>
        @endif
    @endif
</div>
