<?php

namespace App\Models;

use App\Enums\WebsiteTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'type'       => WebsiteTypeEnum::class,
            'deleted_at' => 'datetime',
        ]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_website');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_website');
    }
}
