<?php

declare(strict_types=1);

use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\Web\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');

// Custom broadcasting auth - uses our controller, not Laravel's default
Route::match(['GET', 'POST'], 'broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('auth:sanctum')
    ->name('broadcasting.auth');
