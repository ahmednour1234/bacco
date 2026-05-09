<?php

namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;

class ScraperSource extends Model
{
    protected $connection = 'scraper';
    protected $table      = 'scraper_sources';
    protected $guarded    = ['id'];

    protected $casts = [
        'active'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function brands()
    {
        return $this->hasMany(ScraperBrand::class, 'source_id');
    }

    public function categories()
    {
        return $this->hasMany(ScraperCategory::class, 'source_id');
    }

    public function products()
    {
        return $this->hasMany(ScraperProduct::class, 'source_id');
    }
}
