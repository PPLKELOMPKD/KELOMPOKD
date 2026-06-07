<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LmsCourse;
use App\Models\LmsChapter;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsAssignment;
use App\Models\LmsEnrollment;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\LmsProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminLmsController extends Controller
{
    /**
     * Tampilkan dashboard pemantauan LMS
     */
    public function index(Request $request): Response
    {
        // 1. Auto-seeding jika data monitoring kosong
        if (LmsCourse::where('slug', 'fundamental-ui-ux-design')->doesntExist()) {
            try {
                $seeder = new \Database\Seeders\LmsMonitorSeeder();
                $seeder->run();
            } catch (\Exception $e) {
                // Silently catch seeder errors to avoid breaking the page
            }
        }

        if (ActivityLog::whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi'])->doesntExist()) {
            try {
                $seeder2 = new \Database\Seeders\LmsActivityLogSeeder();
                $seeder2->run();
            } catch (\Exception $e) {
                // Silently catch seeder errors
            }
        }

        $progressService = app(LmsProgressService::class);

        // 2. Banner Stats
        $stats = [
            'total_course' => LmsCourse::count(),
            'total_modul' => LmsLesson::count(),
            'total_quiz' => LmsQuiz::count(),
            'total_enrollment' => LmsEnrollment::count(),
            'total_course_aktif' => LmsCourse::where('moderation_status', 'approved')->count(),
            'total_course_takedown' => LmsCourse::where('moderation_status', 'takedown')->count(),
        ];

        // 3. Tab State
        $activeTab = $request->query('tab', 'moderasi_course');

        // TAB 1: MODERASI COURSE
        $searchCourse = $request->query('search_course', '');
        $statusCourse = $request->query('status_course', 'all');

        $coursesQuery = LmsCourse::with(['company'])->latest();

        if ($searchCourse) {
            $coursesQuery->where(function ($q) use ($searchCourse) {
                $q->where('title', 'like', "%{$searchCourse}%")
                  ->orWhere('provider', 'like', "%{$searchCourse}%")
                  ->orWhereHas('company', function ($cq) use ($searchCourse) {
                      $cq->where('name', 'like', "%{$searchCourse}%");
                  });
            });
        }

        if ($statusCourse && $statusCourse !== 'all') {
            $coursesQuery->where('moderation_status', $statusCourse);
        }

        $coursesRaw = $coursesQuery->paginate(10, ['*'], 'courses_page')->withQueryString();

        $courses = $coursesRaw->through(function ($c) {
            // Count components
            $chapters = LmsChapter::where('course_id', $c->id)->get();
            $chapterIds = $chapters->pluck('id');
            $lessonsCount = LmsLesson::whereIn('chapter_id', $chapterIds)->count();
            $participantsCount = LmsEnrollment::where('course_id', $c->id)->count();

            return [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'company_name' => $c->company->name ?? $c->provider ?? '-',
                'lessons_count' => $lessonsCount,
                'participants_count' => $participantsCount,
                'moderation_status' => $c->moderation_status ?? 'pending',
                'rejection_reason' => $c->rejection_reason,
                'created_at' => $c->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Tab 1 Badge counts
        $courseCounts = [
            'all' => LmsCourse::count(),
            'pending' => LmsCourse::where('moderation_status', 'pending')->count(),
            'approved' => LmsCourse::where('moderation_status', 'approved')->count(),
            'rejected' => LmsCourse::where('moderation_status', 'rejected')->count(),
            'takedown' => LmsCourse::where('moderation_status', 'takedown')->count(),
        ];

        // TAB 2: MODERASI KONTEN LMS
        $tab2Type = $request->query('tab2_type', 'modul');
        $searchContent = $request->query('search_content', '');

        $moduls = null;
        $quizzes = null;
        $assignments = null;

        if ($tab2Type === 'modul') {
            $modulQuery = LmsLesson::with(['chapter.course.company'])->latest();
            if ($searchContent) {
                $modulQuery->where('title', 'like', "%{$searchContent}%")
                    ->orWhereHas('chapter.course', function ($cq) use ($searchContent) {
                        $cq->where('title', 'like', "%{$searchContent}%");
                    });
            }
            $moduls = $modulQuery->paginate(10, ['*'], 'moduls_page')->withQueryString();
        } elseif ($tab2Type === 'quiz') {
            $quizQuery = LmsQuiz::with(['chapter.course'])->withCount('questions')->latest();
            if ($searchContent) {
                $quizQuery->where('title', 'like', "%{$searchContent}%")
                    ->orWhereHas('chapter.course', function ($cq) use ($searchContent) {
                        $cq->where('title', 'like', "%{$searchContent}%");
                    });
            }
            $quizzes = $quizQuery->paginate(10, ['*'], 'quizzes_page')->withQueryString();
        } else {
            $assignmentQuery = LmsAssignment::with(['chapter.course'])->latest();
            if ($searchContent) {
                $assignmentQuery->where('title', 'like', "%{$searchContent}%")
                    ->orWhereHas('chapter.course', function ($cq) use ($searchContent) {
                        $cq->where('title', 'like', "%{$searchContent}%");
                    });
            }
            $assignments = $assignmentQuery->paginate(10, ['*'], 'assignments_page')->withQueryString();
        }

        // TAB 3: KELOLA ENROLLMENT
        $searchEnrollment = $request->query('search_enrollment', '');
        $courseEnrollment = $request->query('course_enrollment', 'all');
        $statusEnrollment = $request->query('status_enrollment', 'all');

        $enrollmentsQuery = LmsEnrollment::with(['student', 'course.company']);

        if ($searchEnrollment) {
            $enrollmentsQuery->where(function ($q) use ($searchEnrollment) {
                $q->whereHas('student', function ($sq) use ($searchEnrollment) {
                    $sq->where('name', 'like', "%{$searchEnrollment}%");
                })->orWhereHas('course', function ($cq) use ($searchEnrollment) {
                    $cq->where('title', 'like', "%{$searchEnrollment}%");
                });
            });
        }

        if ($courseEnrollment && $courseEnrollment !== 'all') {
            $enrollmentsQuery->where('course_id', $courseEnrollment);
        }

        if ($statusEnrollment && $statusEnrollment !== 'all') {
            if ($statusEnrollment === 'selesai') {
                $enrollmentsQuery->where('is_graduated', true);
            } elseif ($statusEnrollment === 'suspend') {
                $enrollmentsQuery->where('status', 'suspended');
            } else {
                $enrollmentsQuery->where('status', '!=', 'suspended')->where('is_graduated', false);
            }
        }

        $enrollmentsRaw = $enrollmentsQuery->latest()->paginate(10, ['*'], 'enrollments_page')->withQueryString();

        $enrollments = $enrollmentsRaw->through(function ($en) use ($progressService) {
            $progress = 0;
            try {
                $progress = $progressService->courseProgress($en);
            } catch (\Exception $e) {}

            $statusText = 'Aktif';
            if ($en->status === 'suspended') {
                $statusText = 'Suspend';
            } elseif ($en->is_graduated) {
                $statusText = 'Selesai';
            }

            return [
                'id' => $en->id,
                'student_name' => $en->student->name ?? '-',
                'student_email' => $en->student->email ?? '-',
                'course_title' => $en->course->title ?? '-',
                'progress' => $progress,
                'status' => $statusText,
                'enrolled_at' => $en->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Dropdowns for Tambah Enrollment Modal
        $allStudents = User::where('role', User::ROLE_MAHASISWA)->where('status', User::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);
        $allCourses = LmsCourse::where('moderation_status', 'approved')->orderBy('title')->get(['id', 'title']);

        // TAB 4: MONITORING LMS
        $searchActivity = $request->query('search_activity', '');
        $roleActivity = $request->query('role_activity', 'all');

        $monitoringStats = [
            'enrollment_today' => LmsEnrollment::whereDate('created_at', today())->count(),
            'course_today' => LmsCourse::whereDate('created_at', today())->count(),
            'modul_today' => LmsLesson::whereDate('created_at', today())->count(),
            'quiz_today' => LmsQuiz::whereDate('created_at', today())->count(),
            'assignment_today' => LmsAssignment::whereDate('created_at', today())->count(),
        ];

        $activityLogsQuery = ActivityLog::with('user')
            ->whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi']);

        if ($roleActivity && $roleActivity !== 'all') {
            $activityLogsQuery->where('role', $roleActivity);
        }

        if ($searchActivity) {
            $activityLogsQuery->where(function ($q) use ($searchActivity) {
                $q->where('action', 'like', "%{$searchActivity}%")
                  ->orWhere('description', 'like', "%{$searchActivity}%")
                  ->orWhereHas('user', function ($uq) use ($searchActivity) {
                      $uq->where('name', 'like', "%{$searchActivity}%");
                  });
            });
        }

        $activityLogsRaw = $activityLogsQuery->latest()->paginate(15, ['*'], 'logs_page')->withQueryString();
        $activityLogs = $activityLogsRaw->through(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user->name ?? 'System',
                'role' => $log->role,
                'action' => $log->action,
                'category' => $log->category,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('Admin/Lms/Index', [
            'stats' => $stats,
            'activeTab' => $activeTab,
            'courses' => $courses,
            'courseCounts' => $courseCounts,
            'moduls' => $moduls,
            'quizzes' => $quizzes,
            'assignments' => $assignments,
            'enrollments' => $enrollments,
            'students' => $allStudents,
            'allCourses' => $allCourses,
            'monitoringStats' => $monitoringStats,
            'activityLogs' => $activityLogs,
            'filters' => [
                'tab' => $activeTab,
                'search_course' => $searchCourse,
                'status_course' => $statusCourse,
                'tab2_type' => $tab2Type,
                'search_content' => $searchContent,
                'search_enrollment' => $searchEnrollment,
                'course_enrollment' => $courseEnrollment,
                'status_enrollment' => $statusEnrollment,
                'search_activity' => $searchActivity,
                'role_activity' => $roleActivity,
            ],
        ]);
    }

    /**
     * Tampilkan detail pengguna LMS
     */
    public function userDetail(User $user): JsonResponse
    {
        $progressService = app(LmsProgressService::class);
        $riwayat = [];
        $totalEnrollment = 0;
        $courseSelesai = 0;
        $avgProgress = 0;

        if ($user->role === User::ROLE_MAHASISWA) {
            $enrollments = $user->lmsEnrollments()->with(['course.company'])->get();
            $totalEnrollment = $enrollments->count();
            $courseSelesai = $enrollments->where('is_graduated', true)->count();
            
            $totalProgress = 0;
            foreach ($enrollments as $en) {
                $progress = $progressService->courseProgress($en);
                $totalProgress += $progress;

                $status = 'Aktif';
                if ($en->status === 'suspended') {
                    $status = 'Suspend';
                } elseif ($en->is_graduated) {
                    $status = 'Selesai';
                }

                $riwayat[] = [
                    'course_title' => $en->course->title ?? 'N/A',
                    'instructor' => $en->course->company->name ?? 'N/A',
                    'progress' => $progress,
                    'status' => $status,
                ];
            }
            $avgProgress = $totalEnrollment > 0 ? (int) round($totalProgress / $totalEnrollment) : 0;
        } else {
            // Perusahaan
            $courses = LmsCourse::where('company_id', $user->id)->get();
            $totalEnrollment = 0;
            foreach ($courses as $c) {
                $totalEnrollment += $c->enrollments()->count();
            }
            $courseSelesai = $courses->where('status', 'published')->count();

            foreach ($courses as $c) {
                $riwayat[] = [
                    'course_title' => $c->title,
                    'instructor' => $user->name,
                    'progress' => $c->enrollments()->count() . ' Peserta',
                    'status' => $c->status === 'published' ? 'Published' : 'Draft',
                ];
            }
        }

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->format('d M Y'),
                'status' => $user->status,
            ],
            'stats' => [
                'total_enrollment' => $totalEnrollment,
                'course_selesai' => $courseSelesai,
                'avg_progress' => $avgProgress,
            ],
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Tangguhkan (suspend) akun pengguna LMS
     */
    public function suspendUser(User $user)
    {
        $user->update(['status' => User::STATUS_BANNED]);
        ActivityLogger::log('Suspend Pengguna LMS', "Menangguhkan pengguna {$user->name} ({$user->email})", 'moderasi');
        return back()->with('success', "Akun \"{$user->name}\" berhasil ditangguhkan.");
    }

    /**
     * Aktifkan kembali akun pengguna LMS
     */
    public function activateUser(User $user)
    {
        $user->update(['status' => User::STATUS_ACTIVE]);
        ActivityLogger::log('Aktifkan Pengguna LMS', "Mengaktifkan pengguna {$user->name} ({$user->email})", 'moderasi');
        return back()->with('success', "Akun \"{$user->name}\" berhasil diaktifkan kembali.");
    }

    /**
     * Hapus (soft delete) akun pengguna LMS
     */
    public function deleteUser(User $user)
    {
        $userName = $user->name;
        $user->delete();
        ActivityLogger::log('Hapus Pengguna LMS', "Menghapus pengguna {$userName}", 'moderasi');
        return back()->with('success', "Akun \"{$userName}\" berhasil dihapus dari sistem.");
    }

    /**
     * Tampilkan detail enrollment
     */
    public function enrollmentDetail(LmsEnrollment $enrollment): JsonResponse
    {
        $enrollment->load(['student', 'course.company', 'course.chapters.lessons', 'course.chapters.quiz', 'course.chapters.assignments']);
        $progressService = app(LmsProgressService::class);
        $progress = 0;
        try {
            $progress = $progressService->courseProgress($enrollment);
        } catch (\Exception $e) {}
        
        // Count totals
        $totalLessons = 0;
        $totalQuizzes = 0;
        $totalAssignments = 0;

        foreach ($enrollment->course->chapters as $ch) {
            $totalLessons += $ch->lessons->count();
            if ($ch->quiz) $totalQuizzes++;
            $totalAssignments += $ch->assignments->count();
        }

        $lessonDone = $enrollment->lessonCompletions()->count() . ' / ' . $totalLessons;
        $quizDone = $enrollment->quizAttempts()->where('passed', true)->count() . ' / ' . $totalQuizzes;
        $assignmentDone = $enrollment->assignmentSubmissions()->whereNotNull('score')->count() . ' / ' . $totalAssignments;

        $status = 'Aktif';
        if ($enrollment->status === 'suspended') {
            $status = 'Suspend';
        } elseif ($enrollment->is_graduated) {
            $status = 'Selesai';
        }

        return response()->json([
            'id' => $enrollment->id,
            'student_name' => $enrollment->student->name ?? '-',
            'instructor_name' => $enrollment->course->company->name ?? '-',
            'course_title' => $enrollment->course->title ?? '-',
            'progress' => $progress,
            'lesson_done' => $lessonDone,
            'quiz_done' => $quizDone,
            'assignment_done' => $assignmentDone,
            'final_grade' => '-',
            'status' => $status,
        ]);
    }

    /**
     * Reset progres belajar enrollment peserta
     */
    public function resetEnrollment(LmsEnrollment $enrollment)
    {
        $enrollment->lessonCompletions()->delete();
        $enrollment->quizAttempts()->delete();
        $enrollment->chapterCompletions()->delete();
        $enrollment->assignmentSubmissions()->delete();
        
        $enrollment->update([
            'is_graduated' => false,
            'completed_at' => null,
            'status' => 'active',
        ]);

        ActivityLogger::log(
            'Reset Progress',
            "Mereset progress course '{$enrollment->course->title}' untuk user '{$enrollment->student->name}'",
            'moderasi'
        );

        return back()->with('success', 'Progress belajar peserta berhasil direset.');
    }

    /**
     * Hapus enrollment peserta dari kursus LMS
     */
    public function deleteEnrollment(LmsEnrollment $enrollment)
    {
        $studentName = $enrollment->student->name ?? 'Unknown';
        $courseTitle = $enrollment->course->title ?? 'Unknown';

        $enrollment->lessonCompletions()->delete();
        $enrollment->quizAttempts()->delete();
        $enrollment->chapterCompletions()->delete();
        $enrollment->assignmentSubmissions()->delete();
        $enrollment->delete();

        ActivityLogger::log(
            'Hapus Enrollment',
            "Menghapus enrollment course '{$courseTitle}' untuk user '{$studentName}'",
            'moderasi'
        );

        return back()->with('success', 'Enrollment peserta berhasil dihapus.');
    }

    // ─── NEW ACTIONS FOR COURSE MODERATION ───

    public function approveCourse(LmsCourse $course)
    {
        $course->update([
            'moderation_status' => 'approved',
            'rejection_reason' => null
        ]);

        ActivityLogger::log(
            'Approve Course',
            "Menyetujui course '{$course->title}' untuk dipublikasikan",
            'moderasi'
        );

        return back()->with('success', "Course '{$course->title}' berhasil disetujui.");
    }

    public function rejectCourse(Request $request, LmsCourse $course)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        $course->update([
            'moderation_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        ActivityLogger::log(
            'Reject Course',
            "Menolak course '{$course->title}'. Alasan: {$request->rejection_reason}",
            'moderasi'
        );

        return back()->with('success', "Course '{$course->title}' ditolak.");
    }

    public function takedownCourse(Request $request, LmsCourse $course)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        $course->update([
            'moderation_status' => 'takedown',
            'rejection_reason' => $request->rejection_reason
        ]);

        ActivityLogger::log(
            'Takedown Course',
            "Menurunkan (takedown) course '{$course->title}'. Alasan: {$request->rejection_reason}",
            'moderasi'
        );

        return back()->with('success', "Course '{$course->title}' berhasil diturunkan.");
    }

    public function restoreCourse(LmsCourse $course)
    {
        $course->update([
            'moderation_status' => 'approved',
            'rejection_reason' => null
        ]);

        ActivityLogger::log(
            'Restore Course',
            "Memulihkan course '{$course->title}' ke status disetujui",
            'moderasi'
        );

        return back()->with('success', "Course '{$course->title}' berhasil dipulihkan.");
    }

    // ─── NEW ACTIONS FOR CONTENT MODERATION ───

    public function takedownLesson(LmsLesson $lesson)
    {
        $lesson->update(['status' => 'takedown']);

        ActivityLogger::log(
            'Takedown Modul',
            "Menurunkan (takedown) modul/lesson '{$lesson->title}' pada course '{$lesson->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Modul '{$lesson->title}' berhasil diturunkan.");
    }

    public function restoreLesson(LmsLesson $lesson)
    {
        $lesson->update(['status' => 'active']);

        ActivityLogger::log(
            'Restore Modul',
            "Memulihkan modul/lesson '{$lesson->title}' pada course '{$lesson->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Modul '{$lesson->title}' berhasil dipulihkan.");
    }

    public function takedownQuiz(LmsQuiz $quiz)
    {
        $quiz->update(['status' => 'takedown']);

        ActivityLogger::log(
            'Takedown Quiz',
            "Menurunkan (takedown) quiz '{$quiz->title}' pada course '{$quiz->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Quiz '{$quiz->title}' berhasil diturunkan.");
    }

    public function restoreQuiz(LmsQuiz $quiz)
    {
        $quiz->update(['status' => 'active']);

        ActivityLogger::log(
            'Restore Quiz',
            "Memulihkan quiz '{$quiz->title}' pada course '{$quiz->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Quiz '{$quiz->title}' berhasil dipulihkan.");
    }

    public function takedownAssignment(LmsAssignment $assignment)
    {
        $assignment->update(['status' => 'takedown']);

        ActivityLogger::log(
            'Takedown Assignment',
            "Menurunkan (takedown) tugas '{$assignment->title}' pada course '{$assignment->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Tugas '{$assignment->title}' berhasil diturunkan.");
    }

    public function restoreAssignment(LmsAssignment $assignment)
    {
        $assignment->update(['status' => 'active']);

        ActivityLogger::log(
            'Restore Assignment',
            "Memulihkan tugas '{$assignment->title}' pada course '{$assignment->chapter->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Tugas '{$assignment->title}' berhasil dipulihkan.");
    }

    // ─── NEW ACTIONS FOR ENROLLMENT ───

    public function storeEnrollment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:lms_courses,id',
        ]);

        // Check unique
        $exists = LmsEnrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Peserta tersebut sudah terdaftar pada course ini.');
        }

        $enrollment = LmsEnrollment::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $student = User::find($request->student_id);
        $course = LmsCourse::find($request->course_id);

        ActivityLogger::log(
            'Tambah Enrollment',
            "Mendaftarkan mahasiswa '{$student->name}' ke dalam course '{$course->title}' oleh Admin",
            'moderasi'
        );

        return back()->with('success', "Mahasiswa '{$student->name}' berhasil didaftarkan ke course '{$course->title}'.");
    }

    public function suspendEnrollment(LmsEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'suspended']);

        ActivityLogger::log(
            'Suspend Enrollment',
            "Menangguhkan pendaftaran '{$enrollment->student->name}' pada course '{$enrollment->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Pendaftaran peserta berhasil ditangguhkan.");
    }

    public function activateEnrollment(LmsEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'active']);

        ActivityLogger::log(
            'Aktifkan Enrollment',
            "Mengaktifkan kembali pendaftaran '{$enrollment->student->name}' pada course '{$enrollment->course->title}'",
            'moderasi'
        );

        return back()->with('success', "Pendaftaran peserta berhasil diaktifkan kembali.");
    }
}
