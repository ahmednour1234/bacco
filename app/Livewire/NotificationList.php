<?php

namespace App\Livewire;

use App\Enums\NotificationTypeEnum;
use App\Models\NotificationRecipient;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public string $filter = 'all'; // all | unread | read

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $recipientId): void
    {
        NotificationRecipient::where('id', $recipientId)
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        NotificationRecipient::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $user = auth()->user();
        $hideBoqSubmitted = $user && ($user->isAdmin() || $user->isEmployee());

        $query = NotificationRecipient::with('notification')
            ->where('user_id', auth()->id())
            ->when($hideBoqSubmitted, function ($q): void {
                $q->whereHas('notification', function ($nq): void {
                    $nq->where('type', '!=', NotificationTypeEnum::BoqSubmitted->value);
                });
            })
            ->orderByDesc('created_at');

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20);

        $unreadCount = NotificationRecipient::where('user_id', auth()->id())
            ->when($hideBoqSubmitted, function ($q): void {
                $q->whereHas('notification', function ($nq): void {
                    $nq->where('type', '!=', NotificationTypeEnum::BoqSubmitted->value);
                });
            })
            ->whereNull('read_at')
            ->count();

        return view('livewire.notification-list', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
