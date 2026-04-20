<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LowonganKerjaController;

Route::get('/', function () {
    return redirect()->route('lowongan.index');
});

Route::resource('lowongan', LowonganKerjaController::class);
