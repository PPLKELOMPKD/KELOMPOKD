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

        // Tab 2 & 3 kosong untuk saat ini
        $enrollments = ['data' => []];
        $activityLogs = ['data' => []];

        return Inertia::render('Admin/Lms/Index', [
            'stats' => $stats,
            'users' => $users,
            'enrollments' => $enrollments,
            'activityLogs' => $activityLogs,
            'filters' => [
                'search_pengguna' => $searchPengguna,
                'filter_role' => $filterRole,
                'search_enrollment' => '',
                'filter_enrollment' => 'all',
                'tab' => $request->query('tab', 'pengguna'),
            ]
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
}
