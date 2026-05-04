<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Ai\AiChatController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Shared as SharedController;
use App\Http\Controllers\Api\User as UserController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(static function () {
    // Public Routes
    Route::prefix('categories')
        ->name('categories.')
        ->controller(CategoryController::class)
        ->group(static function () {
            Route::get('/', 'index')->name('index');
            Route::get('{category:slug}', 'show')->name('show');
        });

    // Unified Auth (Login/Register remain type-specific for clarity, but Logout is shared)
    Route::prefix('auth')->name('auth.')->group(static function () {
        Route::post('login', [UserController\AuthController::class, 'login'])->name('login');
        Route::post('register', [UserController\AuthController::class, 'register'])->name('register');

        Route::middleware('auth:sanctum')->group(static function () {
            Route::post('logout', [SharedController\AuthController::class, 'logout'])->name('logout');
            Route::post('password/verify', [SharedController\AuthController::class, 'verifyPassword'])->name('password.verify');
            Route::post('password/update', [SharedController\AuthController::class, 'updatePassword'])->name('password.update');
        });
    });

    // Shared Authenticated Routes (No user-type prefix)
    Route::middleware(['auth:sanctum'])->group(static function () {
        // Shared Profile
        Route::prefix('profile')
            ->name('profile.')
            ->controller(SharedController\ProfileController::class)
            ->group(static function () {
                Route::get('/', 'show')->name('show');
                Route::put('/', 'replace')->name('replace');
                Route::patch('/', 'update')->name('update');
                Route::delete('/', 'destroy')->name('delete');
            });

        // Shared Wallets
        Route::prefix('wallets')
            ->name('wallets.')
            ->controller(SharedController\WalletController::class)
            ->group(static function () {
                Route::get('/', 'index')->name('index');
                Route::get('{id}', 'show')->name('show');
                Route::get('{id}/histories', 'history')->name('histories');
            });

        // Shared Addresses (User & Vendor)
        Route::prefix('addresses')
            ->name('addresses.')
            ->controller(SharedController\AddressController::class)
            ->group(static function () {
                Route::get('/', 'index')->name('index');
                Route::get('{id}', 'show')->name('show');
                Route::post('/', 'store')->name('store');
                Route::put('{id}', 'replace')->name('replace');
                Route::patch('{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('delete');
            });

        // Broadcasting Auth
        Route::match(['GET', 'POST'], 'broadcasting/auth', [BroadcastController::class, 'authenticate'])
            ->name('broadcasting.auth');
    });

    // User-Specific Routes
    Route::prefix('user')->name('user.')->middleware(['auth:sanctum', 'active.type:user'])
    ->group(static function () {
        Route::prefix('pin')
            ->name('pin.')
            ->controller(UserController\UserPinController::class)
            ->group(static function () {
                Route::post('set', 'setPin')->name('set');
                Route::post('update', 'updatePin')->name('update');
                Route::post('reset', 'resetPin')->name('reset');
                Route::post('verify', 'verifyPin')->name('verify');
            });

        Route::prefix('payment-methods')
            ->name('payment-methods.')
            ->controller(UserController\PaymentMethodController::class)
            ->group(static function () {
                Route::get('/', 'index')->name('index');
                Route::get('{id}', 'show')->name('show');
                Route::post('/', 'store')->name('store');
                Route::put('{id}', 'replace')->name('replace');
                Route::patch('{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('delete');
            });

        Route::prefix('payment-method-types')
            ->name('payment-method-types.')
            ->controller(UserController\PaymentMethodTypeController::class)
            ->group(static function () {
                Route::get('/', 'index')->name('index');
                Route::get('{id}', 'show')->name('show');
            });

        Route::prefix('devices')
            ->name('devices.')
            ->controller(UserController\DeviceController::class)
            ->group(static function () {
                Route::post('/', 'store')->name('store');
                Route::delete('{token}', 'destroy')->name('destroy');
            });

        Route::prefix('wallet-transactions')
            ->name('wallet-transactions.')
            ->controller(UserController\WalletTransactionController::class)
            ->group(static function () {
                Route::get('/', 'index')->name('index');
                Route::get('{id}', 'show')->name('show');
                Route::post('/', 'store')->name('store');
                Route::patch('{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('destroy');
            });

        Route::prefix('ai/chat')
            ->name('ai.chat.')
            ->controller(AiChatController::class)
            ->group(static function () {
                Route::post('sessions', 'createSession')->name('sessions.create');
                Route::post('messages', 'storeMessage')->name('messages.store');
                Route::post('history', 'history')->name('history');
            });

        Route::prefix('support')
            ->name('support.')
            ->controller(UserController\SupportChatController::class)
            ->group(static function () {
                Route::get('ticket', 'getActiveTicket')->name('ticket.active');
                Route::get('unread-count', 'getUnreadCount')->name('unread.count');
                Route::post('messages', 'sendMessage')->name('messages.store');
                Route::get('messages', 'getMessages')->name('messages.index');
                Route::post('typing', 'sendTyping')->name('typing');
            });
    });

    // User Module Routes
    Route::prefix('user')->name('user.')->middleware(['auth:sanctum'])->group(static function () {
        Route::controller(ProfileController::class)->group(static function () {
            Route::get('/', 'show')->name('profile.show');
            Route::patch('/', 'update')->name('profile.update');
            Route::put('/', 'replace')->name('profile.replace');
            Route::delete('/', 'destroy')->name('profile.destroy');
        });

        Route::controller(WalletController::class)->prefix('wallets')->group(static function () {
            Route::get('/', 'index')->name('wallets.index');
            Route::get('{id}', 'show')->name('wallets.show');
            Route::get('{id}/histories', 'history')->name('wallets.history');
        });

        Route::controller(WalletTransactionController::class)->prefix('wallet-transactions')->group(static function () {
            Route::get('/', 'index')->name('transactions.index');
            Route::get('{id}', 'show')->name('transactions.show');
            Route::post('/', 'store')->name('transactions.store');
            Route::patch('{id}', 'update')->name('transactions.update');
            Route::delete('{id}', 'destroy')->name('transactions.destroy');
        });

        Route::controller(AddressController::class)->prefix('addresses')->group(static function () {
            Route::get('/', 'index')->name('addresses.index');
            Route::get('{id}', 'show')->name('addresses.show');
            Route::post('/', 'store')->name('addresses.store');
            Route::patch('{id}', 'update')->name('addresses.update');
            Route::put('{id}', 'replace')->name('addresses.replace');
            Route::delete('{id}', 'destroy')->name('addresses.destroy');
        });

        Route::controller(SupportChatController::class)->prefix('support')->group(static function () {
            Route::get('ticket', 'getActiveTicket')->name('support.ticket');
            Route::post('typing', 'sendTyping')->name('support.typing');
            Route::post('message', 'sendMessage')->name('support.message');
            Route::get('messages', 'getMessages')->name('support.messages');
            Route::get('unread-count', 'getUnreadCount')->name('support.unread-count');
        });

        Route::controller(DeviceController::class)->prefix('devices')->group(static function () {
            Route::post('/', 'store')->name('devices.store');
            Route::delete('{token}', 'destroy')->name('devices.destroy');
        });
    });

    // Shared Product Routes (Serve data to Consumer app)
    Route::prefix('products')
        ->name('products.')
        ->middleware(['auth:sanctum'])
        ->controller(ProductController::class)
        ->group(static function () {
            Route::get('/', 'index')->name('index'); // Unified index
            Route::get('{id}', 'show')->name('show'); // Unified show
        });

    // Fallback Route - 404
    Route::fallback(static fn (): JsonResponse => response()->json([
        'status' => [
            'code' => '404',
            'success' => false,
            'message' => 'Endpoint not found',
        ],
        'data' => [],
    ], 404))->name('fallback');
});
