<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ __('app.notifications') }}</h2>
            @if($unreadCount > 0)
                <p class="mt-1 text-sm text-slate-500">
                    {{ $unreadCount }} {{ __('app.unread_notifications') }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            {{-- Filter --}}
            <div class="flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                @foreach(['all' => __('app.all'), 'unread' => __('app.unread'), 'read' => __('app.read')] as $val => $label)
                    <button wire:click="$set('filter', '{{ $val }}')"
                            class="rounded-lg px-3.5 py-1.5 text-sm font-medium transition-all
                                   {{ $filter === $val
                                       ? 'bg-emerald-500 text-white shadow-sm'
                                       : 'text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Mark all read --}}
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('app.mark_all_read') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Notifications --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $recipient)
                @php
                    $n = $recipient->notification;
                    $iconType = \App\Enums\NotificationTypeEnum::tryFrom($n->type)?->icon() ?? 'info';
                    $isRead = $recipient->read_at !== null;
                    $actionUrl = $n->data['action_url'] ?? null;
                @endphp

                <div class="flex items-start gap-4 px-5 py-4 transition
                            {{ $isRead ? 'bg-white' : 'bg-blue-50/40' }}
                            hover:bg-slate-50">

                    {{-- Icon --}}
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full
                        @if($iconType === 'success') bg-emerald-100
                        @elseif($iconType === 'warning') bg-amber-100
                        @elseif($iconType === 'quotation') bg-violet-100
                        @else bg-blue-100
                        @endif
                    ">
                        @if($iconType === 'success')
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                        @elseif($iconType === 'warning')
                            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @elseif($iconType === 'quotation')
                            <svg class="h-5 w-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 {{ $isRead ? 'font-medium' : '' }}">
                                    {{ $n->title }}
                                </p>
                                <p class="mt-0.5 text-sm text-slate-500">{{ $n->body }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if(!$isRead)
                                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="text-xs text-slate-400">
                                {{ \Illuminate\Support\Carbon::parse($recipient->created_at)->diffForHumans() }}
                            </span>
                            @if(!$isRead)
                                <button wire:click="markAsRead({{ $recipient->id }})"
                                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                    {{ __('app.mark_as_read') }}
                                </button>
                            @endif
                            @if($actionUrl)
                                <a href="{{ $actionUrl }}" wire:navigate
                                   class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                    {{ __('app.view_details') }} →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-slate-500">{{ __('app.no_notifications') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('app.no_notifications_desc') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
