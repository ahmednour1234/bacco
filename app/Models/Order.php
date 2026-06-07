<?php

namespace App\Models;

use App\Enums\EnduserOrderStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'total_amount' => 'decimal:2',
            'vat_amount'   => 'decimal:2',
            'grand_total'  => 'decimal:2',
            'deleted_at'   => 'datetime',
        ]);
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $enum = OrderStatusEnum::tryFrom($value);
                if ($enum) {
                    return $enum;
                }
                // Map old statuses to new ones
                return match ($value) {
                    'completed', 'cancelled', 'refunded' => OrderStatusEnum::Closed,
                    default => OrderStatusEnum::Open,
                };
            },
            set: fn ($value) => $value instanceof OrderStatusEnum ? $value->value : $value,
        );
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function quotationRequest(): BelongsTo
    {
        return $this->belongsTo(QuotationRequest::class);
    }

    public function quotationVersion(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function enduserStatus(): EnduserOrderStatusEnum
    {
        if (($this->status->value ?? $this->status) === OrderStatusEnum::Closed->value) {
            return EnduserOrderStatusEnum::Closed;
        }

        $payments = $this->relationLoaded('payments') ? $this->payments : null;

        $hasApprovedPayment = $payments
            ? $payments->contains(fn (Payment $payment) => $payment->status === PaymentStatusEnum::Approved)
            : $this->payments()->where('status', PaymentStatusEnum::Approved->value)->exists();

        if ($hasApprovedPayment) {
            return EnduserOrderStatusEnum::OpenPaymentConfirmed;
        }

        $latestPayment = $this->relationLoaded('latestPayment')
            ? $this->latestPayment
            : $this->latestPayment()->first();

        return $latestPayment?->status === PaymentStatusEnum::Submitted
            ? EnduserOrderStatusEnum::OpenReceiptUnderReview
            : EnduserOrderStatusEnum::OpenUnpaid;
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function engineeringUpdates(): HasMany
    {
        return $this->hasMany(EngineeringUpdate::class);
    }

    public function logisticsUpdates(): HasMany
    {
        return $this->hasMany(LogisticsUpdate::class);
    }
}
