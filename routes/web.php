<?php

use App\Http\Controllers\Web\AdminPanelController;
use App\Http\Controllers\Web\PanelAuthController;
use App\Http\Controllers\Web\PanelPreferenceController;
use App\Http\Controllers\Web\VendorPanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [PanelAuthController::class, 'create'])->name('login');
    Route::post('/login', [PanelAuthController::class, 'store'])->name('login.store');
    Route::get('/register', [PanelAuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [PanelAuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [PanelAuthController::class, 'destroy'])->name('logout');
    Route::post('/preferences/locale', [PanelPreferenceController::class, 'updateLocale'])->name('preferences.locale');
    Route::post('/preferences/theme', [PanelPreferenceController::class, 'updateTheme'])->name('preferences.theme');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->as('admin.')
    ->group(static function (): void {
        Route::get('/', [AdminPanelController::class, 'show'])->name('dashboard');
        Route::get('/vendors/pending', [AdminPanelController::class, 'pendingVendors'])->name('web.vendors.pending');
        Route::get('/vendors/pending/{vendor}', [AdminPanelController::class, 'showPendingVendor'])->name('web.vendors.pending.show');
        Route::post('/vendors/pending/{vendor}/approve', [AdminPanelController::class, 'approvePendingVendor'])->name('web.vendors.pending.approve');
        Route::post('/vendors/pending/{vendor}/reject', [AdminPanelController::class, 'rejectPendingVendor'])->name('web.vendors.pending.reject');
        Route::get('/{module}', [AdminPanelController::class, 'show'])
            ->where('module', 'dashboard|vendors|catalog|orders|payments|users|help-center|settings')
            ->name('module');
    });

Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->as('vendor.')
    ->group(static function (): void {
        Route::get('/', [VendorPanelController::class, 'show'])->name('dashboard');
        Route::get('/{module}', [VendorPanelController::class, 'show'])
            ->where('module', 'dashboard|products|orders|payments|store-profile|notifications|help')
            ->name('module');
    });
