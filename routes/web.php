<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TimController as AdminTimController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\AsnImportController;
use App\Http\Controllers\EselonController;
use App\Http\Controllers\JabatanController;
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

        // Import/update massal ASN via Excel (didaftarkan sebelum resource agar
        // 'users/import' tidak tertangkap route show 'users/{user}')
        Route::get('users/import', [AsnImportController::class, 'form'])->name('users.import.form');
        Route::get('users/import/template', [AsnImportController::class, 'template'])->name('users.import.template');
        Route::post('users/import/preview', [AsnImportController::class, 'preview'])->name('users.import.preview');
        Route::post('users/import/apply', [AsnImportController::class, 'apply'])->name('users.import.apply');

        // CRUD Users
        Route::resource('users', UserController::class);

        // Master data: Eselon & Jabatan (konfigurasi kuota honor)
        Route::resource('eselons', EselonController::class)->except(['show']);
        Route::resource('jabatans', JabatanController::class)->except(['show']);

        // CRUD Tims
        Route::resource('tims', AdminTimController::class);
        Route::patch('tims/{tim}/approve', [AdminTimController::class, 'approve'])->name('tims.approve');
        Route::patch('tims/{tim}/reject', [AdminTimController::class, 'reject'])->name('tims.reject');
        Route::get('tims/{tim}/check-members', [AdminTimController::class, 'checkMemberStatus'])
            ->name('tims.check-members');

        // Laporan honor per eselon (audit akhir tahun) + drill-down per ASN
        Route::get('laporan-honor', [LaporanController::class, 'index'])->name('laporan-honor.index');
        Route::get('laporan-honor/asn', [LaporanController::class, 'asn'])->name('laporan-honor.asn');
        Route::get('laporan-honor/export', [LaporanController::class, 'export'])->name('laporan-honor.export');
    });
});
