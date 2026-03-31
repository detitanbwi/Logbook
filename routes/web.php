<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;

// Redirect root
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Resource
    Route::resource('employees', EmployeeController::class);

    // KPI Resource [FASE 2]
    Route::resource('kpis', \App\Modules\Kpi\KpiController::class)->except(['show']);

    // Logbook Resource [FASE 3]
    Route::resource('logbooks', \App\Http\Controllers\LogbookController::class);

    // Review Resource [FASE 3]
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LogbookReviewController::class, 'index'])->name('index');
        Route::get('/{logbook}', [\App\Http\Controllers\LogbookReviewController::class, 'edit'])->name('edit');
        Route::put('/{logbook}', [\App\Http\Controllers\LogbookReviewController::class, 'update'])->name('update');
    });
});
