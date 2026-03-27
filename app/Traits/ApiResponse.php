<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = [], ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => [
                'code' => (string) $code,
                'success' => true,
                'message' => $message,
            ],
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(?string $message = null, int $code = 400, mixed $data = []): JsonResponse
    {
        return response()->json([
            'status' => [
                'code' => (string) $code,
                'success' => false,
                'message' => $message,
            ],
            'data' => $data,
        ], $code);
    }
}
