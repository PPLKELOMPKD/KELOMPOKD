<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PesertaController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = Auth::user();
        $data = [];

        if ($user && $user->isMahasiswa()) {
            $profile = $user->mahasiswaProfile;

            $data['profileSummary'] = [
                'name' => $user->name,
                'study_program' => $profile?->study_program ?? 'Lengkapi program studi Anda',
                'department' => $profile?->department ?? 'Jurusan belum diisi',
                'university' => $profile?->university ?? 'Universitas belum diisi',
                'phone' => $profile?->phone ?? 'Nomor telepon belum diisi',
                'location' => $profile?->location ?? 'Lokasi belum diisi',
                'bio' => $profile?->bio ?? 'Lengkapi profil Anda untuk meningkatkan ketertarikan recruiter.',
            ];

            $data['stats'] = [
                ['label' => 'Profil Lengkap', 'value' => $profile ? 'Siap' : 'Belum'],
                ['label' => 'Lamaran Aktif', 'value' => (string) $user->applications()->count()],
                ['label' => 'Lowongan Tersedia', 'value' => (string) Internship::query()->where('is_published', true)->count()],
            ];

            $data['latestInternships'] = Internship::query()
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

            $data['latestNotifications'] = $user->notificationsFeed()
                ->limit(3)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read_at' => $notification->read_at?->diffForHumans(),
                ]);
        }

        return Inertia::render('Peserta', $data);
    }
}
