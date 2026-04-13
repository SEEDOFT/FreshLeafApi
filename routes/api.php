<?php

use App\Http\Controllers\Api\Admin as AdminController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\User;
use App\Http\Controllers\Api\Vendor as VendorController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {

        // Public Auth Routes (Rate limited)
        Route::controller(User\AuthController::class)
            ->prefix('auth')
            ->middleware('throttle:60,1')
            ->group(static function () {
                Route::post('register', 'register');
                Route::post('login', 'login');
            });

        // Protected User Auth Routes
        Route::controller(User\AuthController::class)
            ->prefix('auth')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::post('password/verify', 'verifyPassword');
                Route::post('password/update', 'updatePassword');
                Route::post('logout', 'logout');
            });

        // User PIN Routes
        Route::controller(User\UserPinController::class)->prefix('pin')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::post('set', 'setPin');
                Route::post('update', 'updatePin');
                Route::post('reset', 'resetPin');
                Route::post('verify', 'verifyPin');
            });

        // User Addresses Routes
        Route::controller(User\UserAddressController::class)->prefix('users/addresses')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::get('/', 'index')->name('users.addresses.index');
                Route::get('{address}', 'show')->name('users.addresses.show');
                Route::post('/', 'store')->name('users.addresses.store');
                Route::put('{address}', 'replace')->name('users.addresses.update');
                Route::patch('{address}', 'update')->name('users.addresses.update');
                Route::delete('{address}', 'destroy')->name('users.addresses.delete');
            });

        // User Payment Methods Routes
        Route::controller(User\UserPaymentMethodController::class)->prefix('users/payment-methods')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::get('/', 'index')->name('users.payment-methods.index');
                Route::post('/', 'store')->name('users.payment-methods.store');
                Route::put('{paymentMethod}', 'replace')->name('users.payment-methods.update');
                Route::patch('{paymentMethod}', 'update')->name('users.payment-methods.update');
                Route::delete('{paymentMethod}', 'destroy')->name('users.payment-methods.delete');
            });

        // User Payments Routes (Stripe)
        Route::controller(PaymentController::class)->prefix('users/payments')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::post('intent', 'createPaymentIntent');
                Route::post('confirm', 'confirmPayment');
                Route::post('refund', 'refund');
                Route::get('status', 'status');
                Route::post('paypal/order', 'createPayPalOrder');
                Route::post('paypal/capture', 'capturePayPalOrder');
                Route::get('paypal/status', 'getPayPalOrderStatus');
                Route::post('paypal/refund', 'refundPayPal');
            });

        // User Profile Routes
        Route::controller(User\UserController::class)->prefix('users/profile')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::get('/', 'show')->name('users.profile.show');
                Route::put('/', 'replace')->name('users.profile.update');
                Route::patch('/', 'update')->name('users.profile.update');
                Route::delete('/', 'destroy')->name('users.profile.delete');
            });

        // AI Chat Routes
        Route::controller(AiChatController::class)->prefix('ai/chat')
            ->middleware(['auth:sanctum', 'active.type:consumer'])
            ->group(function () {
                Route::post('sessions', 'createSession')->name('ai.chat.sessions.create');
                Route::post('messages', 'storeMessage')->name('ai.chat.messages.store');
                Route::post('history', 'history')->name('ai.chat.history');
            });

        // Product Routes (Public)
        Route::controller(ProductController::class)->prefix('products')
            ->group(function () {
                Route::get('/', 'index')->name('products.index');
                Route::get('{product}', 'show')->name('products.show');
            });

        // Broadcasting Auth
        Route::post('broadcasting/auth', function (Request $request) {
            return Broadcast::auth($request);
        })->middleware('auth:sanctum');

        // Webhooks (No rate limiting - must receive external requests)
        Route::post('webhooks/stripe', [PaymentController::class, 'handleWebhook']);
        Route::post('webhooks/paypal', [PaymentController::class, 'handlePayPalWebhook']);

        // Admin Auth Routes (Rate limited)
        Route::controller(AdminController\AuthController::class)->prefix('admin/auth')
            ->middleware('throttle:60,1')
            ->group(function () {
                Route::post('login', 'login');
            });

        // Admin Protected Routes
        Route::controller(AdminController\DashboardController::class)->prefix('admin')
            ->middleware(['auth:sanctum', 'role:admin', 'active.status:admin'])
            ->group(function () {
                Route::post('auth/logout', 'logout');
                Route::get('dashboard', 'index');
                Route::get('dashboard/{module}', 'show')->where('module', 'dashboard|vendors|catalog|orders|payments|users|help-center|settings');
            });

        Route::controller(AdminController\ProfileController::class)->prefix('admin/profile')
            ->middleware(['auth:sanctum', 'role:admin', 'active.status:admin'])
            ->group(function () {
                Route::get('/', 'show');
                Route::patch('/', 'update');
            });

        Route::controller(PreferenceController::class)->prefix('admin/preferences')
            ->middleware(['auth:sanctum', 'role:admin', 'active.status:admin'])
            ->group(function () {
                Route::get('/', 'show');
                Route::patch('/', 'update');
            });

        // Super Admin Only Routes
        Route::controller(AdminController\PendingVendorController::class)->prefix('admin/vendors')
            ->middleware(['auth:sanctum', 'role:super_admin', 'active.status:admin'])
            ->group(function () {
                Route::get('pending', 'index');
                Route::get('pending/{vendor}', 'show');
                Route::patch('{vendor}', 'update');
            });

        // Vendor Auth Routes (Rate limited)
        Route::controller(VendorController\AuthController::class)->prefix('vendor/auth')
            ->middleware('throttle:60,1')
            ->group(function () {
                Route::post('register', 'register');
                Route::post('login', 'login');
            });

        // Vendor Protected Routes
        Route::controller(VendorController\DashboardController::class)->prefix('vendor')
            ->middleware(['auth:sanctum', 'role:vendor', 'active.status:vendor'])
            ->group(function () {
                Route::post('auth/logout', 'logout');
                Route::get('dashboard', 'index');
                Route::get('products', 'products');
                Route::get('orders', 'orders');
                Route::get('payments', 'payments');
                Route::get('store-profile', 'storeProfile');
                Route::get('notifications', 'notifications');
                Route::get('help', 'help');
            });

        Route::controller(VendorController\ProfileController::class)->prefix('vendor/profile')
            ->middleware(['auth:sanctum', 'role:vendor', 'active.status:vendor'])
            ->group(function () {
                Route::get('/', 'show');
                Route::patch('/', 'update');
            });

        Route::controller(PreferenceController::class)->prefix('vendor/preferences')
            ->middleware(['auth:sanctum', 'role:vendor', 'active.status:vendor'])
            ->group(function () {
                Route::get('/', 'show');
                Route::patch('/', 'update');
            });

        // Fallback Route - 404
        Route::fallback(
            static fn (): JsonResponse => response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'errors' => [],
            ], 404)
        );
    });
