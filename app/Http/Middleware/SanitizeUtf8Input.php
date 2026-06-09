<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strips invalid/malformed UTF-8 byte sequences from all incoming request input.
 *
 * Livewire serialises every public component property into its JSON snapshot on
 * each /update request. If a property ever holds a string with malformed UTF-8
 * (e.g. a value pasted from Word/PDF, or a mangled multibyte char), Laravel's
 * JsonResponse calls json_encode() which fails with:
 *   "Malformed UTF-8 characters, possibly incorrectly encoded".
 *
 * Sanitising at the request boundary guarantees no property can ever capture
 * invalid bytes, so the snapshot always encodes cleanly.
 */
class SanitizeUtf8Input
{
    public function handle(Request $request, Closure $next): Response
    {
        $clean = $this->cleanArray($request->all());
        $request->merge($clean);

        return $next($request);
    }

    /**
     * Recursively convert every string to valid UTF-8, dropping invalid bytes.
     */
    protected function cleanArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                $input[$key] = $this->cleanString($value);
            }
        }

        return $input;
    }

    protected function cleanString(string $value): string
    {
        // Already valid – leave untouched (fast path).
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Drop any byte sequence that isn't valid UTF-8.
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $clean !== false ? $clean : '';
    }
}
