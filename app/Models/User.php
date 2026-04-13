<?php

namespace App\Models;

use App\Enums\UserTypeEnum;
use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasPublicUuid, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'active'            => 'boolean',
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'user_type'         => UserTypeEnum::class,
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'deleted_at'        => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function supplierProfile(): HasOne
    {
        return $this->hasOne(SupplierProfile::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function quotationRequests(): HasMany
    {
        return $this->hasMany(QuotationRequest::class, 'client_id');
    }

    public function assignedQuotationRequests(): HasMany
    {
        return $this->hasMany(QuotationRequest::class, 'assigned_employee_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function boqs(): HasMany
    {
        return $this->hasMany(Boq::class, 'client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'client_id');
    }

    public function reviewedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'reviewed_by');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }

    public function notificationRecipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function quotationComments(): HasMany
    {
        return $this->hasMany(QuotationComment::class);
    }

    public function engineeringUpdates(): HasMany
    {
        return $this->hasMany(EngineeringUpdate::class, 'updated_by');
    }

    public function logisticsUpdates(): HasMany
    {
        return $this->hasMany(LogisticsUpdate::class, 'updated_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UploadedDocument::class, 'uploaded_by');
    }

    public function preparedQuotationVersions(): HasMany
    {
        return $this->hasMany(QuotationVersion::class, 'prepared_by');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->user_type === UserTypeEnum::Admin;
    }

    public function isEmployee(): bool
    {
        return $this->user_type === UserTypeEnum::Employee;
    }

    public function isClient(): bool
    {
        return $this->user_type === UserTypeEnum::Client;
    }

    public function isSupplier(): bool
    {
        return $this->user_type === UserTypeEnum::Supplier;
    }
}
