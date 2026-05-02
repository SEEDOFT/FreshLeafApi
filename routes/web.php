<?php

declare(strict_types=1);

use App\Http\Controllers\Web\LandingPageController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');

Route::match(['GET', 'POST'], 'broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('auth:sanctum')
    ->name('broadcasting.auth');
