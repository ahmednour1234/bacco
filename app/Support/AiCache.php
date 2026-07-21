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

        // Everything here is belt-and-braces on purpose: this store is read on
        // the first line of every extraction, so anything it throws takes the
        // whole job down before a single row is parsed. It must degrade, never
        // fail.
        try {
            // config() is read rather than trusting the store to exist, because
            // Cache::store() on an undefined name throws rather than returning
            // null. A server still serving a config cached from before the store
            // was added lands here.
            if (config('cache.stores.ai') !== null) {
                $store = Cache::store('ai');

                // Touch it once. The config can be present while the backing
                // table is not — a deploy that pulled the config but skipped the
                // migration — and that failure would otherwise surface mid-job.
                $store->get('__ai_cache_probe__');

                return self::$store = $store;
            }
        } catch (\Throwable $e) {
            self::warn('AiCache: the [ai] store is unusable (' . $e->getMessage() . '); using the default store.');

            return self::$store = Cache::store();
        }

        self::warn('AiCache: the [ai] store is not defined; using the default store. Run `php artisan config:clear`.');

        return self::$store = Cache::store();
    }

    /** Log once per process, so a per-row cache read cannot flood the log. */
    private static function warn(string $message): void
    {
        if (self::$warned) {
            return;
        }

        self::$warned = true;
        Log::warning($message);
    }

    /** Clears the memoised store. Only needed in tests that swap config. */
    public static function flushResolved(): void
    {
        self::$store  = null;
        self::$warned = false;
    }
}
