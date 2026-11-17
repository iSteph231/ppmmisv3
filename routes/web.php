<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityRequestController;
use App\Http\Controllers\InspectionReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkRequestController;
use App\Support\InventoryForms;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/request-account', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/request-account', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::redirect('/register', '/request-account');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::redirect('/verify-otp', '/request-account');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/change-password', [ResetPasswordController::class, 'showRequiredResetForm'])->name('password.setup');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');

    Route::prefix('inventory')->name('inventory.')->middleware('role:admin')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        foreach (array_keys(InventoryForms::names()) as $form) {
            Route::get('/'.$form, [InventoryController::class, 'show'])
                ->defaults('form', $form)->name($form);
        }

        Route::get('/{form}/export/pdf', [InventoryController::class, 'exportPdf'])
            ->whereIn('form', array_keys(InventoryForms::names()))->name('export-pdf');
        Route::get('/{form}/entries/{entry}/export/pdf', [InventoryController::class, 'exportPdf'])
            ->whereIn('form', array_keys(InventoryForms::names()))->name('entries.export-pdf');
        Route::post('/{form}/entries', [InventoryController::class, 'store'])
            ->whereIn('form', array_keys(InventoryForms::names()))->name('entries.store');
    });

    Route::prefix('request-facility')->name('request-facility.')->group(function () {
        Route::get('/', [FacilityRequestController::class, 'index'])->middleware('role:admin,user')->name('index');
        Route::get('/create', [FacilityRequestController::class, 'create'])->middleware('role:user')->name('create');
        Route::get('/availability', [FacilityRequestController::class, 'availability'])->middleware('role:user')->name('availability');
        Route::post('/', [FacilityRequestController::class, 'store'])->middleware('role:user')->name('store');
        Route::get('/{facilityRequest}', [FacilityRequestController::class, 'show'])->middleware('role:admin,user')->name('show');
        Route::get('/{facilityRequest}/program-image', [FacilityRequestController::class, 'programImage'])->middleware('role:admin,user')->name('program-image');
        Route::get('/{facilityRequest}/export-pdf', [FacilityRequestController::class, 'exportPdf'])->middleware('role:admin')->name('export-pdf');
        Route::get('/{facilityRequest}/photos', [FacilityRequestController::class, 'photos'])->middleware('role:user')->name('photos');
        Route::post('/{facilityRequest}/photos', [FacilityRequestController::class, 'uploadPhotos'])->middleware('role:user')->name('photos.store');
        Route::post('/{facilityRequest}/evaluation', [FacilityRequestController::class, 'storeEvaluation'])->middleware('role:user')->name('evaluation.store');
        Route::get('/{facilityRequest}/photos/{stage}', [FacilityRequestController::class, 'photo'])->middleware('role:admin,user')->name('photo');
        Route::patch('/{facilityRequest}/approve', [FacilityRequestController::class, 'approve'])->middleware('role:admin')->name('approve');
    });

    Route::get('/work-requests/{workRequest}/photo', [WorkRequestController::class, 'photo'])->name('work-requests.photo');
    Route::put('/work-requests/{workRequest}/schedule', [WorkRequestController::class, 'schedule'])->name('work-requests.schedule');
    Route::resource('work-requests', WorkRequestController::class);

    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/complete', [MaintenanceController::class, 'complete'])->name('complete');
        Route::get('/{id}', [MaintenanceController::class, 'show'])->name('show');
    });

    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionReportController::class, 'index'])->name('index');
        Route::get('/export/pdf', [InspectionReportController::class, 'exportAllPdf'])->name('export-all-pdf');
        Route::get('/{id}', [InspectionReportController::class, 'show'])->name('show');
        Route::get('/{id}/complete', [InspectionReportController::class, 'completeForm'])->name('complete-form');
        Route::post('/{id}/complete', [InspectionReportController::class, 'complete'])->name('complete');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/work-requests', [ReportController::class, 'workRequestsReport'])->name('work-requests');
            Route::get('/work-requests/export/pdf', [ReportController::class, 'exportWorkRequestsPdf'])->name('work-requests.export-pdf');
            Route::get('/work-requests/{workRequest}/export/pdf', [ReportController::class, 'exportWorkRequestPdf'])->name('work-requests.export-single-pdf');
            Route::get('/facility-requests', [ReportController::class, 'facilityRequestsReport'])->name('facility-requests');
            Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
        });

        Route::middleware('role:admin,engineer')->group(function () {
            Route::get('/maintenance', [ReportController::class, 'maintenanceReport'])->name('maintenance');
            Route::get('/maintenance/export/pdf', [ReportController::class, 'exportMaintenancePdf'])->name('maintenance.export-pdf');
        });
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/account-requests/{accountRequest}/approve', [UserController::class, 'approveAccountRequest'])->name('account-requests.approve');
        Route::delete('/account-requests/{accountRequest}', [UserController::class, 'declineAccountRequest'])->name('account-requests.decline');
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/browser', [NotificationController::class, 'browser'])->name('browser');
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    });
});
