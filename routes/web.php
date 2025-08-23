<?php

use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimController;


Route::get('/', [StaffController::class, 'index'])->name('home');

Route::get('/admin', function () {
    return view('admin.index');
});

Route::resource('tims', TimController::class);
