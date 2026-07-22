<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'approvals';

    protected $fillable = [
        'name', 'issuing_body', 'approval_code',
        'normalized_key', 'description', 'active',
    ];

    protected $casts = ['active' => 'boolean'];
}
