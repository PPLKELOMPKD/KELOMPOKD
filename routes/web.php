<?php

<<<<<<< Updated upstream
=======
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
<<<<<<< Updated upstream
    return view('welcome');
=======
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
>>>>>>> Stashed changes
});
