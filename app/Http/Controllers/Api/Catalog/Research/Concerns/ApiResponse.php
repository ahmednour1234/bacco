<?php

namespace App\Http\Controllers\Api\Catalog\Research\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Consistent JSON envelope for the catalog research API:
 *   { "success": bool, "data": mixed, "message": ?string, "errors": ?array }
 */
trait ApiResponse
{
    protected function ok(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    protected function fail(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
