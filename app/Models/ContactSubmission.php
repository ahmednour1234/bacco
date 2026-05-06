<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'role',
        'inquiry_type',
        'message',
        'status',
        'ip_address',
    ];

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function markAsRead(): void
    {
        if ($this->status === 'new') {
            $this->update(['status' => 'read']);
        }
    }
}
