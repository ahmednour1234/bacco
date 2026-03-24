<?php

namespace App\Models;

use App\Enums\NotificationChannelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends BaseModel
{
    use HasFactory;

    protected $table = 'qimta_notifications';

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'data'    => 'array',
            'channel' => NotificationChannelEnum::class,
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }
}
