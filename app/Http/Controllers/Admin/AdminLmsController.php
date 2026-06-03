<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LmsCourse;
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

        $progressService = app(LmsProgressService::class);

        // 2. Banner Stats
        $totalCourse = LmsCourse::count();
        $totalMahasiswaAktif = User::where('role', User::ROLE_MAHASISWA)->where('status', User::STATUS_ACTIVE)->count();
        $totalEnrollment = LmsEnrollment::count();
        
        $completedEnrollments = LmsEnrollment::where('is_graduated', true)->count();
        $completionRate = $totalEnrollment > 0 ? (int) round(($completedEnrollments / $totalEnrollment) * 100) : 0;

        $stats = [
            'total_course' => $totalCourse,
            'total_mahasiswa_aktif' => $totalMahasiswaAktif,
            'total_enrollment' => $totalEnrollment,
            'completion_rate' => $completionRate,
        ];

        // 3. Tab 1: Pengguna LMS
        $searchPengguna = $request->query('search_pengguna', '');
        $filterRole = $request->query('filter_role', 'all');

        $usersQuery = User::whereIn('role', [User::ROLE_MAHASISWA, User::ROLE_PERUSAHAAN]);

        if ($searchPengguna) {
            $usersQuery->where(function ($q) use ($searchPengguna) {
                $q->where('name', 'like', "%{$searchPengguna}%")
                  ->orWhere('email', 'like', "%{$searchPengguna}%");
            });
        }

        if (in_array($filterRole, [User::ROLE_MAHASISWA, User::ROLE_PERUSAHAAN])) {
            $usersQuery->where('role', $filterRole);
        }

        $usersRaw = $usersQuery->latest()->paginate(10, ['*'], 'users_page')->withQueryString();

        // Transform Users
        $users = $usersRaw->through(function ($u) {
            if ($u->role === User::ROLE_MAHASISWA) {
                $totalCourse = $u->lmsEnrollments()->count();
                $courseSelesai = $u->lmsEnrollments()->where('is_graduated', true)->count();
            } else {
                $totalCourse = LmsCourse::where('company_id', $u->id)->count();
                $courseSelesai = LmsCourse::where('company_id', $u->id)->where('status', 'published')->count();
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'total_course' => $totalCourse,
                'course_selesai' => $courseSelesai,
                'status' => $u->status,
                'created_at' => $u->created_at->format('d M Y'),
            ];
        });

        // Tab 2: Enrollment
        $searchEnrollment = $request->query('search_enrollment', '');
        $filterEnrollment = $request->query('filter_enrollment', 'all');

        $enrollmentsQuery = LmsEnrollment::with(['student', 'course.company']);

        if ($searchEnrollment) {
            $enrollmentsQuery->whereHas('student', function ($q) use ($searchEnrollment) {
                $q->where('name', 'like', "%{$searchEnrollment}%")
                  ->orWhere('email', 'like', "%{$searchEnrollment}%");
            })->orWhereHas('course', function ($q) use ($searchEnrollment) {
                $q->where('title', 'like', "%{$searchEnrollment}%");
            });
        }

        if (in_array($filterEnrollment, ['active', 'completed'])) {
            $enrollmentsQuery->where('is_graduated', $filterEnrollment === 'completed');
        }

        $enrollmentsRaw = $enrollmentsQuery->latest()->paginate(10, ['*'], 'enrollments_page')
            ->withQueryString();

        $enrollments = $enrollmentsRaw->through(function ($en) use ($progressService) {
            $progress = 0;
            try {
                $progress = $progressService->courseProgress($en);
            } catch (\Exception $e) {}

            return [
                'id' => $en->id,
                'student_name' => $en->student->name ?? '-',
                'student_email' => $en->student->email ?? '-',
                'course_title' => $en->course->title ?? '-',
                'instructor_name' => $en->course->company->name ?? '-',
                'progress' => $progress,
                'status' => $en->status === 'rejected' ? 'Dibatalkan' : ($en->is_graduated ? 'Selesai' : 'Aktif'),
                'enrolled_at' => $en->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Tab 3: Activity Logs
        $searchActivity = $request->query('search_activity', '');
        $filterActivityRole = $request->query('filter_role', 'all');
        $filterActivityCategory = $request->query('filter_category', 'all');

        $activityLogsQuery = ActivityLog::query()
            ->byRole($filterActivityRole)
            ->byCategory($filterActivityCategory)
            ->search($searchActivity)
            ->latest();

        $activityLogsRaw = $activityLogsQuery->paginate(10, ['*'], 'activity_logs_page')
            ->withQueryString();

        $activityLogs = $activityLogsRaw->through(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user->name ?? 'System',
                'role' => $log->role,
                'action' => $log->action,
                'category' => $log->category,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('d M Y H:i'),
            ];
        });

        return Inertia::render('Admin/Lms/Index', [
            'stats' => $stats,
            'users' => $users,
            'enrollments' => $enrollments,
            'activityLogs' => $activityLogs,
            'filters' => [
                'search_pengguna' => $searchPengguna,
                'filter_role' => $filterRole,
                'search_enrollment' => $searchEnrollment,
                'filter_enrollment' => $filterEnrollment,
                'search_activity' => $searchActivity,
                'filter_category' => $filterActivityCategory,
                'tab' => $request->query('tab', 'pengguna'),
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
                if ($en->status === 'rejected') {
                    $status = 'Dibatalkan';
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
            $courseSelesai = $courses->where('status', 'published')->count(); // Dianggap course aktif yang dirilis
            $avgProgress = 100; // Standar default untuk instruktur

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

        return back()->with('success', "Akun \"{$user->name}\" berhasil ditangguhkan.");
    }

    /**
     * Aktifkan kembali akun pengguna LMS
     */
    public function activateUser(User $user)
    {
        $user->update(['status' => User::STATUS_ACTIVE]);

        return back()->with('success', "Akun \"{$user->name}\" berhasil diaktifkan kembali.");
    }

    /**
     * Hapus (soft delete) akun pengguna LMS
     */
    public function deleteUser(User $user)
    {
        $userName = $user->name;
        $user->delete();

        return back()->with('success', "Akun \"{$userName}\" berhasil dihapus dari sistem.");
    }

    /**
     * Tampilkan detail enrollment
     */
    public function enrollmentDetail(LmsEnrollment $enrollment): JsonResponse
    {
        $enrollment->load(['student', 'course.company', 'course.lessons', 'course.quizzes', 'course.assignments']);
        $progressService = app(LmsProgressService::class);
        $progress = 0;
        try {
            $progress = $progressService->courseProgress($enrollment);
        } catch (\Exception $e) {}
        
        $lessonDone = $enrollment->lessonCompletions()->count() . ' / ' . ($enrollment->course->lessons ? $enrollment->course->lessons->count() : 0);
        $quizDone = $enrollment->quizAttempts()->where('is_passed', true)->count() . ' / ' . ($enrollment->course->quizzes ? $enrollment->course->quizzes->count() : 0);
        $assignmentDone = $enrollment->assignmentSubmissions()->whereNotNull('grade')->count() . ' / ' . ($enrollment->course->assignments ? $enrollment->course->assignments->count() : 0);

        $status = 'Aktif';
        if ($enrollment->status === 'rejected') {
            $status = 'Dibatalkan';
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

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log(
                auth()->user(),
                'Reset Progress',
                'LMS',
                "Mereset progress course '{$enrollment->course->title}' untuk user '{$enrollment->student->name}'"
            );
        }

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

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log(
                auth()->user(),
                'Hapus Enrollment',
                'LMS',
                "Menghapus enrollment course '{$courseTitle}' untuk user '{$studentName}'"
            );
        }

        return back()->with('success', 'Enrollment peserta berhasil dihapus.');
    }
}
