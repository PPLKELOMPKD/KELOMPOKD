<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTrackingController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/peserta', PesertaController::class)->name('peserta');
Route::get('/perusahaan', function () { return Inertia::render('Perusahaan'); })->name('perusahaan');
Route::get('/tentang', function () { return Inertia::render('TentangSikara'); })->name('tentang');

// Fitur Navigasi
Route::get('/lowongan', function () { return Inertia::render('Features/Lowongan'); })->name('lowongan');
Route::get('/perusahaan-list', function () { return Inertia::render('Features/CompanyList'); })->name('perusahaan-list');
Route::get('/lms', function () { return Inertia::render('Features/Lms'); })->name('lms');
Route::get('/event', function () { return Inertia::render('Features/Event'); })->name('event');
Route::get('/generate-cv', function () { return Inertia::render('Features/GenerateCv'); })->name('generate-cv');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
        Route::post('/profile/skills', [SkillController::class, 'store'])->name('skills.store');
        Route::get('/internships', [InternshipController::class, 'index'])->name('internships.index');
        Route::get('/intenships/{internship}', [InternshipController::class, 'show'])->name('internships.show');
        Route::post('/internship-apply', [ApplicationController::class, 'store'])->name('internships.apply');
        Route::get('/applications', [ApplicationTrackingController::class, 'index'])->name('applications.index');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::get('/cv/download', [CvController::class, 'download'])->name('cv.download');
    });

    Route::middleware('role:perusahaan')->prefix('perusahaan')->name('perusahaan.')->group(function () {
        Route::get('/internships', [\App\Http\Controllers\CompanyInternshipController::class, 'index'])->name('internships.index');
        Route::get('/internships/create', [\App\Http\Controllers\CompanyInternshipController::class, 'create'])->name('internships.create');
        Route::post('/internships', [\App\Http\Controllers\CompanyInternshipController::class, 'store'])->name('internships.store');
        Route::get('/internships/{internship}/edit', [\App\Http\Controllers\CompanyInternshipController::class, 'edit'])->name('internships.edit');
        Route::put('/internships/{internship}', [\App\Http\Controllers\CompanyInternshipController::class, 'update'])->name('internships.update');
        Route::delete('/internships/{internship}', [\App\Http\Controllers\CompanyInternshipController::class, 'destroy'])->name('internships.destroy');
    });

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
