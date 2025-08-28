    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\Admin\UserController;
    use App\Http\Controllers\Admin\HonorariumController;
    use App\Http\Controllers\Admin\TimController as AdminTimController;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    */

    // ==================== AUTH ====================
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ==================== LANDING ====================
    Route::get('/', function () {
        return view('staff.index'); // halaman depan staff
    });

    // ==================== STAFF ====================
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // ==================== ADMIN ====================
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard admin
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Users
        Route::resource('users', UserController::class);

        // CRUD Tims
        Route::resource('tims', AdminTimController::class);

        // CRUD Honorarium
        Route::resource('honoraria', HonorariumController::class);
    });

    Route::post('/admin/users/bulk-delete', [UserController::class,'bulkDelete'])->name('admin.users.bulkDelete');
    Route::post('/admin/tims/bulk-delete', [UserController::class,'bulkDelete'])->name('admin.tims.bulkDelete');
    Route::post('/admin/honoraria/bulk-delete', [UserController::class,'bulkDelete'])->name('admin.honoraria.bulkDelete');

