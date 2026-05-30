<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Article extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'slug', 'name_en', 'name_ar',
        'title_en', 'title_ar',
        'desc_en', 'desc_ar',
        'image', 'tag', 'sort_order', 'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $article) {
            if (empty($article->slug)) {
                $base = Str::slug($article->title_en);
                $slug = $base;
                $i    = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $article->slug = $slug;
            }
        });
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'active' => 'boolean',
        ]);
    }
}
