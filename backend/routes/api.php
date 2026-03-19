<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KpiAssignmentController;
use App\Http\Controllers\Api\V1\LogbookController;
use App\Http\Controllers\Api\V1\ManagerLogbookController;
use App\Http\Controllers\Api\V1\MasterKpiController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Users Management
        Route::apiResource('users', UsersController::class);
        Route::put('users/{user}/reset-password', [UsersController::class, 'resetPassword']);

        // KPI Master
        Route::apiResource('kpi/master', MasterKpiController::class)->parameters([
            'master' => 'kpi',
        ]);

        Route::get('kpi/assignments', [KpiAssignmentController::class, 'index']);
        Route::post('kpi/assignments', [KpiAssignmentController::class, 'store']);
        Route::delete('kpi/assignments/{assignment}', [KpiAssignmentController::class, 'destroy']);
        Route::get('kpi/me', [KpiAssignmentController::class, 'me']);

        Route::post('logbooks/{logbook}/revert', [ManagerLogbookController::class, 'revert']);
        Route::put('logbooks/{logbook}/rate', [ManagerLogbookController::class, 'rate']);

        Route::post('logbooks/start', [LogbookController::class, 'start']);
        Route::get('logbooks', [LogbookController::class, 'index']);
        Route::get('logbooks/{logbook}', [LogbookController::class, 'show']);
        Route::patch('logbooks/{logbook}/kpi/{detail}/toggle', [LogbookController::class, 'toggleKpi']);
        Route::post('logbooks/{logbook}/submit', [LogbookController::class, 'submit']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::put('notifications/{notification}/read', [NotificationController::class, 'read']);

        Route::get('audit-logs', [AuditController::class, 'index']);

        // Analytics & Dashboards
        Route::get('dashboard/admin', [AnalyticsController::class, 'adminDashboard']);
        Route::get('dashboard/manager', [AnalyticsController::class, 'managerDashboard']);
        Route::get('dashboard/manager/locations', [AnalyticsController::class, 'teamLocations']);
        Route::get('dashboard/staff', [AnalyticsController::class, 'staffDashboard']);
        Route::get('users/{user}/kpi-achievements', [AnalyticsController::class, 'userKpiAchievements']);
        Route::get('reports/export', [AnalyticsController::class, 'exportReports']);
    });
});
