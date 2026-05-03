<?php

declare(strict_types=1);

use App\Http\Controllers\Web\LandingPageController;
use App\Http\Controllers\Web\VerificationDocumentController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');

Route::get('/admin/documents/{path}', [VerificationDocumentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->where('path', '.*')
    ->name('admin.documents.show');

Route::match(['GET', 'POST'], 'broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('auth:sanctum')
    ->name('broadcasting.auth');
