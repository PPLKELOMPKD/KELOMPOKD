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
            ->where('date', '>=', now()->toDateString())
            ->count();

        // ── LMS stats ─────────────────────────────────────────────────
        $totalCourses     = LmsCourse::count();
        $publishedCourses = LmsCourse::where('status', 'published')->count();
        $totalEnrollments = LmsEnrollment::count();

        // ── Stats cards ───────────────────────────────────────────────
        $stats = [
            ['label' => 'Total Pengguna',      'value' => (string) $totalUsers,        'icon' => 'users',      'color' => 'blue',    'sub' => $activeUsers . ' aktif'],
            ['label' => 'Mahasiswa',            'value' => (string) $totalMahasiswa,    'icon' => 'graduation', 'color' => 'indigo',  'sub' => 'peserta terdaftar'],
            ['label' => 'Perusahaan Mitra',     'value' => (string) $totalPerusahaan,   'icon' => 'building',   'color' => 'emerald', 'sub' => 'mitra terdaftar'],
            ['label' => 'Lowongan Aktif',       'value' => (string) $activeInternships, 'icon' => 'briefcase',  'color' => 'purple',  'sub' => $totalInternships . ' total'],
            ['label' => 'Total Lamaran',        'value' => (string) $totalApplications, 'icon' => 'inbox',      'color' => 'orange',  'sub' => ($applicationsByStatus['lolos'] ?? 0) . ' diterima'],
            ['label' => 'Event Mendatang',      'value' => (string) $activeEvents,      'icon' => 'calendar',   'color' => 'pink',    'sub' => $totalEvents . ' total event'],
            ['label' => 'Kursus LMS',           'value' => (string) $publishedCourses,  'icon' => 'book',       'color' => 'teal',    'sub' => $totalCourses . ' total kursus'],
            ['label' => 'Enrollment LMS',       'value' => (string) $totalEnrollments,  'icon' => 'chart',      'color' => 'cyan',    'sub' => 'peserta aktif kursus'],
        ];

        // ── Application pipeline ──────────────────────────────────────
        $pipeline = [
            ['label' => 'Menunggu Ulasan', 'value' => $applicationsByStatus['menunggu ulasan'] ?? 0, 'icon' => 'clock',  'color' => 'blue'],
            ['label' => 'Wawancara',       'value' => $applicationsByStatus['wawancara']       ?? 0, 'icon' => 'chat',   'color' => 'purple'],
            ['label' => 'Lolos',           'value' => $applicationsByStatus['lolos']            ?? 0, 'icon' => 'check',  'color' => 'emerald'],
            ['label' => 'Tidak Lolos',     'value' => $applicationsByStatus['tidak lolos']      ?? 0, 'icon' => 'x',      'color' => 'red'],
        ];

        // ── Recent users ──────────────────────────────────────────────
        $recentUsers = User::latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'status'     => $u->status,
                'created_at' => optional($u->created_at)->format('d M Y'),
            ]);

        // ── Recent applications ────────────────────────────────────────
        $recentApplications = Application::with(['user', 'internship'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($app) {
                $statusColorMap = [
                    'menunggu ulasan' => 'blue',
                    'wawancara'       => 'purple',
                    'lolos'           => 'emerald',
                    'tidak lolos'     => 'red',
                ];
                $status = strtolower($app->status ?? 'menunggu ulasan');
                return [
                    'id'          => $app->id,
                    'name'        => $app->user?->name ?? 'Pelamar',
                    'email'       => $app->user?->email ?? '-',
                    'position'    => $app->internship?->title ?? '-',
                    'company'     => $app->internship?->company_name ?? '-',
                    'date'        => optional($app->created_at)->format('d M Y'),
                    'status'      => ucwords($status),
                    'statusRaw'   => $status,
                    'statusColor' => $statusColorMap[$status] ?? 'blue',
                ];
            });

        // ── Platform modules overview ──────────────────────────────────
        $platformModules = [
            ['name' => 'Kelola Pengguna',   'desc' => 'Manajemen akun mahasiswa & perusahaan', 'icon' => 'users',     'color' => 'blue',   'count' => $totalUsers,        'unit' => 'pengguna'],
            ['name' => 'Verifikasi Mitra',  'desc' => 'Review & verifikasi akun perusahaan',   'icon' => 'building',  'color' => 'emerald','count' => $totalPerusahaan,   'unit' => 'perusahaan'],
            ['name' => 'Moderasi Lowongan', 'desc' => 'Pantau & moderasi lowongan magang',     'icon' => 'briefcase', 'color' => 'purple', 'count' => $totalInternships,  'unit' => 'lowongan'],
            ['name' => 'Manajemen Event',   'desc' => 'Pantau semua event & webinar platform', 'icon' => 'calendar',  'color' => 'orange', 'count' => $totalEvents,       'unit' => 'event'],
            ['name' => 'Pantau LMS',        'desc' => 'Monitor kursus & progres mahasiswa',    'icon' => 'book',      'color' => 'teal',   'count' => $totalCourses,      'unit' => 'kursus'],
            ['name' => 'Data Lamaran',      'desc' => 'Pantau seluruh proses rekrutmen',       'icon' => 'inbox',     'color' => 'pink',   'count' => $totalApplications, 'unit' => 'lamaran'],
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats'              => $stats,
            'pipeline'           => $pipeline,
            'recentUsers'        => $recentUsers,
            'recentApplications' => $recentApplications,
            'platformModules'    => $platformModules,
        ]);
    }
}
