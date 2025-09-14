<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\HonorariumController;
use App\Http\Controllers\Admin\TimController as AdminTimController;
use App\Http\Controllers\Admin\TimController;
use Illuminate\Support\Facades\Auth;

// Redirect root URL to login if not authenticated
Route::redirect('/', '/login');

// Guest routes (only accessible if NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Auth routes (only accessible if logged in)
Route::middleware(['auth'])->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect after login based on role
    Route::get('/dashboard', function () {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('staff.dashboard.index');
    })->name('dashboard');

    // ==================== STAFF ====================
    Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('dashboard', [StaffController::class, 'indexDashboard'])->name('dashboard.index');
        Route::get('profile', [StaffController::class, 'indexProfile'])->name('profile.index');

        // Tim routes
        Route::get('tim', [StaffController::class, 'indexTim'])->name('tim.index');
        Route::get('tim/create', [StaffController::class, 'createTim'])->name('tim.create');
        Route::post('tim', [StaffController::class, 'storeTim'])->name('tim.store');
    });

    // ==================== ADMIN ====================
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard admin
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Users
        Route::resource('users', UserController::class);
        Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');

        // CRUD Tims
        Route::resource('tims', AdminTimController::class);
        Route::patch('/tims/{tim}/approve', [TimController::class, 'approve'])->name('tims.approve');
        Route::patch('/tims/{tim}/reject', [TimController::class, 'reject'])->name('tims.reject');
        Route::delete('tims/bulk-delete', [AdminTimController::class, 'bulkDelete'])->name('tims.bulkDelete');
    });


    // ==================== STAFF TIM ====================
    Route::resource('tims', TimController::class);
});
