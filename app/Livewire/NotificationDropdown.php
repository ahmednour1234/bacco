<?php

namespace App\Livewire;

use App\Models\NotificationRecipient;
use Illuminate\Support\Carbon;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public int $unreadCount = 0;

    public function getListeners(): array
    {
        return ['notification-sent' => 'refreshNotifications'];
    }

    public function refreshNotifications(): void
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function markAsRead(int $recipientId): ?string
    {
        $recipient = NotificationRecipient::with('notification')
            ->where('id', $recipientId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $recipient) {
            return null;
        }

        if (! $recipient->read_at) {
            $recipient->update(['read_at' => now()]);
            $this->unreadCount = max(0, $this->unreadCount - 1);
        }

        $url = $recipient->notification->data['action_url'] ?? null;

        if ($url) {
            return $this->redirect($url);
        }

        return null;
    }

    public function markAllAsRead(): void
    {
        NotificationRecipient::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->unreadCount = 0;
    }

    public function render()
    {
        $this->unreadCount = $this->getUnreadCount();

        $notifications = NotificationRecipient::with('notification')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function (NotificationRecipient $r) {
                $n = $r->notification;
                return (object) [
                    'id'         => $r->id,
                    'title'      => $n->title,
                    'body'       => $n->body,
                    'type'       => $n->type,
                    'action_url' => $n->data['action_url'] ?? null,
                    'is_read'    => $r->read_at !== null,
                    'time_ago'   => Carbon::parse($r->created_at)->diffForHumans(),
                ];
            });

        return view('livewire.notification-dropdown', [
            'notifications' => $notifications,
        ]);
    }

    private function getUnreadCount(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return NotificationRecipient::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }
}
