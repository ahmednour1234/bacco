<?php

namespace App\Services;

use App\Enums\NotificationChannelEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\UserTypeEnum;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send a notification to specific users.
     */
    public function send(
        string               $title,
        string               $body,
        NotificationTypeEnum $type,
        array                $recipientIds,
        ?string              $actionUrl = null,
        ?array               $extraData = null,
    ): Notification {
        $data = array_filter([
            'action_url' => $actionUrl,
            ...(array) $extraData,
        ]);

        $notification = Notification::create([
            'title'   => $title,
            'body'    => $body,
            'type'    => $type->value,
            'channel' => NotificationChannelEnum::Database->value,
            'data'    => $data ?: null,
        ]);

        $rows = collect($recipientIds)->unique()->map(fn (int $userId) => [
            'notification_id' => $notification->id,
            'user_id'         => $userId,
            'uuid'            => (string) \Illuminate\Support\Str::uuid(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ])->all();

        \App\Models\NotificationRecipient::insert($rows);

        return $notification;
    }

    /**
     * Send a notification to all users of a given type.
     */
    public function sendToUserType(
        string               $title,
        string               $body,
        NotificationTypeEnum $type,
        UserTypeEnum         $userType,
        ?string              $actionUrl = null,
        ?array               $extraData = null,
    ): Notification {
        $ids = User::where('user_type', $userType->value)
            ->where('active', true)
            ->pluck('id')
            ->all();

        return $this->send($title, $body, $type, $ids, $actionUrl, $extraData);
    }

    /**
     * Send to multiple user types at once.
     */
    public function sendToUserTypes(
        string               $title,
        string               $body,
        NotificationTypeEnum $type,
        array                $userTypes,
        ?string              $actionUrl = null,
        ?array               $extraData = null,
    ): Notification {
        $ids = User::whereIn('user_type', collect($userTypes)->map->value->all())
            ->where('active', true)
            ->pluck('id')
            ->all();

        return $this->send($title, $body, $type, $ids, $actionUrl, $extraData);
    }

    /**
     * Send to a specific user + all admins.
     */
    public function sendToUserAndAdmins(
        string               $title,
        string               $body,
        NotificationTypeEnum $type,
        int                  $userId,
        ?string              $actionUrl = null,
        ?array               $extraData = null,
    ): Notification {
        $adminIds = User::where('user_type', UserTypeEnum::Admin->value)
            ->where('active', true)
            ->pluck('id')
            ->all();

        $allIds = array_unique(array_merge([$userId], $adminIds));

        return $this->send($title, $body, $type, $allIds, $actionUrl, $extraData);
    }
}
