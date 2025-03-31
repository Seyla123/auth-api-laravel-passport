<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\v1\auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// ┌──────────────────────┐
// │ API Version  1       │
// └──────────────────────┘
Route::prefix('v1')->group(function () {
    // ┌──────────────────────┐
    // │ Authentication       │
    // └──────────────────────┘
    // route: /v1/auth/...
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::get('/me', [AuthController::class, 'currentUser'])->middleware('auth:api', 'verified');

        // verification
        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');

    });
});

