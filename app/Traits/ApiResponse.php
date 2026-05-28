<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

use function response;

/**
 * Standardized API response trait.
 */
trait ApiResponse
{
    /**
     * Return a success response.
     */
    protected static function successResponse(
        mixed $data = [],
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        if ($data instanceof ResourceCollection) {
            $data = $data->response()->getData(true);
        }

        return response()->json(
            [
                'status' => [
                    'code' => (string) $code,
                    'success' => true,
                    'message' => $message,
                ],
                'data' => $data,
            ],
            $code
        );
    }

    /**
     * Return an error response.
     */
    protected static function errorResponse(
        string $message = 'Error',
        int $code = 400,
        mixed $data = []
    ): JsonResponse {
        return response()->json(
            [
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $message,
                ],
                'data' => $data,
            ],
            $code
        );
    }

    /**
     * Return an unauthorized response.
     */
    protected static function unauthorizedResponse(
        string $message = 'Unauthorized',
        mixed $data = []
    ): JsonResponse {
        return static::errorResponse($message, 401, $data);
    }

    /**
     * Return a not found response.
     */
    protected static function notFoundResponse(
        string $message = 'Resource not found',
        mixed $data = []
    ): JsonResponse {
        return static::errorResponse($message, 404, $data);
    }

    /**
     * Return a success response (Non-static version).
     */
    protected function success(
        mixed $data = [],
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return static::successResponse($data, $message, $code);
    }

    /**
     * Return an error response (Non-static version).
     */
    protected function error(
        string $message = 'Error',
        int $code = 400,
        mixed $data = []
    ): JsonResponse {
        return static::errorResponse($message, $code, $data);
    }

    /**
     * Return an unauthorized response (Non-static version).
     */
    protected function unauthorized(
        string $message = 'Unauthorized',
        mixed $data = []
    ): JsonResponse {
        return static::unauthorizedResponse($message, $data);
    }

    /**
     * Return a not found response (Non-static version).
     */
    protected function notFound(
        string $message = 'Resource not found',
        mixed $data = []
    ): JsonResponse {
        return static::notFoundResponse($message, $data);
    }
}
