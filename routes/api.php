<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPaymentMethodController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(static function () {
    Route::prefix('auth')->group(static function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware(['auth:sanctum', 'active.type:consumer'])->group(static function () {
            Route::post('/password/verify', [AuthController::class, 'verifyPassword']);
            Route::post('/password/update', [AuthController::class, 'updatePassword']);
            Route::post('/logout', [AuthController::class, 'logout']);

            Route::post('/pin/set', [PinController::class, 'setPin']);
            Route::post('/pin/update', [PinController::class, 'updatePin']);
            Route::post('/pin/reset', [PinController::class, 'resetPin']);
            Route::post('/pin/verify', [PinController::class, 'verifyPin']);
        });
    });

    Route::prefix('vendor/auth')->group(static function () {
        Route::post('/register', [VendorController::class, 'register']);
        Route::post('/login', [VendorController::class, 'login']);
    });

    Route::prefix('admin/auth')->group(static function () {
        Route::post('/login', [AdminController::class, 'login']);
    });

    Route::prefix('/users')->middleware(['auth:sanctum', 'active.type:consumer'])
        ->group(static function () {
            Route::get('consumer-profile', [UserController::class, 'consumerProfile']);
            Route::put('consumer-profile', [UserController::class, 'updateConsumerProfile']);

            Route::apiResource('addresses', UserAddressController::class);
            Route::apiResource('payment-methods', UserPaymentMethodController::class);

            Route::post('payments/intent', [PaymentController::class, 'createPaymentIntent']);
            Route::post('payments/confirm', [PaymentController::class, 'confirmPayment']);
            Route::post('payments/refund', [PaymentController::class, 'refund']);
            Route::get('payments/status', [PaymentController::class, 'status']);

            Route::post('payments/paypal/order', [PaymentController::class, 'createPayPalOrder']);
            Route::post('payments/paypal/capture', [PaymentController::class, 'capturePayPalOrder']);
            Route::get('payments/paypal/status', [PaymentController::class, 'getPayPalOrderStatus']);
            Route::post('payments/paypal/refund', [PaymentController::class, 'refundPayPal']);

            Route::get('profile', [UserController::class, 'show'])->name('users.profile.show');
            Route::put('profile', [UserController::class, 'replace'])->name('users.profile.update');
            Route::patch('profile', [UserController::class, 'update'])->name('users.profile.update');
            Route::delete('profile', [UserController::class, 'destroy'])->name('users.profile.delete');
        });

    Route::controller(AiChatController::class)->prefix('ai/chat')
        ->middleware(['auth:sanctum', 'active.type:consumer'])->group(static function () {
            Route::post('/sessions', 'createSession')->name('ai.chat.sessions.create');
            Route::post('/messages', 'storeMessage')->name('ai.chat.messages.store');
            Route::post('/history', 'history')->name('ai.chat.history');
        });

    Route::apiResource('products', ProductController::class)
        ->only(['index', 'show'])
        ->names([
            'index' => 'products.index',
            'show' => 'products.show',
        ]);

    Route::post('broadcasting/auth', static function (Request $request) {
        return Broadcast::auth($request);
    })->middleware('auth:sanctum');

    Route::controller(VendorController::class)
        ->prefix('vendor')
        ->middleware(['auth:sanctum', 'active.type:vendor'])
        ->group(static function () {
            Route::get('/me', 'me')->name('vendor.me');
            Route::get('/overview', 'overview')->name('vendor.overview');
            Route::get('/profile', 'profile')->name('vendor.profile.show');
            Route::put('/profile', 'updateProfile')->name('vendor.profile.update');
        });

    Route::controller(AdminController::class)
        ->prefix('admin')
        ->middleware(['auth:sanctum', 'active.type:admin'])
        ->group(static function () {
            Route::get('/me', 'me')->name('admin.me');
            Route::get('/overview', 'overview')->name('admin.overview');
            Route::get('/profile', 'profile')->name('admin.profile.show');
            Route::put('/profile', 'updateProfile')->name('admin.profile.update');
            Route::get('/vendors/pending', 'pendingVendors')->name('admin.vendors.pending');
            Route::post('/vendors/{vendor}/approve', 'approveVendor')->name('admin.vendors.approve');

            Route::apiResource('products', ProductController::class);
        });

    Route::post('webhooks/stripe', [PaymentController::class, 'handleWebhook']);
    Route::post('webhooks/paypal', [PaymentController::class, 'handlePayPalWebhook']);
});
