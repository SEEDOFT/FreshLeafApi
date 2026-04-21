<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureActiveUserType;
use App\Http\Middleware\SetLocaleFromAcceptLanguage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->alias([
            'active.type' => EnsureActiveUserType::class,
        ]);

        $middleware->api(prepend: [
            SetLocaleFromAcceptLanguage::class,
        ]);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $buildExceptionResponse = static function (
            int $code,
            bool $success,
            string $message,
            mixed $data = []
        ): array {
            return [
                'status' => [
                    'code' => (string) $code,
                    'success' => $success,
                    'message' => $message,
                ],
                'data' => $data,
            ];
        };

        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => $request->expectsJson()
                || $request->is('api/*') || $request->is('broadcasting/*')
        );

        $exceptions->render(
            static function (
                ValidationException $exception,
                Request $request
            ) use ($buildExceptionResponse): ?JsonResponse {
                if (
                    ! $request->expectsJson()
                    && ! $request->is('api/*')
                    && ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = $exception->status;

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: $exception->getMessage(),
                        data: $exception->errors(),
                    ),
                    $code
                );
            });

        $exceptions->render(
            static function (
                AuthenticationException $exception,
                Request $request
            ) use ($buildExceptionResponse): ?JsonResponse {
                if (
                    ! $request->expectsJson()
                    && ! $request->is('api/*')
                    && ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = 401;

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: $exception->getMessage() ?: 'Unauthenticated.',
                        data: [],
                    ),
                    $code
                );
            });

        $exceptions->render(
            static function (
                AuthorizationException $exception,
                Request $request
            ) use ($buildExceptionResponse): ?JsonResponse {
                if (
                    ! $request->expectsJson()
                     && ! $request->is('api/*')
                     && ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = $exception->hasStatus()
                    ? (int) $exception->status()
                    : 403;

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: $exception->getMessage() ?: 'Forbidden.',
                        data: [],
                    ),
                    $code
                );
            });

        $exceptions->render(
            static function (
                ModelNotFoundException $exception,
                Request $request
            ) use ($buildExceptionResponse) {
                if (
                    ! $request->expectsJson() &&
                    ! $request->is('api/*') &&
                    ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = 404;

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: 'Resource not found.',
                        data: [],
                    ),
                    $code
                );
            });

        $exceptions->render(
            static function (
                HttpExceptionInterface $exception,
                Request $request
            ) use ($buildExceptionResponse) {
                if (
                    ! $request->expectsJson() &&
                    ! $request->is('api/*') &&
                    ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = $exception->getStatusCode();

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: $exception->getMessage()
                            ?: (SymfonyResponse::$statusTexts[$code]
                                ?? 'HTTP error'),
                        data: [],
                    ),
                    $code
                );
            });

        $exceptions->render(
            static function (
                Throwable $exception,
                Request $request
            ) use ($buildExceptionResponse): ?JsonResponse {
                if (
                    ! $request->expectsJson()
                    && ! $request->is('api/*')
                    && ! $request->is('broadcasting/*')
                ) {
                    return null;
                }

                $code = 500;
                $message = config('app.debug') && $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Something went wrong.';

                return response()->json(
                    $buildExceptionResponse(
                        code: $code,
                        success: false,
                        message: $message,
                        data: [],
                    ),
                    $code
                );
            });
    })->create();
