<?php

namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;

class ScraperBrand extends Model
{
    protected $connection = 'scraper';
    protected $table      = 'scraper_brands';
    protected $guarded    = ['id'];

    protected $casts = [
        'is_synced'       => 'boolean',
        'synced_at'       => 'datetime',
        'last_scraped_at' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(ScraperSource::class, 'source_id');
    }

    public function products()
    {
        return $this->hasMany(ScraperProduct::class, 'scraper_brand_id');
    }
}
