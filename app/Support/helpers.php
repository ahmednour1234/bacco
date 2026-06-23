<?php

// Global helper functions. Loaded via require_once from AppServiceProvider so
// they are available everywhere without a composer "files" autoload entry
// (no `composer dump-autoload` required on deploy).

if (! function_exists('catalog_value_t')) {
    /**
     * Translate a stored catalog data value (division / category / item
     * family) to the current locale via the `catalog.<group>` map.
     *
     * Falls back to the original (English) value when no translation key
     * exists, so newly imported / untranslated values still display.
     *
     *   catalog_value_t('divisions', $product->division)
     *
     * @param  string       $group  e.g. 'divisions', 'categories', 'items'
     * @param  string|null  $value  the stored value to translate
     */
    function catalog_value_t(string $group, ?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $key = "catalog.{$group}.{$value}";

        return \Illuminate\Support\Facades\Lang::has($key) ? __($key) : $value;
    }
}
