<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProfileController;
use App\Modules\Logbook\LogbookController;

// Public Routes
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', function () {
        return auth()->user();
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/change-password', [ProfileController::class, 'changePassword']);

    // Logbooks
    Route::post('/logbooks', [LogbookController::class, 'store']);
    Route::get('/logbooks/my', [LogbookController::class, 'myLogbooks']);
    Route::get('/logbooks/pending', [LogbookController::class, 'pendingLogbooks']);
    Route::get('/logbooks/{id}', [LogbookController::class, 'show']);
    Route::post('/logbooks/{id}/review', [LogbookController::class, 'review']);
    Route::post('/logbooks/{id}/reject', [LogbookController::class, 'reject']);
});
