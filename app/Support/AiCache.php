<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * The cache store AI results live in.
 *
 * They are kept out of the default store because `cache:clear` — which
 * `optimize:clear` calls on every deploy — would otherwise wipe extractions,
 * prices and questions that were paid for.
 *
 * Resolved through here rather than Cache::store('ai') directly so a missing
 * store definition degrades instead of failing. A server still serving a cached
 * config from before the store existed threw "Cache store [ai] is not defined"
 * and killed every extraction job outright; falling back to the default store
 * keeps the work running while the config catches up.
 */
class AiCache
{
    private static ?Repository $store = null;

    private static bool $warned = false;

    public static function store(): Repository
    {
        if (self::$store !== null) {
            return self::$store;
        }

        // config() is read rather than trusting the store to exist, because
        // Cache::store() on an undefined name throws rather than returning null.
        if (config('cache.stores.ai') !== null) {
            return self::$store = Cache::store('ai');
        }

        if (! self::$warned) {
            self::$warned = true;

            Log::warning('AiCache: the [ai] store is not defined; using the default store. Run `php artisan config:clear`.');
        }

        return self::$store = Cache::store();
    }

    /** Clears the memoised store. Only needed in tests that swap config. */
    public static function flushResolved(): void
    {
        self::$store  = null;
        self::$warned = false;
    }
}
