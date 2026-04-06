<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(
    static function () {
        Route::prefix('auth')->group(
            static function () {
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/login', [AuthController::class, 'login']);

                Route::middleware('auth:sanctum')->group(
                    static function () {
                        Route::post('/password/verify', [AuthController::class, 'verifyPassword']);
                        Route::post('/password/update', [AuthController::class, 'updatePassword']);
                        Route::post('/logout', [AuthController::class, 'logout']);

                        Route::post('/pin/set', [PinController::class, 'setPin']);
                        Route::post('/pin/update', [PinController::class, 'updatePin']);
                        Route::post('/pin/reset', [PinController::class, 'resetPin']);
                        Route::post('/pin/verify', [PinController::class, 'verifyPin']);
                    }
                );
            });

        Route::prefix('/users')->middleware('auth:sanctum')->group(
            static function () {
                Route::put('addresses/{address}', [AddressController::class, 'replace']);
                Route::apiResource('addresses', AddressController::class);

                Route::patch('payment-methods/{payment_method}', [PaymentMethodController::class, 'update']);
                Route::put('payment-methods/{payment_method}', [PaymentMethodController::class, 'replace']);
                Route::apiResource('payment-methods', PaymentMethodController::class);

                Route::post('payments/intent', [PaymentController::class, 'createPaymentIntent']);
                Route::post('payments/confirm', [PaymentController::class, 'confirmPayment']);
                Route::post('payments/refund', [PaymentController::class, 'refund']);
                Route::get('payments/status', [PaymentController::class, 'status']);

                Route::post('payments/paypal/order', [PaymentController::class, 'createPayPalOrder']);
                Route::post('payments/paypal/capture', [PaymentController::class, 'capturePayPalOrder']);
                Route::get('payments/paypal/status', [PaymentController::class, 'getPayPalOrderStatus']);
                Route::post('payments/paypal/refund', [PaymentController::class, 'refundPayPal']);

                Route::get('/profile', [UserController::class, 'show']);
                Route::patch('/profile', [UserController::class, 'update']);
                Route::put('/profile', [UserController::class, 'replace']);
                Route::delete('/profile', [UserController::class, 'destroy']);
            }
        );

        Route::controller(AiChatController::class)->prefix('ai/chat')->middleware('auth:sanctum')->group(
            static function () {
                Route::post('/sessions', 'createSession')->name('ai.chat.sessions.create');
                Route::post('/messages', 'storeMessage')->name('ai.chat.messages.store');
                Route::post('/history', 'history')->name('ai.chat.history');
            }
        );

        Route::post('webhooks/stripe', [PaymentController::class, 'handleWebhook']);
        Route::post('webhooks/paypal', [PaymentController::class, 'handlePayPalWebhook']);
    }
);
