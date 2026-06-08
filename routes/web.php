<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventModerationController;
use App\Http\Controllers\Admin\ApplicationDataController;
use App\Http\Controllers\Admin\CompanyVerificationController;
use App\Http\Controllers\Admin\AdminLmsController;
use App\Http\Controllers\Admin\InternshipModerationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTrackingController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\EventRatingController;
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
})->name('home')->middleware('guest');

Route::get('/peserta', PesertaController::class)->name('peserta')->middleware('role.landing:mahasiswa');
Route::get('/perusahaan', function () {
    return Inertia::render('Perusahaan');
})->name('perusahaan')->middleware('role.landing:perusahaan');
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
    $user = auth()->user();
    $isAuthenticated = (bool) $user;
    $isMahasiswa = $user && $user->role === 'mahasiswa';
    $now = \Carbon\Carbon::now();

    $events = \App\Models\Event::with(['company', 'company.perusahaanProfile'])
        ->where('status', 'published')
        ->where('moderation_status', 'approved')
        ->latest()
        ->get()
        ->map(function ($event) use ($user, $isMahasiswa, $now) {
            $activeCount = $event->registrations()
                ->whereIn('status', ['registered', 'attended'])
                ->count();

            $userRegistration = null;
            if ($isMahasiswa && $user) {
                $userRegistration = $event->registrations()
                    ->where('user_id', $user->id)
                    ->first();
            }

            $ratingsData = $event->ratings();
            $avgRating   = round($ratingsData->avg('rating'), 2);
            $ratingCount = $ratingsData->count();

            $userRating = null;
            if ($isMahasiswa && $user) {
                $ur = $event->ratings()->where('user_id', $user->id)->first();
                if ($ur) {
                    $userRating = ['rating' => $ur->rating, 'comment' => $ur->comment];
                }
            }

            // Cek status waktu event berdasarkan datetime (tanggal + jam)
            $eventStartDateTime = \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->start_time);
            $eventEndDateTime   = \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
            $isCompleted = $event->status === 'completed' || $now->gte($eventEndDateTime);
            $isStarted   = !$isCompleted && $now->gte($eventStartDateTime);

            return [
                'id'               => $event->id,
                'title'            => $event->title,
                'category'         => $event->category,
                'description'      => $event->description,
                'date'             => $event->date,
                'start_time'       => $event->start_time,
                'end_time'         => $event->end_time,
                'location'         => $event->location,
                'type'             => $event->type,
                'status'           => $event->status,
                'max_participants'  => $event->max_participants,
                'active_count'     => $activeCount,
                'is_completed'     => $isCompleted,
                'is_started'       => $isStarted,
                'is_full'          => $event->max_participants !== null && $activeCount >= $event->max_participants,
                'user_registration' => $userRegistration ? [
                    'id'     => $userRegistration->id,
                    'status' => $userRegistration->status,
                ] : null,
                'avg_rating'   => $avgRating ?: null,
                'rating_count' => $ratingCount,
                'user_rating'  => $userRating,
                'company'          => $event->company ? [
                    'id'   => $event->company->id,
                    'name' => $event->company->name,
                ] : null,
            ];
        });

    return Inertia::render('Features/Event', [
        'events'          => $events,
        'isAuthenticated'  => $isAuthenticated,
        'isMahasiswa'     => $isMahasiswa,
    ]);
})->name('event');

Route::get('/event-feedback', function () {
    $user           = auth()->user();
    $isAuthenticated = (bool) $user;
    $isMahasiswa    = $user && $user->role === 'mahasiswa';
    $now            = \Carbon\Carbon::now();

    // Ambil semua event yang sudah selesai:
    // 1. Status 'completed' ATAU
    // 2. Status 'published' & moderation 'approved' & datetime (date + end_time) sudah lewat
    $completedEvents = \App\Models\Event::with(['company', 'company.perusahaanProfile'])
        ->where('moderation_status', 'approved')
        ->where(function ($q) use ($now) {
            $q->where('status', 'completed')
              ->orWhere(function ($q2) use ($now) {
                  $q2->where('status', 'published')
                     ->whereRaw("CONCAT(date, ' ', end_time) < ?", [$now->format('Y-m-d H:i:s')]);
              });
        })
        ->latest()
        ->get()
        ->map(function ($event) use ($user, $isMahasiswa) {
            $avgRating   = round($event->ratings()->avg('rating'), 2) ?: null;
            $ratingCount = $event->ratings()->count();
            $activeCount = $event->registrations()
                ->whereIn('status', ['registered', 'attended'])
                ->count();

            $userRating = null;
            if ($isMahasiswa && $user) {
                $ur = $event->ratings()->where('user_id', $user->id)->first();
                if ($ur) {
                    $userRating = ['rating' => $ur->rating, 'comment' => $ur->comment];
                }
            }

            // Hitung distribusi bintang
            $ratingDistribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $ratingDistribution[$i] = $event->ratings()->where('rating', $i)->count();
            }

            return [
                'id'                  => $event->id,
                'title'               => $event->title,
                'category'            => $event->category,
                'description'         => $event->description,
                'date'                => $event->date,
                'start_time'          => $event->start_time,
                'end_time'            => $event->end_time,
                'location'            => $event->location,
                'type'                => $event->type,
                'status'              => $event->status,
                'max_participants'    => $event->max_participants,
                'active_count'        => $activeCount,
                'avg_rating'          => $avgRating,
                'rating_count'        => $ratingCount,
                'rating_distribution' => $ratingDistribution,
                'user_rating'         => $userRating,
                'company'             => $event->company ? [
                    'id'   => $event->company->id,
                    'name' => $event->company->name,
                ] : null,
            ];
        });

    return Inertia::render('Features/EventFeedback', [
        'completedEvents' => $completedEvents,
        'isAuthenticated' => $isAuthenticated,
        'isMahasiswa'     => $isMahasiswa,
    ]);
})->name('event.feedback');

