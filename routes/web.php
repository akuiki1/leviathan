<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TimController as AdminTimController;
use Illuminate\Support\Facades\Auth;

Route::redirect('/', '/login');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Auth routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect setelah login
    Route::get('/dashboard', function () {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('staff.dashboard.index');
    })->name('dashboard');

    // ================= STAFF =================
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('dashboard', [StaffController::class, 'indexDashboard'])->name('dashboard.index');
        Route::get('profile', [StaffController::class, 'indexProfile'])->name('profile.index');

        // Tim routes (staff)
        Route::get('tim', [StaffController::class, 'indexTim'])->name('tim.index');
        Route::get('tim/create', [StaffController::class, 'createTim'])->name('tim.create');
        Route::post('tim', [StaffController::class, 'storeTim'])->name('tim.store');
    });

    // ================= ADMIN =================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Users
        Route::resource('users', UserController::class);

        // CRUD Tims
        Route::resource('tims', AdminTimController::class);
        Route::patch('tims/{tim}/approve', [AdminTimController::class, 'approve'])->name('tims.approve');
        Route::patch('tims/{tim}/reject', [AdminTimController::class, 'reject'])->name('tims.reject');
        Route::get('tims/{tim}/check-members', [AdminTimController::class, 'checkMemberStatus'])
            ->name('tims.check-members');
    });
});
