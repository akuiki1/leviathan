<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimController;

Route::get('/', function () {
    return view('staff.index');
});

Route::get('/admin', function () {
    return view('admin.index');
});

Route::resource('tims', TimController::class);