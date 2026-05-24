<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTrackingController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\LmsController;
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
Route::get('/perusahaan', function () {
    return Inertia::render('Perusahaan');
})->name('perusahaan');
Route::get('/tentang', function () {
    return Inertia::render('TentangSikara');
})->name('tentang');
Route::get('/panduan', function () {
    return Inertia::render('Panduan');
})->name('panduan');
Route::get('/pusat-informasi', function () {
    return Inertia::render('PusatInformasi');
})->name('pusat-informasi');

// Fitur Navigasi (publik)
Route::get('/lowongan', [InternshipController::class, 'lowongan'])->name('lowongan');
Route::get('/perusahaan-list', [\App\Http\Controllers\CompanyController::class, 'index'])->name('perusahaan-list');
Route::get('/perusahaan-profile/{id}', [\App\Http\Controllers\CompanyController::class, 'show'])->name('perusahaan.profile');
Route::get('/lms', [LmsController::class, 'index'])->name('lms');
Route::get('/lms/module/{course}', [LmsController::class, 'show'])->name('lms.module.show');
Route::get('/event', function () {
    $events = \App\Models\Event::with(['company', 'company.perusahaanProfile'])->where('status', 'published')->latest()->get();
    return Inertia::render('Features/Event', ['events' => $events]);
})->name('event');
Route::get('/generate-cv', function () {
    return Inertia::render('Features/GenerateCv');
})->name('generate-cv');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ── Mahasiswa ─────────────────────────────────────────────────────
    Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
        Route::post('/profile/skills', [SkillController::class, 'store'])->name('skills.store');
        Route::get('/internships', [InternshipController::class, 'index'])->name('internships.index');
        Route::get('/internships/{internship}', [InternshipController::class, 'show'])->name('internships.show');
        Route::post('/internship-apply', [ApplicationController::class, 'store'])->name('internships.apply');
        Route::get('/applications', [ApplicationTrackingController::class, 'index'])->name('applications.index');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::get('/cv/download', [CvController::class, 'download'])->name('cv.download');

        Route::post('/lms/{course}/enroll', [\App\Http\Controllers\LmsEnrollmentController::class, 'store'])->name('lms.enrollments.store');
        Route::delete('/lms/{course}/enroll', [\App\Http\Controllers\LmsEnrollmentController::class, 'destroy'])->name('lms.enrollments.destroy');
        Route::post('/lms/lessons/{lesson}/complete', [\App\Http\Controllers\LmsLessonCompletionController::class, 'store'])->name('lms.lessons.complete');
        Route::post('/lms/quizzes/{quiz}/submit', [\App\Http\Controllers\LmsQuizAttemptController::class, 'store'])->name('lms.quizzes.submit');
        Route::post('/lms/courses/{course}/assignments/{assignment}/submit', [\App\Http\Controllers\LmsAssignmentSubmissionController::class, 'store'])->name('lms.assignments.submit');
        // Route ini ditambahkan/diperbaiki di remote
        Route::delete('/lms/courses/{course}/assignments/{assignment}/submissions/{submission}', [\App\Http\Controllers\LmsAssignmentSubmissionController::class, 'destroy'])->name('lms.assignments.revoke');
        Route::get('/lms/{course}/certificate', [\App\Http\Controllers\LmsCertificateController::class, 'download'])->name('lms.certificate.download');
    });

    // ── Perusahaan ────────────────────────────────────────────────────
    Route::middleware(['auth', 'role:perusahaan'])->prefix('perusahaan')->name('perusahaan.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Lowongan
        Route::get('/internships', [\App\Http\Controllers\CompanyInternshipController::class, 'index'])->name('internships.index');
        Route::get('/internships/create', [\App\Http\Controllers\CompanyInternshipController::class, 'create'])->name('internships.create');
        Route::post('/internships', [\App\Http\Controllers\CompanyInternshipController::class, 'store'])->name('internships.store');
        Route::get('/internships/{internship}/edit', [\App\Http\Controllers\CompanyInternshipController::class, 'edit'])->name('internships.edit');
        Route::put('/internships/{internship}', [\App\Http\Controllers\CompanyInternshipController::class, 'update'])->name('internships.update');
        Route::delete('/internships/{internship}', [\App\Http\Controllers\CompanyInternshipController::class, 'destroy'])->name('internships.destroy');

        // Kelola Pelamar
        Route::get('/applicants', [\App\Http\Controllers\CompanyApplicantController::class, 'index'])->name('applicants.index');
        Route::get('/applicants/{application}', [\App\Http\Controllers\CompanyApplicantController::class, 'show'])->name('applicants.show');
        Route::patch('/applicants/{application}/status', [\App\Http\Controllers\CompanyApplicantController::class, 'updateStatus'])->name('applicants.updateStatus');

        // Event
        Route::resource('/events', \App\Http\Controllers\CompanyEventController::class)->except('show');

        // Laporan
        Route::get('/reports', function () {
            return \Inertia\Inertia::render('Company/Reports/Index');
        })->name('reports.index');

        // LMS — Kelola Kursus (CRUD)
        Route::resource('/lms', \App\Http\Controllers\CompanyLmsCourseController::class)->parameters(['lms' => 'course'])->names('lms');
        Route::post('/lms/{course}/publish', [\App\Http\Controllers\CompanyLmsCourseController::class, 'publish'])->name('lms.publish');
        Route::post('/lms/{course}/unpublish', [\App\Http\Controllers\CompanyLmsCourseController::class, 'unpublish'])->name('lms.unpublish');

        // LMS — Enrollments & Grading
        Route::get('/lms/{course}/enrollments', [\App\Http\Controllers\CompanyLmsEnrollmentController::class, 'index'])->name('lms.enrollments.index');
        Route::patch('/lms/{course}/enrollments/{enrollment}/graduate', [\App\Http\Controllers\CompanyLmsEnrollmentController::class, 'toggleGraduation'])->name('lms.enrollments.graduate');
        Route::post('/lms/{course}/enrollments/{enrollment}/reset', [\App\Http\Controllers\CompanyLmsEnrollmentController::class, 'resetProgress'])->name('lms.enrollments.reset');
        Route::delete('/lms/{course}/enrollments/{enrollment}', [\App\Http\Controllers\CompanyLmsEnrollmentController::class, 'destroy'])->name('lms.enrollments.destroy');

        // LMS — Builder (Konten Bab, Materi, Quiz, Tugas)
        Route::get('/lms/{course}/builder', [\App\Http\Controllers\CompanyLmsContentController::class, 'builder'])->name('lms.builder');
        Route::post('/lms/{course}/chapters', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeChapter'])->name('lms.chapters.store');
        Route::put('/lms/chapters/{chapter}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateChapter'])->name('lms.chapters.update');
        Route::delete('/lms/chapters/{chapter}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyChapter'])->name('lms.chapters.destroy');

        Route::post('/lms/chapters/{chapter}/lessons', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeLesson'])->name('lms.lessons.store');
        Route::put('/lms/lessons/{lesson}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateLesson'])->name('lms.lessons.update');
        Route::delete('/lms/lessons/{lesson}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyLesson'])->name('lms.lessons.destroy');

        Route::post('/lms/chapters/{chapter}/assignments', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeAssignment'])->name('lms.assignments.store');
        Route::patch('/lms/assignments/{assignment}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateAssignment'])->name('lms.assignments.update');
        Route::delete('/lms/assignments/{assignment}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyAssignment'])->name('lms.assignments.destroy');

        Route::post('/lms/chapters/{chapter}/quiz', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeQuiz'])->name('lms.quizzes.store');
        Route::put('/lms/quizzes/{quiz}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateQuiz'])->name('lms.quizzes.update');
        Route::delete('/lms/quizzes/{quiz}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyQuiz'])->name('lms.quizzes.destroy');

        Route::post('/lms/quizzes/{quiz}/questions', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeQuestion'])->name('lms.questions.store');
        Route::put('/lms/questions/{question}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateQuestion'])->name('lms.questions.update');
        Route::delete('/lms/questions/{question}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyQuestion'])->name('lms.questions.destroy');

        Route::post('/lms/questions/{question}/options', [\App\Http\Controllers\CompanyLmsContentController::class, 'storeOption'])->name('lms.options.store');
        Route::put('/lms/options/{option}', [\App\Http\Controllers\CompanyLmsContentController::class, 'updateOption'])->name('lms.options.update');
        Route::delete('/lms/options/{option}', [\App\Http\Controllers\CompanyLmsContentController::class, 'destroyOption'])->name('lms.options.destroy');

        Route::get('/lms/{course}/assignments/{assignment}/submissions', [\App\Http\Controllers\CompanyLmsGradingController::class, 'index'])->name('lms.grading.index');
        Route::patch('/lms/{course}/assignments/{assignment}/submissions/{submission}', [\App\Http\Controllers\CompanyLmsGradingController::class, 'update'])->name('lms.grading.update');
    });

    // ── Admin ─────────────────────────────────────────────────────────
    Route::middleware(['auth', 'role:admin', 'strict.admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        // Fitur lain seperti manajemen user, verifikasi, dll akan ditambahkan di sini
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
