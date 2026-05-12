<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkRequestController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\InspectionReportController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==================== AUTHENTICATION ROUTES ====================
// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Register Routes (ADD THESE)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// ==================== PROTECTED ROUTES ====================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // Work Requests
    Route::middleware(['auth'])->group(function () {
    
    // ==================== WORK REQUESTS ROUTES ====================
    // IMPORTANT: Specific routes MUST come BEFORE the resource route
    
    // Update status route (specific)
    Route::post('/work-requests/{workRequest}/update-status', [WorkRequestController::class, 'updateStatus'])->name('work-requests.update-status');
    
    // Export single PDF (specific pattern with {id})
    Route::get('/work-requests/{id}/export-pdf', [WorkRequestController::class, 'exportSinglePDF'])->name('work-requests.export-single-pdf');
    
    // Resource route (generic - MUST be LAST)
    Route::resource('work-requests', WorkRequestController::class);
    
    });
    
    // Maintenance Records
    Route::middleware(['auth'])->group(function () {
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{id}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance/{id}', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::delete('/maintenance/{id}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
    
});
    
    // Report Routes
Route::prefix('reports')->name('reports.')->middleware('auth')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/work-requests', [ReportController::class, 'workRequestsReport'])->name('work-requests');
    Route::get('/maintenance', [ReportController::class, 'maintenanceReport'])->name('maintenance');
    Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
});
    
    // Users (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::get('/settings/system', [SettingsController::class, 'systemInfo'])->name('settings.system');
    
    // Notifications (AJAX)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
});

// API routes for AJAX calls
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData']);
    Route::post('/work-requests', [WorkRequestController::class, 'store']);
    Route::get('/search/users', [UserController::class, 'search']);
});

Route::middleware(['auth'])->group(function () {
    // Work Requests Routes
    Route::resource('work-requests', WorkRequestController::class);
});

Route::get('/notifications/fetch', function() {
    $notifications = Auth::user()->notifications ?? collect([]);
    return response()->json(['success' => true, 'notifications' => $notifications]);
})->middleware('auth');

Route::post('/notifications/mark-read', function() {
    // handle mark as read
})->middleware('auth');

// API routes for notifications
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notifications', [DashboardController::class, 'getNotifications']);
    Route::post('/api/notifications/{id}/mark-read', [DashboardController::class, 'markNotificationRead']);
    Route::post('/api/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead']);
    Route::get('/api/dashboard-stats', [DashboardController::class, 'getStats']);
    Route::get('/api/chart-data', [DashboardController::class, 'getChartData']);
});

Route::put('/work-requests/{workRequest}/schedule', [WorkRequestController::class, 'schedule'])->name('work-requests.schedule');

// Inspection Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/inspections', [InspectionReportController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/{id}', [InspectionReportController::class, 'show'])->name('inspections.show');
    Route::get('/inspections/{id}/complete', [InspectionReportController::class, 'completeForm'])->name('inspections.complete-form');
    Route::post('/inspections/{id}/complete', [InspectionReportController::class, 'complete'])->name('inspections.complete');
});

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
