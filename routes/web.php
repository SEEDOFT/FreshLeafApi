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

Route::get('/livewire-debug', function (\Illuminate\Http\Request $request) {
    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'livewire.upload-file',
        now()->addMinutes(30)
    );

    // Simulate validation
    $validateRequest = \Illuminate\Http\Request::create($url, 'POST');
    $validateUrl = $validateRequest->url();
    $validateOriginal = rtrim($validateUrl.'?'.\Illuminate\Support\Arr::query(
        \Illuminate\Support\Arr::except($validateRequest->query(), ['signature'])
    ), '?');
    $expectedSignature = hash_hmac('sha256', $validateOriginal, config('app.key'));

    return [
        'generated_url' => $url,
        'validate_url_used' => $validateUrl,
        'validate_original_string_used' => $validateOriginal,
        'validate_expected_signature' => $expectedSignature,
        'validate_actual_signature' => $validateRequest->query('signature'),
        'match' => hash_equals($expectedSignature, (string) $validateRequest->query('signature', '')),
        
        'request_url' => $request->url(),
        'request_query' => $request->query(),
        'request_scheme' => $request->getScheme(),
        'request_host' => $request->getHost(),
    ];
});
