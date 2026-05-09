<?php

namespace App\Models\Scraper;

use Illuminate\Database\Eloquent\Model;

class ScraperProduct extends Model
{
    protected $connection = 'scraper';
    protected $table      = 'scraper_products';
    protected $guarded    = ['id'];

    protected $casts = [
        'price'           => 'decimal:2',
        'raw_data'        => 'array',
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

    public function scraperBrand()
    {
        return $this->belongsTo(ScraperBrand::class, 'scraper_brand_id');
    }

    public function scraperCategory()
    {
        return $this->belongsTo(ScraperCategory::class, 'scraper_category_id');
    }
}
