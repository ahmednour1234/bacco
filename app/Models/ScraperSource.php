<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Model;

class ScraperSource extends Model
{
    use HasPublicUuid;

    protected $table = 'scraper_sources';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active'     => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
