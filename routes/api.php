<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\PendingVendorController as AdminPendingVendorController;
use App\Http\Controllers\Api\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Api\User\PinController;
use App\Http\Controllers\Api\User\UserAddressController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\UserPaymentMethodController;
use App\Http\Controllers\Api\Vendor\AuthController as VendorAuthController;
use App\Http\Controllers\Api\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Api\Vendor\ProfileController as VendorProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(static function () {
    Route::prefix('auth')->group(static function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(static function () {
                Route::post('/password/verify', [AuthController::class, 'verifyPassword']);
                Route::post('/password/update', [AuthController::class, 'updatePassword']);
                Route::post('/logout', [AuthController::class, 'logout']);

                Route::post('/pin/set', [PinController::class, 'setPin']);
                Route::post('/pin/update', [PinController::class, 'updatePin']);
                Route::post('/pin/reset', [PinController::class, 'resetPin']);
                Route::post('/pin/verify', [PinController::class, 'verifyPin']);
            });
    });

    Route::prefix('/users')->middleware(['auth:sanctum', 'active.type:consumer'])
        ->group(static function () {
            Route::get('addresses', [UserAddressController::class, 'index'])->name('users.addresses.index');
            Route::get('addresses/{address}', [UserAddressController::class, 'show'])->name('users.addresses.show');
            Route::post('addresses', [UserAddressController::class, 'store'])->name('users.addresses.store');
            Route::put('addresses/{address}', [UserAddressController::class, 'replace'])->name('users.addresses.update');
            Route::patch('addresses/{address}', [UserAddressController::class, 'update'])->name('users.addresses.update');
            Route::delete('addresses/{address}', [UserAddressController::class, 'destroy'])->name('users.addresses.delete');

            Route::get('payment-methods', [UserPaymentMethodController::class, 'index'])->name('users.payment-methods.index');
            Route::post('payment-methods', [UserPaymentMethodController::class, 'store'])->name('users.payment-methods.store');
            Route::put('payment-methods/{paymentMethod}', [UserPaymentMethodController::class, 'replace'])->name('users.payment-methods.update');
            Route::patch('payment-methods/{paymentMethod}', [UserPaymentMethodController::class, 'update'])->name('users.payment-methods.update');
            Route::delete('payment-methods/{paymentMethod}', [UserPaymentMethodController::class, 'destroy'])->name('users.payment-methods.delete');

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
        ->middleware(['auth:sanctum', 'active.type:consumer'])
        ->group(static function () {
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

    Route::post('webhooks/stripe', [PaymentController::class, 'handleWebhook']);
    Route::post('webhooks/paypal', [PaymentController::class, 'handlePayPalWebhook']);

    Route::prefix('admin')->group(static function () {
        Route::prefix('auth')->group(static function () {
            Route::post('login', [AdminAuthController::class, 'login']);
        });

        Route::middleware(['auth:sanctum', 'role:admin', 'active.status:admin'])->group(static function () {
            Route::post('auth/logout', [AdminAuthController::class, 'logout']);
            Route::get('dashboard', [AdminDashboardController::class, 'index']);
            Route::get('dashboard/{module}', [AdminDashboardController::class, 'show'])
                ->where('module', 'dashboard|vendors|catalog|orders|payments|users|help-center|settings');
            Route::get('profile', [AdminProfileController::class, 'show']);
            Route::patch('profile', [AdminProfileController::class, 'update']);
            Route::get('preferences', [PreferenceController::class, 'show']);
            Route::patch('preferences', [PreferenceController::class, 'update']);
        });

        Route::middleware(['auth:sanctum', 'role:super_admin', 'active.status:admin'])->group(static function () {
            Route::get('vendors/pending', [AdminPendingVendorController::class, 'index']);
            Route::get('vendors/pending/{vendor}', [AdminPendingVendorController::class, 'show']);
            Route::patch('vendors/{vendor}', [AdminPendingVendorController::class, 'update']);
        });
    });

    Route::prefix('vendor')->group(static function () {
        Route::prefix('auth')->group(static function () {
            Route::post('register', [VendorAuthController::class, 'register']);
            Route::post('login', [VendorAuthController::class, 'login']);
        });

        Route::middleware(['auth:sanctum', 'role:vendor', 'active.status:vendor'])->group(static function () {
            Route::post('auth/logout', [VendorAuthController::class, 'logout']);
            Route::get('dashboard', [VendorDashboardController::class, 'index']);
            Route::get('products', [VendorDashboardController::class, 'products']);
            Route::get('orders', [VendorDashboardController::class, 'orders']);
            Route::get('payments', [VendorDashboardController::class, 'payments']);
            Route::get('store-profile', [VendorDashboardController::class, 'storeProfile']);
            Route::get('notifications', [VendorDashboardController::class, 'notifications']);
            Route::get('help', [VendorDashboardController::class, 'help']);
            Route::get('profile', [VendorProfileController::class, 'show']);
            Route::patch('profile', [VendorProfileController::class, 'update']);
            Route::get('preferences', [PreferenceController::class, 'show']);
            Route::patch('preferences', [PreferenceController::class, 'update']);
        });
    });
});
