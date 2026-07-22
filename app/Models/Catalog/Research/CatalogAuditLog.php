<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CatalogAuditLog extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'catalog_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'event', 'auditable_type', 'auditable_id',
        'before', 'after', 'meta', 'user_id', 'created_at',
    ];

    protected $casts = [
        'before'     => 'array',
        'after'      => 'array',
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
