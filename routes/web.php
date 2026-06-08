<?php

declare(strict_types=1);

use App\Http\Controllers\Web\LandingPageController;
use App\Http\Controllers\Web\VerificationDocumentController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', LandingPageController::class)->name('home');

Route::get('/admin/documents/{path}', [VerificationDocumentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->where('path', '.*')
    ->name('admin.documents.show');

Route::get('/private-storage/{path}', static function (string $path) {
    if (str_contains($path, '..')) {
        abort(404);
    }

    if (! Storage::disk('local')->exists($path)) {
        abort(404);
    }

    return Storage::disk('local')->response($path);
})
    ->middleware(['auth', 'signed'])
    ->where('path', '.*')
    ->name('private.storage');

Route::match(['GET', 'POST'], 'broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('auth:sanctum')
    ->name('broadcasting.auth');
