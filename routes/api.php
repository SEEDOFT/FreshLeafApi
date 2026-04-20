<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin as AdminController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\User as UserController;
use App\Http\Controllers\Api\Vendor as VendorController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(static function () {
    // User routes
    Route::prefix('user')
        ->name('user.')
        ->group(static function () {
            // User Auth Routes (Public)
            Route::prefix('auth')
                ->name('auth.')
                ->controller(UserController\AuthController::class)
                ->group(static function () {
                    Route::post('register', 'register')->name('register');
                    Route::post('login', 'login')->name('login');
                });

            // Authenticated user routes
            Route::middleware(['auth:sanctum', 'active.type:user'])
                ->group(static function () {
                    Route::prefix('auth')
                        ->name('auth.')
                        ->controller(UserController\AuthController::class)
                        ->group(static function () {
                            Route::post('password/verify', 'verifyPassword')
                                ->name('password.verify');
                            Route::post('password/update', 'updatePassword')
                                ->name('password.update');
                            Route::post('logout', 'logout')->name('logout');
                        });

                    Route::prefix('pin')
                        ->name('pin.')
                        ->controller(UserController\UserPinController::class)
                        ->group(static function () {
                            Route::post('set', 'setPin')->name('set');
                            Route::post('update', 'updatePin')->name('update');
                            Route::post('reset', 'resetPin')->name('reset');
                            Route::post('verify', 'verifyPin')->name('verify');
                        });

                    Route::prefix('profile')
                        ->name('profile.')
                        ->controller(UserController\ProfileController::class)
                        ->group(static function () {
                            Route::get('/', 'show')->name('show');
                            Route::put('/', 'replace')->name('replace');
                            Route::patch('/', 'update')->name('update');
                            Route::delete('/', 'destroy')->name('delete');
                        });

                    Route::prefix('products')
                        ->name('products.')
                        ->controller(ProductController::class)
                        ->group(static function () {
                            Route::get('/', 'userIndex')->name('index');
                            Route::get('{product}', 'userShow')->name('show');
                        });

                    Route::prefix('addresses')
                        ->name('addresses.')
                        ->controller(UserController\UserAddressController::class)
                        ->group(static function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('{address}', 'show')->name('show');
                            Route::post('/', 'store')->name('store');
                            Route::put('{address}', 'replace')->name('replace');
                            Route::patch('{address}', 'update')->name('update');
                            Route::delete('{address}', 'destroy')->name('delete');
                        });

                    Route::prefix('payment-methods')
                        ->name('payment-methods.')
                        ->controller(UserController\UserPaymentMethodController::class)
                        ->group(static function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'store')->name('store');
                            Route::put('{paymentMethod}', 'replace')->name('replace');
                            Route::patch('{paymentMethod}', 'update')->name('update');
                            Route::delete('{paymentMethod}', 'destroy')->name('delete');
                        });

                    Route::prefix('payments')
                        ->name('payments.')
                        ->controller(PaymentController::class)
                        ->group(static function () {
                            Route::post('intent', 'createPaymentIntent')->name('intent');
                            Route::post('confirm', 'confirmPayment')->name('confirm');
                            Route::post('refund', 'refund')->name('refund');
                            Route::get('status', 'status')->name('status');
                            Route::prefix('paypal')
                                ->name('paypal.')
                                ->group(static function () {
                                    Route::post('order', 'createPayPalOrder')->name('order');
                                    Route::post('capture', 'capturePayPalOrder')->name('capture');
                                    Route::get('status', 'getPayPalOrderStatus')->name('status');
                                    Route::post('refund', 'refundPayPal')->name('refund');
                                });
                        });

                    Route::prefix('ai/chat')
                        ->name('ai.chat.')
                        ->controller(AiChatController::class)
                        ->group(static function () {
                            Route::post('sessions', 'createSession')->name('sessions.create');
                            Route::post('messages', 'storeMessage')->name('messages.store');
                            Route::post('history', 'history')->name('history');
                        });
                });
        });

    // Webhooks (No rate limiting - must receive external requests)
    Route::prefix('webhooks')
        ->name('webhooks.')
        ->group(static function () {
            Route::post('stripe', [PaymentController::class, 'handleWebhook'])->name('stripe');
            Route::post('paypal', [PaymentController::class, 'handlePayPalWebhook'])->name('paypal');
        });

    // Authenticated (any sanctum) routes
    Route::post('broadcasting/auth', [Broadcast::class, 'auth'])
        ->middleware('auth:sanctum')
        ->name('broadcasting.auth');

    // Admin routes
    Route::prefix('admin')
        ->name('admin.')
        ->group(static function () {
            Route::prefix('auth')
                ->name('auth.')
                ->controller(AdminController\AuthController::class)
                ->middleware('throttle:60,1')
                ->group(static function () {
                    Route::post('login', 'login')->name('login');
                });

            Route::prefix('profile')
                ->name('profile.')
                ->controller(AdminController\ProfileController::class)
                ->middleware(['auth:sanctum', 'active.type:admin'])
                ->group(static function () {
                    Route::get('/', 'show')->name('show');
                    Route::patch('/', 'update')->name('update');
                });

            Route::prefix('products')
                ->name('products.')
                ->controller(ProductController::class)
                ->middleware(['auth:sanctum', 'active.type:admin'])
                ->group(static function () {
                    Route::get('/', 'adminIndex')->name('index');
                    Route::get('{product}', 'adminShow')->name('show');
                });
        });

    // Vendor routes
    Route::prefix('vendor')
        ->name('vendor.')
        ->group(static function () {
            Route::prefix('auth')
                ->name('auth.')
                ->controller(VendorController\AuthController::class)
                ->middleware('throttle:60,1')
                ->group(static function () {
                    Route::post('register', 'register')->name('register');
                    Route::post('login', 'login')->name('login');
                });

            Route::prefix('profile')
                ->name('profile.')
                ->controller(VendorController\ProfileController::class)
                ->middleware(['auth:sanctum', 'active.type:vendor'])
                ->group(static function () {
                    Route::get('/', 'show')->name('show');
                    Route::patch('/', 'update')->name('update');
                });

            Route::prefix('products')
                ->name('products.')
                ->controller(ProductController::class)
                ->middleware(['auth:sanctum', 'active.type:vendor'])
                ->group(static function () {
                    Route::get('/', 'vendorIndex')->name('index');
                    Route::get('{product}', 'vendorShow')->name('show');
                });

            Route::prefix('addresses')
                ->name('addresses.')
                ->controller(VendorController\VendorAddressController::class)
                ->middleware(['auth:sanctum', 'active.type:vendor'])
                ->group(static function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{address}', 'show')->name('show');
                    Route::post('/', 'store')->name('store');
                    Route::put('{address}', 'replace')->name('replace');
                    Route::patch('{address}', 'update')->name('update');
                    Route::delete('{address}', 'destroy')->name('delete');
                });
        });

    // Fallback Route - 404
    Route::fallback(
        static fn (): JsonResponse => response()->json([
            'success' => false,
            'message' => 'Endpoint not found',
            'errors' => [],
        ], 404)
    )->name('fallback');
});
