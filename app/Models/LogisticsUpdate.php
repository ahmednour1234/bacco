<?php

namespace App\Models;

use App\Enums\LogisticsStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsUpdate extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => LogisticsStatusEnum::class,
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
