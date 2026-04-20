<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Base Response Structure
     */
    private static function baseResponse(
        string $code,
        bool $success,
        ?string $message = null,
        mixed $data = [],
    ) {
        return [
            'status' => [
                'code' => $code,
                'success' => $success,
                'message' => $message,
            ],
            'data' => $data,
        ];
    }

    /**
     * Return a success JSON response.
     */
    protected function successResponse(
        mixed $data = [],
        ?string $message = null,
        int $code = 200
    ): JsonResponse {
        return \response()->json(
            self::baseResponse(
                code: (string) $code,
                success: true,
                message: $message,
                data: $data
            ), $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(
        ?string $message = null,
        int $code = 400,
        mixed $data = []
    ): JsonResponse {
        return \response()->json(
            self::baseResponse(
                code: (string) $code,
                success: false,
                message: $message,
                data: $data
            ), $code);
    }

    /**
     * Return a forbidden JSON response (403).
     */
    protected static function forbidden(
        ?string $message = null,
        int $code = 403,
        mixed $data = []
    ): JsonResponse {
        return \response()->json(
            self::baseResponse(
                code: (string) $code,
                success: false,
                message: $message,
                data: $data
            ), $code);
    }

    /**
     * Return an unauthorized JSON response (401).
     */
    protected static function unauthorized(
        ?string $message = null,
        int $code = 401,
        mixed $data = []
    ): JsonResponse {
        return \response()->json(
            self::baseResponse(
                code: (string) $code,
                success: false,
                message: $message,
                data: $data
            ), $code);
    }
}