Route::get('/event/{event}', function (\App\Models\Event $event) {
    $user = auth()->user();
    $isAuthenticated = (bool) $user;
    $isMahasiswa = $user && $user->role === 'mahasiswa';

    if ($event->status !== 'published' || $event->moderation_status !== 'approved') {
        abort(404);
    }

    $activeCount = $event->registrations()
        ->whereIn('status', ['registered', 'attended'])
        ->count();

    $userRegistration = null;
    if ($isMahasiswa && $user) {
        $userRegistration = $event->registrations()
            ->where('user_id', $user->id)
            ->first();
    }

    $avgRating   = round($event->ratings()->avg('rating'), 2);
    $ratingCount = $event->ratings()->count();
    $allRatings  = $event->ratings()->with('user')->latest()->get()->map(function ($r) {
        return [
            'id'         => $r->id,
            'rating'     => $r->rating,
            'comment'    => $r->comment,
            'created_at' => $r->created_at,
            'user_name'  => $r->user ? $r->user->name : 'Mahasiswa',
        ];
    });

    $userRating = null;
    if ($isMahasiswa && $user) {
        $ur = $event->ratings()->where('user_id', $user->id)->first();
        if ($ur) {
            $userRating = ['rating' => $ur->rating, 'comment' => $ur->comment];
        }
    }

    $now = \Carbon\Carbon::now();
    $eventStartDateTime = \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->start_time);
    $eventEndDateTime   = \Carbon\Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
    $isEventCompleted   = $event->status === 'completed' || $now->gte($eventEndDateTime);
    $isEventStarted     = !$isEventCompleted && $now->gte($eventStartDateTime);

    $eventData = [
        'id'               => $event->id,
        'title'            => $event->title,
        'category'         => $event->category,
        'description'      => $event->description,
        'date'             => $event->date,
        'start_time'       => $event->start_time,
        'end_time'         => $event->end_time,
        'location'         => $event->location,
        'type'             => $event->type,
        'status'           => $event->status,
        'max_participants'  => $event->max_participants,
        'active_count'     => $activeCount,
        'is_full'          => $event->max_participants !== null && $activeCount >= $event->max_participants,
        'is_completed'     => $isEventCompleted,
        'is_started'       => $isEventStarted,
        'user_registration' => $userRegistration ? [
            'id'     => $userRegistration->id,
            'status' => $userRegistration->status,
        ] : null,
        'avg_rating'        => $avgRating ?: null,
        'rating_count'      => $ratingCount,
        'ratings'           => $allRatings,
        'user_rating'       => $userRating,
        'company'          => $event->company ? [
            'id'   => $event->company->id,
            'name' => $event->company->name,
        ] : null,
    ];

    return Inertia::render('Features/EventDetail', [
        'event'           => $eventData,
        'isAuthenticated'  => $isAuthenticated,
        'isMahasiswa'     => $isMahasiswa,
    ]);
})->name('event.detail');
Route::get('/generate-cv', function () {
    return Inertia::render('Features/GenerateCv');
})->name('generate-cv');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('dm')->name('dm.')->group(function () {
        Route::get('/conversations', [DirectMessageController::class, 'index'])->name('conversations.index');
        Route::get('/users', [DirectMessageController::class, 'users'])->name('users.index');
        Route::post('/conversations', [DirectMessageController::class, 'store'])->name('conversations.store');
        Route::get('/conversations/{conversation}/messages', [DirectMessageController::class, 'messages'])->name('conversations.messages');
        Route::post('/conversations/{conversation}/messages', [DirectMessageController::class, 'send'])->name('conversations.send');
        Route::post('/conversations/{conversation}/read', [DirectMessageController::class, 'read'])->name('conversations.read');
    });

    // ── Global Notifications (Mahasiswa & Perusahaan) ─────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // ── Mahasiswa ─────────────────────────────────────────────────────
    Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
        Route::post('/profile/skills', [SkillController::class, 'store'])->name('skills.store');
        Route::get('/internships', [InternshipController::class, 'index'])->name('internships.index');
        Route::get('/internships/{internship}', [InternshipController::class, 'show'])->name('internships.show');
        Route::post('/internship-apply', [ApplicationController::class, 'store'])->name('internships.apply');
        Route::get('/applications', [ApplicationTrackingController::class, 'index'])->name('applications.index');
        Route::get('/cv/download', [CvController::class, 'download'])->name('cv.download');

        Route::post('/lms/{course}/enroll', [\App\Http\Controllers\LmsEnrollmentController::class, 'store'])->name('lms.enrollments.store');
        Route::delete('/lms/{course}/enroll', [\App\Http\Controllers\LmsEnrollmentController::class, 'destroy'])->name('lms.enrollments.destroy');
        Route::post('/lms/lessons/{lesson}/complete', [\App\Http\Controllers\LmsLessonCompletionController::class, 'store'])->name('lms.lessons.complete');
        Route::post('/lms/quizzes/{quiz}/submit', [\App\Http\Controllers\LmsQuizAttemptController::class, 'store'])->name('lms.quizzes.submit');
        Route::post('/lms/courses/{course}/assignments/{assignment}/submit', [\App\Http\Controllers\LmsAssignmentSubmissionController::class, 'store'])->name('lms.assignments.submit');
        // Route ini ditambahkan/diperbaiki di remote
        Route::delete('/lms/courses/{course}/assignments/{assignment}/submissions/{submission}', [\App\Http\Controllers\LmsAssignmentSubmissionController::class, 'destroy'])->name('lms.assignments.revoke');
        Route::get('/lms/{course}/certificate', [\App\Http\Controllers\LmsCertificateController::class, 'download'])->name('lms.certificate.download');

        // ── Event Registration (Mahasiswa) ────────────────────────────
        Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])->name('events.register');
        Route::delete('/events/{event}/register', [EventRegistrationController::class, 'destroy'])->name('events.register.cancel');
        Route::get('/my-events', [EventRegistrationController::class, 'myEvents'])->name('my-events');

        // ── Event Rating (Mahasiswa) ──────────────────────────────────
        Route::post('/events/{event}/ratings', [EventRatingController::class, 'store'])->name('events.ratings.store');
        Route::put('/events/{event}/ratings', [EventRatingController::class, 'update'])->name('events.ratings.update');
    });

    // ── Perusahaan ────────────────────────────────────────────────────
    Route::middleware(['auth', 'verified', 'role:perusahaan'])->prefix('perusahaan')->name('perusahaan.')->group(function () {
        // Dashboard: bisa diakses oleh semua perusahaan (termasuk yang pending verifikasi admin)
        // Halaman ini akan menampilkan status verifikasi
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Halaman pending verifikasi admin (untuk perusahaan yg belum diverifikasi)
        Route::get('/pending-verification', function () {
            $user = auth()->user();
            if ($user->status === 'active') {
                return redirect()->route('perusahaan.dashboard');
            }
            return \Inertia\Inertia::render('Perusahaan/PendingVerification', [
                'status' => $user->status,
                'name'   => $user->name,
                'email'  => $user->email,
            ]);
        })->name('pending-verification');

        Route::get('/profile', [\App\Http\Controllers\CompanyController::class, 'ownerProfile'])->name('profile.show');
        Route::post('/profile', [\App\Http\Controllers\CompanyController::class, 'updateOwnerProfile'])->name('profile.update');

        // ── Routes berikut memerlukan verifikasi admin (status = active) ──────
        Route::middleware('company.verified')->group(function () {
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
            Route::get('/reports', [\App\Http\Controllers\CompanyReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/download', [\App\Http\Controllers\CompanyReportController::class, 'downloadRecruitment'])->name('reports.download');
            Route::get('/reports/events/download', [\App\Http\Controllers\CompanyReportController::class, 'downloadEvents'])->name('reports.events.download');

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
    });


    // ── Admin ─────────────────────────────────────────────────────────
    Route::middleware(['auth', 'role:admin', 'strict.admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ── Moderasi Lowongan ─────────────────────────────────────────
        Route::get('/internships', [InternshipModerationController::class, 'index'])->name('internships.index');
        Route::get('/internships/calendar', [InternshipModerationController::class, 'calendar'])->name('internships.calendar');
        Route::get('/internships/{internship}', [InternshipModerationController::class, 'show'])->name('internships.show');
        Route::patch('/internships/{internship}/approve', [InternshipModerationController::class, 'approve'])->name('internships.approve');
        Route::patch('/internships/{internship}/reject', [InternshipModerationController::class, 'reject'])->name('internships.reject');
        Route::patch('/internships/{internship}/takedown', [InternshipModerationController::class, 'takedown'])->name('internships.takedown');

        // ── Pantau LMS ────────────────────────────────────────────────
        Route::get('/lms', [AdminLmsController::class, 'index'])->name('lms.index');
        Route::get('/lms/users/{user}/detail', [AdminLmsController::class, 'userDetail'])->name('lms.users.detail');
        Route::patch('/lms/users/{user}/suspend', [AdminLmsController::class, 'suspendUser'])->name('lms.users.suspend');
        Route::patch('/lms/users/{user}/activate', [AdminLmsController::class, 'activateUser'])->name('lms.users.activate');
        Route::delete('/lms/users/{user}', [AdminLmsController::class, 'deleteUser'])->name('lms.users.destroy');
        
        // Course Moderation
        Route::patch('/lms/courses/{course:id}/approve', [AdminLmsController::class, 'approveCourse'])->name('lms.courses.approve');
        Route::patch('/lms/courses/{course:id}/reject', [AdminLmsController::class, 'rejectCourse'])->name('lms.courses.reject');
        Route::patch('/lms/courses/{course:id}/takedown', [AdminLmsController::class, 'takedownCourse'])->name('lms.courses.takedown');
        Route::patch('/lms/courses/{course:id}/restore', [AdminLmsController::class, 'restoreCourse'])->name('lms.courses.restore');

        // Content Moderation
        Route::patch('/lms/lessons/{lesson}/takedown', [AdminLmsController::class, 'takedownLesson'])->name('lms.lessons.takedown');
        Route::patch('/lms/lessons/{lesson}/restore', [AdminLmsController::class, 'restoreLesson'])->name('lms.lessons.restore');
        Route::patch('/lms/quizzes/{quiz}/takedown', [AdminLmsController::class, 'takedownQuiz'])->name('lms.quizzes.takedown');
        Route::patch('/lms/quizzes/{quiz}/restore', [AdminLmsController::class, 'restoreQuiz'])->name('lms.quizzes.restore');
        Route::patch('/lms/assignments/{assignment}/takedown', [AdminLmsController::class, 'takedownAssignment'])->name('lms.assignments.takedown');
        Route::patch('/lms/assignments/{assignment}/restore', [AdminLmsController::class, 'restoreAssignment'])->name('lms.assignments.restore');

        // Enrollment Management
        Route::get('/lms/enrollments/{enrollment}/detail', [AdminLmsController::class, 'enrollmentDetail'])->name('lms.enrollments.detail');
        Route::post('/lms/enrollments', [AdminLmsController::class, 'storeEnrollment'])->name('lms.enrollments.store');
        Route::post('/lms/enrollments/{enrollment}/reset', [AdminLmsController::class, 'resetEnrollment'])->name('lms.enrollments.reset');
        Route::patch('/lms/enrollments/{enrollment}/suspend', [AdminLmsController::class, 'suspendEnrollment'])->name('lms.enrollments.suspend');
        Route::patch('/lms/enrollments/{enrollment}/activate', [AdminLmsController::class, 'activateEnrollment'])->name('lms.enrollments.activate');
        Route::delete('/lms/enrollments/{enrollment}', [AdminLmsController::class, 'deleteEnrollment'])->name('lms.enrollments.destroy');

        // ── Manajemen Pengguna ─────────────────────────────────────────
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.updateStatus');

        // ── Verifikasi Perusahaan ──────────────────────────────────────
        Route::get('/verifications', [CompanyVerificationController::class, 'index'])->name('verifications.index');
        Route::get('/verifications/{user}', [CompanyVerificationController::class, 'show'])->name('verifications.show');
        Route::patch('/verifications/{user}/status', [CompanyVerificationController::class, 'updateStatus'])->name('verifications.updateStatus');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');



        // ── Data Lamaran ──────────────────────────────────────────────
        Route::get('/applications/export', [ApplicationDataController::class, 'export'])->name('applications.export');
        Route::get('/applications', [ApplicationDataController::class, 'index'])->name('applications.index');

        // ── Manajemen Event ───────────────────────────────────────────
        Route::get('/events', [AdminEventModerationController::class, 'index'])->name('events.index');
        Route::patch('/events/{event}/approve', [AdminEventModerationController::class, 'approve'])->name('events.approve');
        Route::patch('/events/{event}/reject', [AdminEventModerationController::class, 'reject'])->name('events.reject');
        Route::delete('/events/{event}', [AdminEventModerationController::class, 'destroy'])->name('events.destroy');
    });

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
