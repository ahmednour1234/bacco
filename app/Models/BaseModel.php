<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasPublicUuid;

    /**
     * The attributes that are not mass assignable.
     * Guard only the real PK and uuid (uuid is auto-generated).
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * Default casts shared by every model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active'     => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
