<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user->isMahasiswa()) {
            $profile = $user->mahasiswaProfile;
            $latestInternships = Internship::query()
                ->where('is_published', true)
                ->orderBy('deadline_at')
                ->limit(3)
                ->get()
                ->map(fn (Internship $internship) => [
                    'id' => $internship->id,
                    'title' => $internship->title,
                    'company_name' => $internship->company_name,
                    'location' => $internship->location,
                    'deadline_at' => optional($internship->deadline_at)->format('d M Y'),
                    'requirements' => Str::limit($internship->requirements, 90),
                ]);

            $latestNotifications = $user->notificationsFeed()
                ->limit(3)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read_at' => $notification->read_at?->diffForHumans(),
                ]);

            return Inertia::render('Dashboard', [
                'title' => 'Dashboard Mahasiswa',
                'subtitle' => 'Kelola profil, lamar magang, dan pantau perkembangan karier Anda.',
                'stats' => [
                    ['label' => 'Profil Lengkap', 'value' => $profile ? 'Siap' : 'Belum'],
                    ['label' => 'Lamaran Aktif', 'value' => (string) $user->applications()->count()],
                    ['label' => 'Lowongan Tersedia', 'value' => (string) Internship::query()->where('is_published', true)->count()],
                ],
                'profileSummary' => [
                    'name' => $user->name,
                    'study_program' => $profile?->study_program ?? 'Lengkapi program studi Anda',
                    'department' => $profile?->department ?? 'Jurusan belum diisi',
                    'university' => $profile?->university ?? 'Universitas belum diisi',
                    'phone' => $profile?->phone ?? 'Nomor telepon belum diisi',
                    'location' => $profile?->location ?? 'Lokasi belum diisi',
                    'bio' => $profile?->bio ?? 'Lengkapi profil Anda untuk meningkatkan ketertarikan recruiter.',
                ],
                'latestInternships' => $latestInternships,
                'latestNotifications' => $latestNotifications,
                'stubMessage' => null,
            ]);
        }

        if ($user->isPerusahaan()) {
            // ── Core stats ──────────────────────────────────────────────────
            $companyInternships = $user->internships();

            $activeInternships  = (clone $companyInternships)->where('is_published', true)->count();
            $allInternshipIds   = (clone $companyInternships)->pluck('id');

            // Applications belonging to this company's internships
            $applicationsQuery  = \App\Models\Application::whereIn('internship_id', $allInternshipIds);
            $totalApplicants    = (clone $applicationsQuery)->count();
            $acceptedApplicants = (clone $applicationsQuery)->where('status', 'lolos')->count();
            $interviewCount     = (clone $applicationsQuery)->where('status', 'wawancara')->count();
            $interviewRatio     = $totalApplicants > 0
                ? round(($interviewCount / $totalApplicants) * 100) . '%'
                : '0%';

            // Events belonging to this company
            $eventsQuery   = $user->events();
            $activeEvents  = (clone $eventsQuery)->where('status', 'published')
                                                 ->where('date', '>=', now()->toDateString())
                                                 ->count();
            $totalEventParticipants = (clone $eventsQuery)->withCount('registrations')
                                                           ->get()
                                                           ->sum('registrations_count');

            // ── Pipeline status counts ───────────────────────────────────────
            $statusMap = [
                'menunggu ulasan' => 'waiting',
                'wawancara'       => 'interview',
                'lolos'           => 'passed',
                'tidak lolos'     => 'failed',
            ];
            $pipelineCounts = (clone $applicationsQuery)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $pipeline = [
                ['label' => 'Menunggu Ulasan', 'value' => $pipelineCounts['menunggu ulasan'] ?? 0, 'status' => 'waiting',  'icon' => 'clock', 'color' => 'blue'],
                ['label' => 'Wawancara',        'value' => $pipelineCounts['wawancara']       ?? 0, 'status' => 'interview','icon' => 'chat',  'color' => 'purple'],
                ['label' => 'Lolos',             'value' => $pipelineCounts['lolos']            ?? 0, 'status' => 'passed',  'icon' => 'check', 'color' => 'green'],
                ['label' => 'Tidak Lolos',       'value' => $pipelineCounts['tidak lolos']      ?? 0, 'status' => 'failed',  'icon' => 'x',     'color' => 'red'],
            ];

            // ── Recent applicants (5 latest) ─────────────────────────────────
            $recentApplicants = (clone $applicationsQuery)
                ->with(['user.mahasiswaProfile', 'internship'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($app) {
                    $applicant = $app->user;
                    $profile   = $applicant?->mahasiswaProfile;
                    $name      = $applicant?->name ?? 'Pelamar';
                    $words     = explode(' ', $name);
                    $initials  = strtoupper(
                        (substr($words[0] ?? '', 0, 1)) . (substr($words[1] ?? '', 0, 1))
                    );
                    $statusColorMap = [
                        'menunggu ulasan' => 'blue',
                        'wawancara'       => 'purple',
                        'lolos'           => 'green',
                        'tidak lolos'     => 'red',
                    ];
                    $status = strtolower($app->status ?? 'menunggu ulasan');
                    return [
                        'id'          => $app->id,
                        'name'        => $name,
                        'email'       => $applicant?->email ?? '-',
                        'initials'    => $initials,
                        'position'    => $app->internship?->title ?? '-',
                        'university'  => $profile?->university ?? '-',
                        'major'       => $profile?->department ?? '-',
                        'date'        => optional($app->created_at)->format('d M Y'),
                        'status'      => ucwords($status),
                        'statusColor' => $statusColorMap[$status] ?? 'blue',
                    ];
                });

            // ── Upcoming events (3 soonest) ───────────────────────────────────
            $upcomingEvents = $user->events()
                ->where('status', 'published')
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->limit(3)
                ->withCount('registrations')
                ->get()
                ->map(fn ($event) => [
                    'id'           => $event->id,
                    'date'         => $event->date->format('d'),
                    'month'        => $event->date->translatedFormat('M'),
                    'title'        => $event->title,
                    'time'         => ($event->start_time ? substr($event->start_time, 0, 5) : '–')
                                    . ($event->end_time ? ' - ' . substr($event->end_time, 0, 5) : ''),
                    'location'     => $event->location ?? 'Online',
                    'participants' => $event->registrations_count,
                    'status'       => ucfirst($event->status),
                ]);

            return Inertia::render('Dashboard', [
                'title'    => 'Dashboard Perusahaan',
                'subtitle' => 'Ringkasan performa rekrutmen dan manajemen event Anda.',
                'role'     => 'perusahaan',
                'stats'    => [
                    ['label' => 'LOWONGAN AKTIF',    'value' => (string) $activeInternships,  'icon' => 'briefcase',  'color' => 'blue'],
                    ['label' => 'TOTAL PELAMAR',     'value' => (string) $totalApplicants,    'trend' => 'dari semua lowongan', 'trendUp' => true, 'icon' => 'users', 'color' => 'green'],
                    ['label' => 'RASIO WAWANCARA',   'value' => $interviewRatio,              'icon' => 'user-check', 'color' => 'purple'],
                    ['label' => 'PELAMAR DITERIMA',  'value' => (string) $acceptedApplicants, 'icon' => 'check-circle','color' => 'emerald'],
                    ['label' => 'EVENT AKTIF',        'value' => (string) $activeEvents,       'icon' => 'calendar',   'color' => 'blue'],
                    ['label' => 'PESERTA EVENT',      'value' => (string) $totalEventParticipants, 'icon' => 'ticket', 'color' => 'orange'],
                ],
                'pipeline'         => $pipeline,
                'recentApplicants' => $recentApplicants,
                'upcomingEvents'   => $upcomingEvents,
                'notifications'    => [],
                'profileSummary'   => [
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'status' => 'Terverifikasi (Mitra Resmi)',
                    'bio'    => 'Portal manajemen rekrutmen B2B SIKARA.',
                ],
                'stubMessage' => null,
            ]);
        }

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard Admin',
            'subtitle' => 'Role admin sudah aktif untuk login dan siap dilanjutkan pada sprint berikutnya.',
            'role' => 'admin',
            'stats' => [
                ['label' => 'Akun Aktif', 'value' => '1'],
                ['label' => 'Modul Sprint 1', 'value' => 'Stub'],
                ['label' => 'Status', 'value' => 'Coming Soon'],
            ],
            'stubMessage' => 'Fitur lanjutan untuk role ini disiapkan pada sprint berikutnya.',
        ]);
    }
}
