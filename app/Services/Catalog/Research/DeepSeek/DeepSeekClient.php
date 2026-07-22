<?php

namespace App\Services\Catalog\Research\DeepSeek;

use App\Models\Catalog\Research\AiProviderLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Low-level DeepSeek chat client. Concerns handled here (so the provider stays
 * simple): retry with backoff, a token-bucket rate limiter, a circuit breaker,
 * and logging every call to ai_provider_logs WITHOUT secrets.
 *
 * The API is OpenAI-compatible, so swapping to another such endpoint is only a
 * config change (base_url + key), which keeps the module provider-agnostic.
 */
class DeepSeekClient
{
    private const CIRCUIT_KEY   = 'catalog-research:deepseek:circuit';
    private const RATE_KEY      = 'catalog-research:deepseek:rate';
    private const CIRCUIT_TRIPS = 5;      // consecutive failures before opening
    private const CIRCUIT_COOLDOWN = 120; // seconds the circuit stays open

    /**
     * Send a chat completion and return [content, usage].
     *
     * @return array{content:string, usage:array<string,mixed>}
     *
     * @throws RuntimeException on circuit-open, rate-exhaustion or repeated failure
     */
    public function chat(string $systemPrompt, string $userPrompt): array
    {
        $this->guardCircuit();
        $this->guardRateLimit();

        $key     = (string) config('services.deepseek.key');
        $baseUrl = rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com'), '/');
        $model   = (string) config('services.deepseek.model', 'deepseek-chat');
        $timeout = (int) config('services.deepseek.timeout', 120);
        $retries = (int) config('services.deepseek.max_retries', 3);

        if ($key === '') {
            throw new RuntimeException('DeepSeek API key is not configured.');
        }

        $payload = [
            'model'    => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0,
            'stream'          => false,
        ];

        $endpoint = $baseUrl . '/chat/completions';
        $started  = microtime(true);
        $attempt  = 0;
        $lastError = null;

        while ($attempt < max(1, $retries)) {
            $attempt++;
            try {
                $response = Http::timeout($timeout)
                    ->retry(1, 0) // outer loop owns backoff; keep inner simple
                    ->withHeaders(['Authorization' => 'Bearer ' . $key])
                    ->post($endpoint, $payload);

                $durationMs = (int) round((microtime(true) - $started) * 1000);

                if ($response->successful()) {
                    $json    = $response->json();
                    $content = (string) ($json['choices'][0]['message']['content'] ?? '');
                    $usage   = (array) ($json['usage'] ?? []);

                    $this->log($model, $endpoint, $payload, $response->status(), $usage, $durationMs, null);
                    $this->recordSuccess();

                    return ['content' => $content, 'usage' => $usage];
                }

                $lastError = "HTTP {$response->status()}";
                // 4xx (except 429) are not retryable.
                if ($response->status() < 500 && $response->status() !== 429) {
                    $this->log($model, $endpoint, $payload, $response->status(), [], $durationMs, $lastError);
                    $this->recordFailure();
                    throw new RuntimeException("DeepSeek request failed: {$lastError}");
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastError = 'connection: ' . $e->getMessage();
            }

            // Exponential backoff before the next attempt.
            if ($attempt < $retries) {
                usleep((int) (250_000 * (2 ** ($attempt - 1))));
            }
        }

        $this->log($model, $endpoint, $payload, 0, [], (int) round((microtime(true) - $started) * 1000), $lastError);
        $this->recordFailure();

        throw new RuntimeException('DeepSeek request failed after retries: ' . ($lastError ?? 'unknown'));
    }

    // ── Circuit breaker ──────────────────────────────────────────────────────

    private function guardCircuit(): void
    {
        if (Cache::get(self::CIRCUIT_KEY . ':open')) {
            throw new RuntimeException('DeepSeek circuit is open — cooling down.');
        }
    }

    private function recordFailure(): void
    {
        $fails = (int) Cache::increment(self::CIRCUIT_KEY . ':fails');
        Cache::put(self::CIRCUIT_KEY . ':fails', $fails, now()->addMinutes(5));

        if ($fails >= self::CIRCUIT_TRIPS) {
            Cache::put(self::CIRCUIT_KEY . ':open', true, now()->addSeconds(self::CIRCUIT_COOLDOWN));
            Cache::forget(self::CIRCUIT_KEY . ':fails');
            Log::warning('DeepSeek circuit opened after repeated failures.');
        }
    }

    private function recordSuccess(): void
    {
        Cache::forget(self::CIRCUIT_KEY . ':fails');
    }

    // ── Rate limiter (simple per-minute bucket) ──────────────────────────────

    private function guardRateLimit(): void
    {
        $limit = (int) config('services.deepseek.rate_limit', 30);
        if ($limit <= 0) {
            return;
        }

        $bucket = self::RATE_KEY . ':' . now()->format('YmdHi');
        $count  = (int) Cache::increment($bucket);
        if ($count === 1) {
            Cache::put($bucket, 1, now()->addMinutes(2));
        }

        if ($count > $limit) {
            throw new RuntimeException('DeepSeek rate limit reached for this minute.');
        }
    }

    // ── Logging (secrets scrubbed) ───────────────────────────────────────────

    private function log(string $model, string $endpoint, array $payload, int $status, array $usage, int $durationMs, ?string $error): void
    {
        try {
            AiProviderLog::create([
                'provider'          => 'deepseek',
                'model'             => $model,
                'endpoint'          => $endpoint,
                'request_payload'   => $this->scrub($payload),
                'response_status'   => $status ?: null,
                'response_payload'  => null, // full body may be large; usage captured below
                'prompt_tokens'     => $usage['prompt_tokens'] ?? null,
                'completion_tokens' => $usage['completion_tokens'] ?? null,
                'total_tokens'      => $usage['total_tokens'] ?? null,
                'duration_ms'       => $durationMs,
                'error_message'     => $error,
                'created_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the request path.
            Log::warning('Failed to persist ai_provider_log', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Remove anything sensitive before persisting. We never store the API key or
     * Authorization header (they are not in $payload anyway), and we truncate the
     * prompts so the log stays small.
     *
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    private function scrub(array $payload): array
    {
        unset($payload['api_key'], $payload['authorization']);

        if (isset($payload['messages']) && is_array($payload['messages'])) {
            foreach ($payload['messages'] as &$m) {
                if (isset($m['content'])) {
                    $m['content'] = mb_strimwidth((string) $m['content'], 0, 500, '…');
                }
            }
            unset($m);
        }

        return $payload;
    }
}
