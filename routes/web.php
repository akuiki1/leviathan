<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\HonorariumController;
use App\Http\Controllers\Admin\TimController as AdminTimController;
use App\Http\Controllers\TimController;

// ==================== AUTH ====================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== LANDING / STAFF ====================
Route::get('staff/dashboard', [StaffController::class, 'indexDashboard'])->name('staff.dashboard.index');
Route::get('staff/profile', [StaffController::class, 'indexProfile'])->name('staff.profile.index');
Route::get('staff/tim', [StaffController::class, 'indexTim'])->name('staff.tim.index');
Route::get('staff/tim/create', [StaffController::class, 'createTim'])->name('staff.tim.create');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== ADMIN ====================
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard admin
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Users
        Route::resource('users', UserController::class);

        // CRUD Tims
        Route::resource('tims', AdminTimController::class);

        // CRUD Honorarium
        Route::resource('honoraria', HonorariumController::class);

        // Bulk delete
        Route::post('users/bulk-delete', [UserController::class,'bulkDelete'])->name('users.bulkDelete');
        Route::post('tims/bulk-delete', [AdminTimController::class,'bulkDelete'])->name('tims.bulkDelete');
        Route::post('honoraria/bulk-delete', [HonorariumController::class,'bulkDelete'])->name('honoraria.bulkDelete');
    });

    // ==================== STAFF TIM ====================
    Route::resource('tims', TimController::class);
});
