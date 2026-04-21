<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin as AdminController;
use App\Http\Controllers\Api\Ai\AiChatController;
use App\Http\Controllers\Api\Product\ProductController;
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
                            Route::get('{id}', 'userShow')->name('show');
                        });

                    Route::prefix('addresses')
                        ->name('addresses.')
                        ->controller(UserController\AddressController::class)
                        ->group(static function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('{id}', 'show')->name('show');
                            Route::post('/', 'store')->name('store');
                            Route::put('{id}', 'replace')->name('replace');
                            Route::patch('{id}', 'update')->name('update');
                            Route::delete('{id}', 'destroy')->name('delete');
                        });

                    Route::prefix('payment-methods')
                        ->name('payment-methods.')
                        ->controller(UserController\UserPaymentMethodController::class)
                        ->group(static function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'store')->name('store');
                            Route::put('{id}', 'replace')->name('replace');
                            Route::patch('{id}', 'update')->name('update');
                            Route::delete('{id}', 'destroy')->name('delete');
                        });

                    Route::prefix('wallets')
                        ->name('wallets.')
                        ->controller(UserController\WalletController::class)
                        ->group(static function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('{id}', 'show')->name('show');
                            Route::get('{id}/histories', 'history')->name('histories');
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
                    Route::post('register', 'register')->name('register');
                    Route::post('login', 'login')->name('login');
                });

            Route::prefix('auth')
                ->name('auth.')
                ->controller(AdminController\AuthController::class)
                ->middleware(['auth:sanctum', 'active.type:admin'])
                ->group(static function () {
                    Route::post('password/verify', 'verifyPassword')
                        ->name('password.verify');
                    Route::post('password/update', 'updatePassword')
                        ->name('password.update');
                    Route::post('logout', 'logout')->name('logout');
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
                    Route::get('{id}', 'adminShow')->name('show');
                });

            Route::prefix('vendors')
                ->name('vendors.')
                ->controller(AdminController\Vendor\VendorController::class)
                ->middleware(['auth:sanctum', 'active.type:admin'])
                ->group(static function () {
                    Route::get('pending', 'indexPendingVendorApproval')->name('pending.index');
                    Route::get('pending/{id}', 'showPendingVendorApproval')->name('pending.show');
                    Route::patch('pending/{id}', 'updatePendingVendorApproval')->name('update');
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
                    Route::get('{id}', 'vendorShow')->name('show');
                });

            Route::prefix('addresses')
                ->name('addresses.')
                ->controller(VendorController\VendorAddressController::class)
                ->middleware(['auth:sanctum', 'active.type:vendor'])
                ->group(static function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{id}', 'show')->name('show');
                    Route::post('/', 'store')->name('store');
                    Route::put('{id}', 'replace')->name('replace');
                    Route::patch('{id}', 'update')->name('update');
                    Route::delete('{id}', 'destroy')->name('delete');
                });

            Route::prefix('wallets')
                ->name('wallets.')
                ->controller(VendorController\WalletController::class)
                ->middleware(['auth:sanctum', 'active.type:vendor'])
                ->group(static function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{id}', 'show')->name('show');
                    Route::get('{id}/histories', 'history')->name('histories');
                });
        });

    // Fallback Route - 404
    Route::fallback(
        static fn (): JsonResponse => response()->json([
            'status' => [
                'code' => '404',
                'success' => false,
                'message' => 'Endpoint not found',
            ],
            'data' => [],
        ], 404)
    )->name('fallback');
});
