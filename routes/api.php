<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkRequestController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard API endpoints
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData']);
    
    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    
    // Work Requests API
    Route::get('/work-requests', [WorkRequestController::class, 'index']);
    Route::post('/work-requests', [WorkRequestController::class, 'store']);
    Route::put('/work-requests/{workRequest}', [WorkRequestController::class, 'update']);
    Route::delete('/work-requests/{workRequest}', [WorkRequestController::class, 'destroy']);
});