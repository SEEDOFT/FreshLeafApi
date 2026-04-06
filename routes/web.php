<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return view('welcome');
});

Route::post('/broadcasting/auth', static function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum')->withoutMiddleware(ValidateCsrfToken::class);
