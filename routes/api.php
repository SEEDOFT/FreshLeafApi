<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(
    static function () {
        Route::prefix('auth')->group(
            static function () {
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/login', [AuthController::class, 'login']);
                Route::post('/logout', [AuthController::class, 'logout'])
                    ->middleware('auth:sanctum');
            });

        Route::prefix('/users')->middleware('auth:sanctum')->group(
            static function () {
                Route::apiResource('addresses', AddressController::class);
                Route::get('/profile', [UserController::class, 'show']);
                Route::get('/update-profile', [UserController::class, 'update']);
            }
        );
    }
);
