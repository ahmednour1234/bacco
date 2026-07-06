<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * SEO metadata for a public/landing page, keyed by route name.
 *
 * Fields are stored per-locale (*_en / *_ar). The locale-aware accessors
 * ($seo->title, ->meta_desc, ->keywords, ->schema) return the value for the
 * current app locale, falling back to the other locale when one is empty, so a
 * page never renders a blank <title> if only one language was filled in.
 *
 * @property string|null $title
 * @property string|null $meta_desc
 * @property string|null $keywords
 * @property string|null $schema
 */
class SeoMeta extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'route_name', 'label',
        'title_en', 'title_ar',
        'meta_desc_en', 'meta_desc_ar',
        'keywords_en', 'keywords_ar',
        'og_image', 'og_type',
        'schema_en', 'schema_ar',
        'active',
    ];

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'active' => 'boolean',
        ]);
    }

    /**
     * Return the value of a bilingual field for the current locale, falling
     * back to the other locale when the preferred one is empty. Live catalog
     * placeholders (:products, :brands, :categories) are substituted so titles
     * and descriptions always show current, formatted numbers.
     */
    protected function localized(string $base): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $other  = $locale === 'ar' ? 'en' : 'ar';

        $value = $this->getAttribute("{$base}_{$locale}");
        if (! filled($value)) {
            $value = $this->getAttribute("{$base}_{$other}") ?: null;
        }

        return $value === null ? null : $this->substitutePlaceholders($value);
    }

    /**
     * Replace :products / :brands / :categories with live, formatted catalog
     * counts shared with every view by AppServiceProvider. Falls back to the raw
     * token if the stats bag is unavailable.
     */
    protected function substitutePlaceholders(string $value): string
    {
        if (! str_contains($value, ':')) {
            return $value;
        }

        $stats = view()->shared('catalogStats', []);

        $map = [
            ':products'   => isset($stats['products'])   ? number_format((int) $stats['products'])   : ':products',
            ':brands'     => isset($stats['brands'])     ? number_format((int) $stats['brands'])      : ':brands',
            ':categories' => isset($stats['categories']) ? number_format((int) $stats['categories'])  : ':categories',
        ];

        return strtr($value, $map);
    }

    public function getTitleAttribute(): ?string
    {
        return $this->localized('title');
    }

    public function getMetaDescAttribute(): ?string
    {
        return $this->localized('meta_desc');
    }

    public function getKeywordsAttribute(): ?string
    {
        return $this->localized('keywords');
    }

    public function getSchemaAttribute(): ?string
    {
        // Schema is raw JSON-LD; do not run placeholder substitution on it.
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $other  = $locale === 'ar' ? 'en' : 'ar';
        $value  = $this->getAttribute("schema_{$locale}");

        return filled($value) ? $value : ($this->getAttribute("schema_{$other}") ?: null);
    }
}
