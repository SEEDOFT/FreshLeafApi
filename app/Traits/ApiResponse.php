<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

use function response;
use function trans;

/**
 * Standardized API response trait.
 */
trait ApiResponse
{
    /**
     * Translate a message key.
     */
    protected static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return trans("api.{$key}", $replace, $locale);
    }

    /**
     * Return a success response.
     */
    protected static function successResponse(
        mixed $data = [],
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
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
     * Return a translated success response.
     */
    protected static function successTranslated(
        mixed $data = [],
        string $key = 'general.success',
        array $replace = [],
        int $code = 200
    ): JsonResponse {
        return static::successResponse($data, static::trans($key, $replace), $code);
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
     * Return a translated error response.
     */
    protected static function errorTranslated(
        string $key = 'general.error',
        array $replace = [],
        int $code = 400,
        mixed $data = []
    ): JsonResponse {
        return static::errorResponse(static::trans($key, $replace), $code, $data);
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
     * Return a translated unauthorized response.
     */
    protected static function unauthorizedTranslated(
        string $key = 'general.unauthorized',
        array $replace = [],
        mixed $data = []
    ): JsonResponse {
        return static::errorTranslated($key, $replace, 401, $data);
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
     * Return a translated not found response.
     */
    protected static function notFoundTranslated(
        string $key = 'general.not_found',
        array $replace = [],
        mixed $data = []
    ): JsonResponse {
        return static::errorTranslated($key, $replace, 404, $data);
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
     * Return a translated success response (Non-static version).
     */
    protected function successTrans(
        mixed $data = [],
        string $key = 'general.success',
        array $replace = [],
        int $code = 200
    ): JsonResponse {
        return static::successTranslated($data, $key, $replace, $code);
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
     * Return a translated error response (Non-static version).
     */
    protected function errorTrans(
        string $key = 'general.error',
        array $replace = [],
        int $code = 400,
        mixed $data = []
    ): JsonResponse {
        return static::errorTranslated($key, $replace, $code, $data);
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
     * Return a translated unauthorized response (Non-static version).
     */
    protected function unauthorizedTrans(
        string $key = 'general.unauthorized',
        array $replace = [],
        mixed $data = []
    ): JsonResponse {
        return static::unauthorizedTranslated($key, $replace, $data);
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

    /**
     * Return a translated not found response (Non-static version).
     */
    protected function notFoundTrans(
        string $key = 'general.not_found',
        array $replace = [],
        mixed $data = []
    ): JsonResponse {
        return static::notFoundTranslated($key, $replace, $data);
    }
}
