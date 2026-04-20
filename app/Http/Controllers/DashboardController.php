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

        return Inertia::render('Dashboard', [
            'title' => $user->isPerusahaan() ? 'Dashboard Mitra' : 'Dashboard Admin',
            'subtitle' => $user->isPerusahaan()
                ? 'Role perusahaan sudah aktif untuk login dan siap dilanjutkan pada sprint berikutnya.'
                : 'Role admin sudah aktif untuk login dan siap dilanjutkan pada sprint berikutnya.',
            'stats' => [
                ['label' => 'Akun Aktif', 'value' => '1'],
                ['label' => 'Modul Sprint 1', 'value' => 'Stub'],
                ['label' => 'Status', 'value' => 'Coming Soon'],
            ],
            'stubMessage' => 'Fitur lanjutan untuk role ini disiapkan pada sprint berikutnya.',
        ]);
    }
}
