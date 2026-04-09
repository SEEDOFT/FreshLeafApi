<?php

use App\Http\Middleware\EnsureActiveUserType;
use App\Http\Middleware\EnsurePanelRole;
use App\Http\Middleware\SetLocaleFromAcceptLanguage;
use App\Http\Middleware\SetPanelPreferences;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.type' => EnsureActiveUserType::class,
            'role' => EnsurePanelRole::class,
        ]);

        $middleware->web(append: [
            SetPanelPreferences::class,
        ]);

        $middleware->api(prepend: [
            SetLocaleFromAcceptLanguage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return $request->expectsJson()
                || $request->is('api/*')
                || $request->is('broadcasting/*');
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = $exception->status;

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],
                'data' => [
                    'errors' => $exception->errors(),
                ],
            ], $code);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = 401;

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'Unauthenticated.',
                ],
                'data' => [],
            ], $code);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = 403;

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'Forbidden.',
                ],
                'data' => [],
            ], $code);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = 404;

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => 'Resource not found.',
                ],
                'data' => [],
            ], $code);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = $exception->getStatusCode();

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $exception->getMessage() ?: (SymfonyResponse::$statusTexts[$code] ?? 'HTTP error'),
                ],
                'data' => [],
            ], $code);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('broadcasting/*')) {
                return null;
            }

            $code = 500;
            $message = config('app.debug') && $exception->getMessage() !== ''
                ? $exception->getMessage()
                : 'Something went wrong.';

            return response()->json([
                'status' => [
                    'code' => (string) $code,
                    'success' => false,
                    'message' => $message,
                ],
                'data' => [],
            ], $code);
        });
    })->create();
