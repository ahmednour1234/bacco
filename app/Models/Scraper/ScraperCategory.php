<?php

namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;

class ScraperCategory extends Model
{
    protected $connection = 'scraper';
    protected $table      = 'scraper_categories';
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
        return $this->hasMany(ScraperProduct::class, 'scraper_category_id');
    }
}
