<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Models\Internship;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        // ── User stats ────────────────────────────────────────────────
        $totalUsers      = User::count();
        $totalMahasiswa  = User::where('role', 'mahasiswa')->count();
        $totalPerusahaan = User::where('role', 'perusahaan')->count();
        $activeUsers     = User::where('status', 'active')->count();

        // ── Internship stats ──────────────────────────────────────────
        $totalInternships  = Internship::count();
        $activeInternships = Internship::where('is_published', true)->count();

        // ── Application stats ─────────────────────────────────────────
        $totalApplications    = Application::count();
        $applicationsByStatus = Application::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── Event stats ───────────────────────────────────────────────
        $totalEvents  = Event::count();
        $activeEvents = Event::where('status', 'published')
            ->where('moderation_status', 'approved')
            ->where('date', '>=', now()->toDateString())
            ->count();

        // ── LMS stats ─────────────────────────────────────────────────
        $totalCourses     = LmsCourse::count();
        $publishedCourses = LmsCourse::where('status', 'published')->count();
        $totalEnrollments = LmsEnrollment::count();

        // ── Today's activity counts for banner ────────────────────────
        $todayRegistrations = User::whereDate('created_at', today())->count();
        $todayApplications  = Application::whereDate('created_at', today())->count();

        // ── Stats cards (4 essential KPIs matching Quick Actions) ────────────────────
        $stats = [
            ['label' => 'Total Pengguna',    'value' => (string) $totalUsers,        'icon' => 'users',      'color' => 'blue',    'sub' => $activeUsers . ' aktif'],
            ['label' => 'Perusahaan Mitra',  'value' => (string) $totalPerusahaan,   'icon' => 'building',   'color' => 'emerald', 'sub' => 'mitra terdaftar'],
            ['label' => 'Lowongan Magang',   'value' => (string) $activeInternships, 'icon' => 'briefcase',  'color' => 'purple',  'sub' => $totalInternships . ' total dibuat'],
            ['label' => 'Modul Belajar',     'value' => (string) $publishedCourses,  'icon' => 'book',       'color' => 'teal',    'sub' => $totalEnrollments . ' partisipasi'],
        ];

        // ── Application pipeline ──────────────────────────────────────
        $pipeline = [
            ['label' => 'Menunggu Ulasan', 'value' => $applicationsByStatus['menunggu ulasan'] ?? 0, 'color' => 'blue'],
            ['label' => 'Wawancara',       'value' => $applicationsByStatus['wawancara']       ?? 0, 'color' => 'purple'],
            ['label' => 'Lolos',           'value' => $applicationsByStatus['lolos']            ?? 0, 'color' => 'emerald'],
            ['label' => 'Tidak Lolos',     'value' => $applicationsByStatus['tidak lolos']      ?? 0, 'color' => 'red'],
        ];

        // ── LMS Pipeline ──────────────────────────────────────────────
        $lmsEnrollmentsByStatus = LmsEnrollment::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $lmsGraduated = LmsEnrollment::where('is_graduated', true)->count();
        
        $lmsPipeline = [
            ['label' => 'Sedang Belajar', 'value' => $lmsEnrollmentsByStatus['active'] ?? 0, 'color' => 'cyan'],
            ['label' => 'Selesai Modul',  'value' => $lmsEnrollmentsByStatus['completed'] ?? 0, 'color' => 'teal'],
            ['label' => 'Lulus & Bersertifikat', 'value' => $lmsGraduated, 'color' => 'emerald'],
        ];

        // ── Recent users ──────────────────────────────────────────────
        $recentUsers = User::latest()
            ->limit(6)
            ->get(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'status'     => $u->status,
                'created_at' => optional($u->created_at)->format('d M Y'),
            ]);

        // ── Pending Actions for Quick Action Badges ───────────────────
        $pendingPerusahaan = User::where('role', 'perusahaan')->where('status', 'inactive')->count();
        $pendingLowongan   = Internship::where('moderation_status', 'pending')->count();
        $pendingEvents     = Event::where('moderation_status', 'pending')->count();
        $draftCourses      = LmsCourse::where('status', 'draft')->count();

        $pendingActions = [
            'perusahaan' => $pendingPerusahaan,
            'lowongan'   => $pendingLowongan,
            'events'     => $pendingEvents,
            'lms'        => $draftCourses,
        ];

        // ── System Health (real disk usage) ───────────────────────────
        $diskTotal   = @disk_total_space(base_path()) ?: 1;
        $diskFree    = @disk_free_space(base_path()) ?: 0;
        $diskUsed    = $diskTotal - $diskFree;
        $diskPercent = (int) round(($diskUsed / $diskTotal) * 100);
        $systemHealth = [
            'storage' => $diskPercent,
            'status'  => $diskPercent >= 90 ? 'Kritis' : ($diskPercent >= 75 ? 'Perlu Perhatian' : 'Optimal'),
        ];

        // ── Chart data: Monthly user registrations (last 6 months) ────
        $monthlyRegistrations = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyRegistrations[] = [
                'month' => $date->translatedFormat('M'),
                'count' => User::whereYear('created_at', $date->year)
                               ->whereMonth('created_at', $date->month)
                               ->count(),
            ];
        }

        // ── Chart data: Monthly applications (last 6 months) ──────────
        $monthlyApplications = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyApplications[] = [
                'month' => $date->translatedFormat('M'),
                'count' => Application::whereYear('created_at', $date->year)
                                      ->whereMonth('created_at', $date->month)
                                      ->count(),
            ];
        }

        // ── Real Activity Feed ──────────────────────────────────────────
        $activityLogs = \App\Models\ActivityLog::with('user:id,name,email,role')
            ->latest()
            ->limit(7)
            ->get()
            ->map(function ($log) {
                $color = match ($log->category) {
                    'auth'     => 'blue',
                    'lowongan' => 'emerald',
                    'event'    => 'orange',
                    'lms'      => 'purple',
                    'lamaran'  => 'pink',
                    'admin'    => 'slate',
                    'profile'  => 'teal',
                    default    => 'slate',
                };
                
                $userName = $log->user ? $log->user->name : 'Sistem Otomatis';
                $roleLabel = $log->user ? ucfirst($log->user->role) : 'Auto';
                if ($log->role) {
                    $roleLabel = ucfirst($log->role);
                }

                return [
                    'id'     => $log->id,
                    'name'   => $userName,
                    'role'   => $roleLabel,
                    'action' => $log->action . ($log->description ? ' - ' . \Illuminate\Support\Str::limit($log->description, 50) : ''),
                    'time'   => optional($log->created_at)->diffForHumans(),
                    'color'  => $color,
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats'                => $stats,
            'pipeline'             => $pipeline,
            'lmsPipeline'          => $lmsPipeline,
            'pendingActions'       => $pendingActions,
            'systemHealth'         => $systemHealth,
            'recentUsers'          => $recentUsers,
            'monthlyRegistrations' => $monthlyRegistrations,
            'monthlyApplications'  => $monthlyApplications,
            'activityLogs'         => $activityLogs,
            'todayRegistrations'   => $todayRegistrations,
            'todayApplications'    => $todayApplications,
        ]);
    }
}
