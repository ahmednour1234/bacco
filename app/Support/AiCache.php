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

    /** @var array<string, true> Messages already logged this process. */
    private static array $warned = [];

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

            return self::$store = self::fallback();
        }

        self::warn('AiCache: the [ai] store is not defined; using the default store. Run `php artisan config:clear`.');

        return self::$store = self::fallback();
    }

    /**
     * The default store, or an in-memory one if even that fails.
     *
     * Cache::store() throws on a bad default driver too — an unreachable Redis,
     * a missing cache table — so falling back to it is not by itself safe. An
     * array store loses nothing that matters: every AI result is also written to
     * boq_parse_results / boq_answer_results, and the cache only saves a lookup.
     * A slow extraction beats an extraction that never runs.
     */
    private static function fallback(): Repository
    {
        try {
            $store = Cache::store();
            $store->get('__ai_cache_probe__');

            return $store;
        } catch (\Throwable $e) {
            self::warn('AiCache: the default store is unusable too (' . $e->getMessage() . '); caching in memory for this job only.');

            return Cache::driver('array');
        }
    }

    /**
     * Log once per distinct message, so a per-row cache read cannot flood the
     * log while a second, different failure still gets reported. Keying on the
     * message matters: the [ai] fallback always warns first, and a single flag
     * would have hidden the more serious "default store is unusable too".
     */
    private static function warn(string $message): void
    {
        $key = preg_replace('/\(.*\)/', '', $message);

        if (isset(self::$warned[$key])) {
            return;
        }

        self::$warned[$key] = true;
        Log::warning($message);
    }

    /** Clears the memoised store. Only needed in tests that swap config. */
    public static function flushResolved(): void
    {
        self::$store  = null;
        self::$warned = [];
    }
}
