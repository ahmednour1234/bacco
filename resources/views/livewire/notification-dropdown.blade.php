<div x-data="{ open: false }" class="relative" wire:poll.30s="refreshNotifications">
    {{-- Bell button --}}
    <button @click="open = !open"
            class="relative p-2 text-slate-500 hover:text-slate-700
                   hover:bg-slate-100 rounded-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute end-0 top-full mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl
                border border-slate-200 shadow-xl overflow-hidden z-50">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-900">{{ __('app.notifications') }}</span>
            <div class="flex items-center gap-2">
                @if($unreadCount > 0)
                    <span class="text-xs bg-red-100 text-red-600 font-medium px-2 py-0.5 rounded-full">
                        {{ $unreadCount }} {{ __('app.new') }}
                    </span>
                    <button wire:click="markAllAsRead" class="text-xs text-slate-400 hover:text-slate-600 transition-colors" title="{{ __('app.mark_all_read') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Notification list --}}
        <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
            @forelse($notifications as $notification)
                <button
                    wire:click="markAsRead({{ $notification->id }})"
                    class="flex gap-3 px-4 py-3 hover:bg-slate-50 transition-colors w-full text-start
                           {{ $notification->is_read ? 'opacity-60' : '' }}"
                >
                    {{-- Icon --}}
                    @php
                        $iconType = \App\Enums\NotificationTypeEnum::tryFrom($notification->type)?->icon() ?? 'info';
                    @endphp
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5
                        @if($iconType === 'success') bg-emerald-100
                        @elseif($iconType === 'warning') bg-amber-100
                        @elseif($iconType === 'quotation') bg-violet-100
                        @else bg-blue-100
                        @endif
                    ">
                        @if($iconType === 'success')
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                        @elseif($iconType === 'warning')
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @elseif($iconType === 'quotation')
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $notification->title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $notification->body }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification->time_ago }}</p>
                    </div>

                    {{-- Unread dot --}}
                    @if(! $notification->is_read)
                        <span class="w-2 h-2 bg-blue-500 rounded-full shrink-0 mt-2"></span>
                    @endif
                </button>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-xs text-slate-400 mt-2">{{ __('app.no_notifications') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($notifications->count() > 0)
            <div class="px-4 py-3 border-t border-slate-100">
                @php
                    $userType = auth()->user()->type ?? null;
                    $notifRoute = match($userType?->value ?? $userType) {
                        'admin', 'employee' => route('admin.notifications'),
                        'supplier' => route('supplier.notifications'),
                        default => route('enduser.notifications'),
                    };
                @endphp
                <a href="{{ $notifRoute }}" wire:navigate class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                    {{ __('app.view_all_notifications') }} →
                </a>
            </div>
        @endif
    </div>
</div>
