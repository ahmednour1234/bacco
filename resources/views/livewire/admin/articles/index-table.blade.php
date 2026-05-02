<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    @if (session('success'))
        <div class="m-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Search + per-page --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search articles…"
                   class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 placeholder-slate-400 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
        </div>

        <div class="flex items-center gap-3 self-end sm:self-auto">
            <label class="text-sm font-medium text-slate-500">Per page</label>
            <select wire:model.live="perPage"
                    class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @foreach ([5, 10, 25, 50] as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($articles->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
            <svg class="mb-3 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2zM12 11v6M9 14h6"/>
            </svg>
            <p class="text-sm font-medium">No articles found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Image</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Name (EN / AR)</th>
                        <th class="hidden px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">Title</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                        <th class="px-5 py-3.5 text-end text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($articles as $article)
                        <tr class="transition-colors hover:bg-slate-50">
                            {{-- Image --}}
                            <td class="px-5 py-4">
                                @if ($article->image)
                                    <img src="{{ Storage::url($article->image) }}"
                                         alt="{{ $article->name_en }}"
                                         class="h-12 w-12 rounded-lg object-cover border border-slate-200">
                                @else
                                    <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            {{-- Name --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900">{{ $article->name_en }}</p>
                                <p class="text-xs text-slate-400 mt-0.5" dir="rtl">{{ $article->name_ar }}</p>
                            </td>

                            {{-- Title --}}
                            <td class="hidden max-w-xs px-5 py-4 md:table-cell">
                                <p class="truncate text-slate-700">{{ $article->title_en }}</p>
                                <p class="truncate text-xs text-slate-400 mt-0.5" dir="rtl">{{ $article->title_ar }}</p>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center">
                                @if ($article->active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>

                            {{-- Created --}}
                            <td class="px-5 py-4 text-slate-500 text-xs whitespace-nowrap">
                                {{ $article->created_at->format('d M Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-end">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.articles.edit', $article) }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200">
                                        Edit
                                    </a>
                                    <button type="button"
                                            onclick="if(!confirm('Delete article: {{ addslashes($article->name_en) }}? This cannot be undone.')) return;"
                                            wire:click="delete('{{ $article->uuid }}')"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-100">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($articles->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $articles->links() }}
            </div>
        @endif
    @endif
</div>
